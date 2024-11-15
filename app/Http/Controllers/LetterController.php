<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\NumberFormat;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{
    // Get all letters of type masuk or keluar
    public function index(Request $request)
    {
        $type = $request->query('type', 'masuk'); // Default is 'masuk'
        $letters = Letter::with('category', 'creator')
            ->where('type', $type)  // Filter by type (masuk or keluar)
            ->get();

        return response()->json($letters);
    }

    public function show($id)
    {
        $letter = Letter::with('category', 'creator')->findOrFail($id);

        // Determine the file type (image or document)
        $fileType = null;
        $attachmentUrl = null;
        $attachmentName = null;
        $attachmentDownloadUrl = null;

        if ($letter->attachments) {
            $attachmentUrl = asset('storage/' . $letter->attachments); // Make sure the URL is correct
            $attachmentName = basename($letter->attachments); // Get the attachment name
            $extension = pathinfo($letter->attachments, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $fileType = 'image';
            } elseif (in_array(strtolower($extension), ['pdf', 'xlsx'])) {
                $fileType = 'document';
            }
            $attachmentDownloadUrl = route('letters.download', ['id' => $letter->id]);
        }

        return response()->json([
            'letter' => $letter,
            'fileType' => $fileType, // Adding file type info
            'attachmentUrl' => $attachmentUrl, // Including attachment URL
            'attachmentName' => $attachmentName, // Adding attachment name
            'attachmentDownloadUrl' => $attachmentDownloadUrl,
        ]);
    }

    // Download attachment
    public function downloadAttachment($id)
    {
        $letter = Letter::findOrFail($id);

        if (!$letter->attachments) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        // Path file di storage
        $filePath = storage_path('app/public/' . $letter->attachments);

        // Cek apakah file ada
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        // Ambil nama file asli
        $attachmentName = basename($filePath);

        // Tentukan MIME type dari file
        $mimeType = mime_content_type($filePath);

        // Kembalikan respons download dengan nama file dan MIME type yang benar
        return response()->download($filePath, $attachmentName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $attachmentName . '"',
        ]);
    }

    // Create a new letter (Surat Masuk or Keluar with attachments)
    public function store(Request $request)
    {
        // Validasi termasuk pdf, xlsx
        $request->validate([
            'date' => 'required|date',
            'subject' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sender' => 'nullable|string|max:255',
            'recipient' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachments' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx|max:2048',
            'type' => 'required|in:masuk,keluar',
            'letter_number' => 'required_if:type,masuk|string|max:50|unique:letters,letter_number', // Nomor surat manual wajib untuk surat masuk
            'number_format_id' => 'nullable|exists:number_formats,id', // Validasi format penomoran
        ]);

        // Mulai DB Transaction
        DB::beginTransaction();

        try {
            // Ambil detail penomoran untuk tanggal, tahun, dan kategori
            $year = date('Y', strtotime($request->date));
            $month = date('n', strtotime($request->date));
            $monthRoman = $this->convertToRoman($month);

            // Ambil data kategori dan singkatan
            $category = Category::findOrFail($request->category_id);
            $categoryCode = str_pad($category->id, 2, '0', STR_PAD_LEFT); // Tambahkan 0 jika ID kategori satu digit
            $categoryAbbr = $category->abbreviation;

            if ($request->type === 'keluar') {
                // Penomoran otomatis berdasarkan kategori, tahun, dan urutan
                $lastLetter = Letter::where('type', 'keluar')
                    ->whereYear('date', $year)
                    ->orderBy('letter_number', 'desc')
                    ->first();

                $newNumber = 1;
                if ($lastLetter) {
                    $lastNumber = intval(substr($lastLetter->letter_number, 3, 3));
                    $newNumber = $lastNumber + 1;
                }
                $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                // Format string sesuai aturan yang diminta
                $formatString = "{$categoryCode}.{$formattedNumber}/{$categoryAbbr}/ING/{$monthRoman}/{$year}";
            } else {
                // Surat Masuk: Gunakan nomor surat manual yang telah diinput
                $formatString = $request->letter_number;
            }

            // Simpan lampiran
            $attachmentPath = null;
            if ($request->hasFile('attachments')) {
                $file = $request->file('attachments');
                $originalFileName = $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('attachments', $originalFileName, 'public');
            }

            // Buat surat
            $letter = Letter::create([
                'letter_number' => $formatString,
                'subject' => $request->subject,
                'date' => $request->date,
                'category_id' => $request->category_id,
                'created_by' => Auth::id(),
                'sender' => $request->sender,
                'recipient' => $request->recipient,
                'description' => $request->description,
                'attachments' => $attachmentPath,
                'type' => $request->type,
            ]);

            DB::commit();

            return response()->json(['message' => 'Surat berhasil dibuat', 'letter' => $letter]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Terjadi kesalahan saat membuat surat', 'error' => $e->getMessage()], 500);
        }
    }

    // Fungsi konversi bulan ke romawi
    private function convertToRoman($month)
    {
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $romans[$month];
    }

    // Update a letter
    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:masuk,keluar',
            'category_id' => 'required|exists:categories,id',
            'sender' => 'nullable|string|max:255',
            'recipient' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $letter->update($request->all());

        return response()->json(['message' => 'Letter updated successfully', 'letter' => $letter]);
    }

    // Delete a letter
    public function destroy($id)
    {
        $letter = Letter::findOrFail($id);

        if ($letter->attachments) {
            Storage::disk('public')->delete($letter->attachments);
        }

        $letter->delete();

        return response()->json(['message' => 'Letter deleted successfully']);
    }
}
