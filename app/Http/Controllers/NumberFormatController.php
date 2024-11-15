<?php

namespace App\Http\Controllers;

use App\Models\NumberFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NumberFormatController extends Controller
{
    /**
     * Get all number formats.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Ambil semua format penomoran
        $numberFormats = NumberFormat::all();
        return response()->json($numberFormats);
    }

    /**
     * Create a new number format.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input hanya menerima YYYY, MM, KAT, dan NO
        $request->validate([
            'format_string' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Pastikan format hanya mengandung YYYY, MM, KAT, NO
                    if (!preg_match('/^(YYYY|MM|KAT|NO)(\/(YYYY|MM|KAT|NO))*$/', $value)) {
                        $fail('The format string must only contain YYYY, MM, KAT, and NO separated by slashes.');
                    }
                },
            ],
        ]);

        // Buat format penomoran baru
        $numberFormat = NumberFormat::create([
            'format_string' => $request->format_string,
            'created_by' => Auth::id(), // Simpan id admin yang membuat
        ]);

        return response()->json(['message' => 'Number format created successfully', 'numberFormat' => $numberFormat]);
    }

    /**
     * Get a specific number format by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Cari format penomoran berdasarkan ID
        $numberFormat = NumberFormat::findOrFail($id);
        return response()->json($numberFormat);
    }

    /**
     * Update a number format.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Cari format penomoran
        $numberFormat = NumberFormat::findOrFail($id);

        // Validasi input hanya menerima YYYY, MM, KAT, dan NO
        $request->validate([
            'format_string' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Pastikan format hanya mengandung YYYY, MM, KAT, NO
                    if (!preg_match('/^(YYYY|MM|KAT|NO)(\/(YYYY|MM|KAT|NO))*$/', $value)) {
                        $fail('The format string must only contain YYYY, MM, KAT, and NO separated by slashes.');
                    }
                },
            ],
        ]);

        // Update format
        $numberFormat->update([
            'format_string' => $request->format_string,
        ]);

        return response()->json(['message' => 'Number format updated successfully', 'numberFormat' => $numberFormat]);
    }

    /**
     * Delete a number format.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Cari format penomoran berdasarkan ID
        $numberFormat = NumberFormat::findOrFail($id);
        $numberFormat->delete();

        return response()->json(['message' => 'Number format deleted successfully']);
    }
}
