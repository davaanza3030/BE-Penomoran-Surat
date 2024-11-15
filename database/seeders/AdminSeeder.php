<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username' => 'admin', // Nama pengguna admin
            'email' => 'admin@example.com', // Email admin
            'password' => Hash::make('12345678'), // Password yang dienkripsi
            'role' => 'admin', // Tetapkan role admin
        ]);
    }
}
