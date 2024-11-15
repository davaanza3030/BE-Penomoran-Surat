<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KelolaUserController extends Controller
{
    // List semua user
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Tambah user baru
    public function store(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // Menambahkan konfirmasi password
            'role' => 'required|in:admin,staff',
        ]);

        $user = User::create([
            'username' => $request->username,  // Ubah dari 'name' menjadi 'username'
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }



    // Update user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,staff',
            'password' => 'nullable|string|min:8|confirmed', // Password optional saat update
        ]);

        $data = [
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Jika password diisi, baru update password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        return response()->json($user);
    }
    // Delete user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204);
    }
}
