@extends('layouts.guest', ['title' => 'Masuk Petugas - Deteksi Gempa Konsensus 2 Node'])

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Kolom Kiri: Branding & Informasi Sistem -->
    <div class="lg:w-1/2 bg-[#0E1B14] text-white p-8 sm:p-12 lg:p-16 flex flex-col justify-between relative overflow-hidden">
        <!-- Background Grid Pattern Halus -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>

        <!-- Top: Logo & Judul Sistem -->
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-xl bg-[#17703A] flex items-center justify-center text-white shadow-md shadow-emerald-950/40">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-base tracking-tight text-white leading-tight">Deteksi Gempa</h1>
                <p class="text-xs text-emerald-200/70 font-normal">Konsensus 2 Node Diagonal</p>
            </div>
        </div>

        <!-- Middle: Headline & Penjelasan Ringkas -->
        <div class="my-12 lg:my-auto max-w-lg relative z-10">
            <h2 class="text-3xl sm:text-4xl lg:text-[42px] font-bold text-white leading-[1.15] tracking-tight">
                Pantau getaran.<br>
                Pastikan gempa.<br>
                Peringatkan warga.
            </h2>
            <p class="mt-5 text-sm sm:text-base text-gray-300/80 leading-relaxed font-normal">
                Dua node ESP32 dipasang diagonal di tiap RT. Peringatan hanya dikirim jika keduanya terpicu dalam selisih waktu kurang dari 1,5 detik, sehingga gangguan lokal tidak memicu alarm palsu.
            </p>
        </div>

        <!-- Bottom: Diagram Ilustrasi Konsensus 2 Node -->
        <div class="relative z-10 mt-6 lg:mt-0">
            <div class="relative rounded-2xl border border-dashed border-[#263D30] bg-[#0A140F]/90 p-6 min-h-[130px] flex flex-col justify-between overflow-hidden">
                <!-- SVG Garis Diagonal -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="none" viewBox="0 0 400 120">
                    <line x1="60" y1="35" x2="330" y2="85" stroke="#2A4535" stroke-width="2" />
                </svg>

                <!-- Baris Atas: Node A & Parameter Waktu -->
                <div class="flex items-center justify-between relative z-10">
                    <div class="inline-flex items-center gap-2 bg-[#14231B] border border-[#2B4435] rounded-full px-3.5 py-1 text-xs text-gray-200 font-medium shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#4977ec]"></span>
                        <span>Node A</span>
                    </div>
                    <span class="font-mono text-xs text-gray-400 font-medium tracking-wide">Δt &lt; 1,5 dtk</span>
                </div>

                <!-- Baris Bawah: Node B di Kanan Bawah -->
                <div class="flex items-center justify-end relative z-10 mt-6">
                    <div class="inline-flex items-center gap-2 bg-[#14231B] border border-[#2B4435] rounded-full px-3.5 py-1 text-xs text-gray-200 font-medium shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#e6833b]"></span>
                        <span>Node B</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Login Petugas -->
    <div class="lg:w-1/2 bg-[#F3F5F1] p-6 sm:p-12 lg:p-16 flex items-center justify-center min-h-[500px]">
        <div class="w-full max-w-[420px] bg-white rounded-2xl p-8 sm:p-10 shadow-sm border border-[#E1E6DF]">
            <!-- Judul Form -->
            <h2 class="text-2xl font-bold text-[#16211B] tracking-tight">Masuk Petugas</h2>
            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                Khusus akun dengan peran petugas. Warga masuk lewat aplikasi mobile.
            </p>

            <!-- Alert Error -->
            @if ($errors->any())
                <div class="mt-5 p-3.5 rounded-xl bg-red-50 border border-red-200/70 text-red-700 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ url('/login') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                    <input 
                        type="text" 
                        id="email"
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="nama@contoh.id"
                        required 
                        autofocus
                        class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#17703A]/20 focus:border-[#17703A] transition"
                    >
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">Kata sandi</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Masukkan kata sandi"
                        required 
                        class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#17703A]/20 focus:border-[#17703A] transition"
                    >
                </div>

                <div class="flex items-center justify-between pt-1 text-xs">
                    <label class="flex items-center gap-2 text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-gray-300 text-[#17703A] focus:ring-[#17703A]">
                        <span>Ingat saya</span>
                    </label>
                    <a href="javascript:void(0)" onclick="alert('Silakan hubungi administrator posko jika lupa kata sandi petugas.')" class="font-medium text-[#17703A] hover:underline">
                        Lupa kata sandi?
                    </a>
                </div>

                <button 
                    type="submit" 
                    class="w-full mt-2 py-3 bg-[#17703A] hover:bg-[#135c2f] active:scale-[0.99] text-white font-semibold rounded-lg text-sm shadow-sm transition duration-150 flex items-center justify-center gap-2"
                >
                    <span>Masuk</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
