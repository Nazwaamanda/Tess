<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat akun Administrator
        User::factory()->create([
            'name' => 'Administrator Alamtri',
            'email' => 'admin@alamtri.co.id', // Sesuai placeholder di form login HTML kamu
            'password' => Hash::make('Adminalamtri2025'), // Password manual (bukan default factory)
        ]);
    }
}
