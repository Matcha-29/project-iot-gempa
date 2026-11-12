<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $pengguna = Pengguna::where('email', $credentials['email'])->orWhere('no_telepon', $credentials['email'])->first();

        if (! $pengguna || ! Hash::check($credentials['password'], (string) $pengguna->kata_sandi)) {
            return back()->withErrors(['email' => 'Email/nomor telepon atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        if ($pengguna->peran !== 'petugas' || $pengguna->status_akun !== 'aktif') {
            return back()->withErrors(['email' => 'Akun bukan petugas aktif.'])->onlyInput('email');
        }

        Auth::login($pengguna, $request->boolean('remember'));
        $request->session()->regenerate();
        $pengguna->forceFill(['terakhir_masuk_pada' => now()])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
