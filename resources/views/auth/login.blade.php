@extends('layouts.guest', ['title' => 'Login Petugas - Deteksi Gempa IoT'])

@section('content')
<div class="min-h-screen flex flex-col md:flex-row">
    <div class="md:w-7/12 bg-[#14201A] p-8 md:p-16 flex flex-col justify-between text-white relative overflow-hidden">
        <div>
            <!-- Header Brand -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-aman flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-sm tracking-wide leading-none">Deteksi Gempa</h2>
                    <p class="text-xs text-gray-400 mt-1 font-mono">Konsensus 2 Node Diagonal</p>
                </div>
            </div>

            <!-- Pesan Utama -->
            <div class="mt-20 md:mt-32 max-w-lg">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight leading-tight">
                    Pantau getaran.<br>
                    Pastikan gempa.<br>
                    Peringatkan warga.
                </h1>
                <p class="mt-6 text-base text-gray-300 leading-relaxed">
                    Dua node ESP32 dipasang diagonal di tiap RT. Peringatan hanya dikirim jika keduanya terpicu dalam selisih waktu kurang dari 1,5 detik, sehingga gangguan lokal tidak memicu alarm palsu.
                </p>
            </div>
        </div>

        <!-- Ilustrasi Garis Diagonal Konsensus Node -->
        <div class="mt-16 bg-[#1a2b23] border border-gray-700/60 rounded-2xl p-6 relative">
            <div class="flex justify-between items-center text-xs font-mono text-gray-300 mb-6">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#2F5BEA]"></span>
                    <span>Node A</span>
                </div>
                <div class="bg-[#14201A] px-3 py-1 rounded-full border border-gray-600 text-xs">
                    Δt &lt; 1,5 dtk
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#C25E0B]"></span>
                    <span>Node B</span>
                </div>
            </div>
            <div class="relative h-12 flex items-center">
                <div class="w-full border-t border-dashed border-gray-500"></div>
                <div class="absolute left-4 w-3.5 h-3.5 rounded-full bg-[#2F5BEA] ring-4 ring-[#2F5BEA]/20"></div>
                <div class="absolute right-4 w-3.5 h-3.5 rounded-full bg-[#C25E0B] ring-4 ring-[#C25E0B]/20"></div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Login -->
    <div class="md:w-5/12 flex items-center justify-center p-8 bg-[#F3F5F1]">
        <div class="w-full max-w-md bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-brand-border">
            <h2 class="text-2xl font-bold text-brand-text">Masuk Petugas</h2>
            <p class="text-xs text-brand-muted mt-1 leading-relaxed">
                Khusus akun dengan peran petugas. Warga masuk lewat aplikasi mobile.
            </p>

            <form action="{{ url('/dashboard') }}" method="GET" class="mt-8 space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-brand-muted mb-2">Email</label>
                    <input type="email" placeholder="nama@contoh.id" required
                        class="w-full px-4 py-3 rounded-xl border border-brand-border bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-aman/30 focus:border-brand-aman">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-brand-muted mb-2">Kata Sandi</label>
                    <input type="password" placeholder="Masukkan kata sandi" required
                        class="w-full px-4 py-3 rounded-xl border border-brand-border bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-aman/30 focus:border-brand-aman">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-brand-text">
                        <input type="checkbox" class="w-4 h-4 rounded text-brand-aman focus:ring-brand-aman border-brand-border">
                        Ingat saya
                    </label>
                    <a href="#" class="text-brand-aman hover:underline font-semibold">Lupa kata sandi?</a>
                </div>

                <button type="submit"
                    class="w-full mt-4 py-3.5 px-4 rounded-xl bg-brand-aman hover:bg-[#14662F] text-white font-semibold text-sm transition shadow-sm">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
@endsection