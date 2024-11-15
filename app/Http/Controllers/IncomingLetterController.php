<?php

namespace App\Http\Controllers;

use App\Models\IncomingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IncomingLetterController extends Controller
{
    public function index()
    {
        $letters = IncomingLetter::with('category', 'creator')->get();
        return response()->json($letters);
    }

    public function monthlyStats()
    {
        // Menghitung jumlah surat masuk per bulan
        $stats = IncomingLetter::selectRaw('MONTH(date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($stats);
    }


    public function show($id)
    {
        $letter = IncomingLetter::with('category', 'creator')->findOrFail($id);

        $fileType = null;
        $attachmentUrl = null;
        $attachmentName = null;
        $attachmentDownloadUrl = null;

        if ($letter->attachments) {
            $attachmentUrl = asset('storage/' . $letter->attachments);
            $attachmentName = basename($letter->attachments);
            $extension = pathinfo($letter->attachments, PATHINFO_EXTENSION);

            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $fileType = 'image';
            } elseif (in_array(strtolower($extension), ['pdf', 'xlsx'])) {
                $fileType = 'document';
            }
            $attachmentDownloadUrl = route('incoming-letters.download', ['id' => $letter->id]);
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
        $letter = IncomingLetter::findOrFail($id);

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
        $request->validate([
            'date' => 'required|date',
            'subject' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sender' => 'required|string|max:255',
            'attachments' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx|max:2048',
            'letter_number' => 'required|string|max:50|unique:incoming_letters,letter_number',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachments')) {
            $file = $request->file('attachments');
            $attachmentPath = $file->store('attachments', 'public');
        }

        $letter = IncomingLetter::create([
            'letter_number' => $request->letter_number,
            'date' => $request->date,
            'sender' => $request->sender,
            'category_id' => $request->category_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'attachments' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Surat masuk berhasil dibuat', 'letter' => $letter]);
    }

    public function destroy($id)
    {
        $letter = IncomingLetter::findOrFail($id);

        if ($letter->attachments) {
            Storage::disk('public')->delete($letter->attachments);
        }

        $letter->delete();

        return response()->json(['message' => 'Surat masuk berhasil dihapus']);
    }
}
