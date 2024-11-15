<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    // Mengambil semua kategori
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Menyimpan kategori baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Category created successfully', 'category' => $category]);
    }

    // Menampilkan kategori berdasarkan ID
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // Memperbarui kategori
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Category updated successfully', 'category' => $category]);
    }

    // Menghapus kategori
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
