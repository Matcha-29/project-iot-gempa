@extends('layouts.guest', ['title' => 'Login Petugas - Deteksi Gempa IoT'])
@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#F3F5F1] p-6">
    <div class="w-full max-w-md bg-white rounded-2xl p-8 shadow-sm border border-brand-border">
        <h1 class="text-2xl font-bold text-brand-text">Masuk Petugas</h1>
        <p class="text-xs text-brand-muted mt-1">Gunakan email atau nomor telepon akun petugas aktif.</p>
        @if ($errors->any())
            <div class="mt-4 p-3 rounded-xl bg-red-50 text-red-700 text-xs">{{ $errors->first() }}</div>
        @endif
        <form action="{{ url('/login') }}" method="POST" class="mt-8 space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-brand-muted mb-2">EMAIL / NOMOR TELEPON</label>
                <input type="text" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 rounded-xl border border-brand-border text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-brand-muted mb-2">KATA SANDI</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-brand-border text-sm focus:outline-none">
            </div>
            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="remember" value="1" class="rounded"> Ingat saya</label>
            <button type="submit" class="w-full py-3 rounded-xl bg-brand-aman text-white font-semibold text-sm">Masuk</button>
        </form>
    </div>
</div>
@endsection