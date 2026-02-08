<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menangani proses autentikasi (Login).
     */
    public function authenticate(Request $request)
    {
        // 1. Validasi Input

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menangani proses Logout (Opsional/Tambahan).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // --- PERBAIKAN DI SINI ---
        // Ganti redirect('/') menjadi redirect('/login')
        // atau lebih baik menggunakan route name: redirect()->route('login')
        return redirect()->route('login');
    }
}
