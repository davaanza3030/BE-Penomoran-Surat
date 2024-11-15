<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\OutgoingLetter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OutgoingLetterController extends Controller
{
    public function index()
    {
        $letters = OutgoingLetter::with('category', 'creator')->get();
        return response()->json($letters);
    }



    // Helper function to get category abbreviation
    private function getCategoryAbbreviation($categoryId)
    {
        $abbreviations = [
            1 => 'SK',    // Surat Keputusan
            2 => 'SU',    // Surat Undangan
            3 => 'SPm',   // Surat Permohonan
            4 => 'SPb',   // Surat Pemberitahuan
            5 => 'SPp',   // Surat Peminjaman
            6 => 'SPn',   // Surat Pernyataan
            7 => 'SM',    // Surat Mandat
            8 => 'ST',    // Surat Tugas
            9 => 'SKet',  // Surat Keterangan
            10 => 'SR',   // Surat Rekomendasi
            11 => 'SB',   // Surat Balasan
            12 => 'SPPD', // Surat Perintah Perjalanan Dinas
            13 => 'SRT',  // Sertifikat
            14 => 'PK',   // Perjanjian Kerja
            15 => 'SPeng' // Surat Pengantar
        ];

        return $abbreviations[$categoryId] ?? null; // Return null if ID not in array
    }

    public function show($id)
    {
        $letter = OutgoingLetter::with('category', 'creator')->findOrFail($id);

        $fileType = null;
        $attachmentUrl = null;
        $attachmentName = null;
        $attachmentDownloadUrl = null;

        if ($letter->attachments) {
            $attachmentUrl = asset('storage/' . $letter->attachments); // Pastikan ini mengarah ke storage yang benar
            $attachmentName = basename($letter->attachments);
            $extension = pathinfo($letter->attachments, PATHINFO_EXTENSION);

            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $fileType = 'image';
            } elseif (in_array(strtolower($extension), ['pdf', 'xlsx'])) {
                $fileType = 'document';
            }
            $attachmentDownloadUrl = route('outgoing-letters.download', ['id' => $letter->id]);
        }

        return response()->json([
            'letter' => $letter,
            'fileType' => $fileType,
            'attachmentUrl' => $attachmentUrl,
            'attachmentName' => $attachmentName,
            'attachmentDownloadUrl' => $attachmentDownloadUrl,
        ]);
    }


    public function downloadAttachment($id)
    {
        $letter = OutgoingLetter::findOrFail($id);

        if (!$letter->attachments) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        $filePath = storage_path('app/public/' . $letter->attachments);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        $attachmentName = basename($filePath);
        $mimeType = mime_content_type($filePath);

        return response()->download($filePath, $attachmentName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $attachmentName . '"',
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Request data:', $request->all());

        $request->validate([
            'date' => 'required|date',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'recipient' => 'required|string|max:255',
            'recipient_abbreviation' => 'required|string|max:10',
            'letter_number' => 'required|string',
            'attachments' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $year = date('Y', strtotime($request->date));
            $month = date('n', strtotime($request->date));
            $monthRoman = $this->convertToRoman($month);

            $category = Category::findOrFail($request->category_id);
            $categoryCode = str_pad($category->id, 2, '0', STR_PAD_LEFT);

            Log::info("Category ID: " . $category->id);
            $categoryAbbreviation = $this->getCategoryAbbreviation($category->id);
            Log::info("Category Abbreviation: " . $categoryAbbreviation);

            $attachmentPath = null;
            if ($request->hasFile('attachments')) {
                $file = $request->file('attachments');
                $originalFileName = $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('attachments', $originalFileName, 'public'); // Simpan file ke storage publik
                Log::info("Attachment saved at: " . $attachmentPath);
            }

            $letter = OutgoingLetter::create([
                'letter_number' => '',
                'date' => $request->date,
                'recipient' => $request->recipient,
                'recipient_abbreviation' => $request->recipient_abbreviation,
                'category_id' => $request->category_id,
                'subject' => $request->subject,
                'description' => $request->description,
                'attachments' => $attachmentPath,
                'created_by' => Auth::id(),
            ]);

            // $nomorSuratInput = $request->nomor_surat;
            $formattedNumber = str_pad($letter->id, 3, '0', STR_PAD_LEFT);
            $letterNumber = $request->letter_number;

            $letter->update(['letter_number' => $letterNumber]);

            DB::commit();

            return response()->json(['message' => 'Surat keluar berhasil dibuat', 'letter' => $letter]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat membuat surat keluar', 'error' => $e->getMessage()], 500);
        }
    }


    private function convertToRoman($month)
    {
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $romans[$month];
    }

    public function getLastId()
{
    Log::info('getLastId endpoint accessed.');
    $lastId = OutgoingLetter::max('id') ?? 0;
    return response()->json(['last_id' => $lastId]);
}

    public function destroy($id)
    {
        $letter = OutgoingLetter::findOrFail($id);

        if ($letter->attachments) {
            Storage::disk('public')->delete($letter->attachments);
            Log::info("Deleted attachment: " . $letter->attachments); // Log deletion
        }

        $letter->delete();

        return response()->json(['message' => 'Surat keluar berhasil dihapus']);
    }
}
