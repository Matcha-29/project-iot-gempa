@extends('layouts.app', ['title' => 'Pengaturan Wilayah RT 01 - Sistem Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Lokasi',
    'subtitle' => 'Data wilayah pantauan. Sistem ini dipasang untuk satu RT dengan 2 node diagonal.'
])

<main class="p-8 space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Info Ringkas & Peta Ilustrasi Node Diagonal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header Kartu Wilayah RT 01 -->
            <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-brand-border">
                    <h2 class="text-xl font-bold text-brand-text">RT 01</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">2/2 NODE ONLINE</span>
                </div>
                <div class="grid grid-cols-3 gap-4 pt-4 text-xs font-mono">
                    <div>
                        <span class="text-brand-muted text-[11px] font-sans block">Warga terdaftar</span>
                        <span class="text-xl font-bold text-brand-text">24</span>
                    </div>
                    <div>
                        <span class="text-brand-muted text-[11px] font-sans block">Node terpasang</span>
                        <span class="text-xl font-bold text-brand-text">2</span>
                    </div>
                    <div>
                        <span class="text-brand-muted text-[11px] font-sans block">Koordinat</span>
                        <span class="text-xs font-bold text-brand-text">-7.250000, 112.750000</span>
                    </div>
                </div>
            </div>

            <!-- Ilustrasi Posisi Node di RT 01 -->
            <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
                <h3 class="font-bold text-sm text-brand-text mb-4">Posisi Node di RT 01</h3>
                <div class="p-6 rounded-xl bg-gray-50 border border-brand-border h-64 relative flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="px-3 py-1.5 rounded-xl bg-[#2F5BEA] text-white text-xs font-bold shadow-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-white"></span> Node A
                        </span>
                    </div>
                    <!-- Garis Diagonal RT -->
                    <div class="border-t-2 border-dashed border-gray-300 w-full transform -rotate-12 flex justify-center">
                        <span class="bg-emerald-700 text-white px-3 py-0.5 rounded-full text-[10px] font-bold shadow -translate-y-2">RT 01</span>
                    </div>
                    <div class="flex justify-end items-end">
                        <span class="px-3 py-1.5 rounded-xl bg-[#C25E0B] text-white text-xs font-bold shadow-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-white"></span> Node B
                        </span>
                    </div>
                </div>
                <p class="text-[11px] text-brand-muted mt-3 leading-relaxed">
                    Peta ilustrasi. Pasang kedua node di sudut yang berseberangan (diagonal) agar getaran dari satu arah tidak hanya terbaca oleh satu node.
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Form Ubah Data Lokasi -->
        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-base text-brand-text">Ubah Data Lokasi</h3>
                <p class="text-xs text-brand-muted mt-0.5">Dipakai untuk tampilan di dashboard dan aplikasi warga.</p>

                <form class="space-y-4 mt-6 text-xs">
                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Nama Lokasi</label>
                        <input type="text" value="RT 01" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Alamat</label>
                        <textarea rows="2" placeholder="[Alamat lengkap RT]" class="w-full px-3 py-2 border border-brand-border rounded-xl focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3 font-mono">
                        <div>
                            <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1 font-sans">Latitude</label>
                            <input type="text" value="-7.250000" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1 font-sans">Longitude</label>
                            <input type="text" value="112.750000" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Nomor Petugas untuk Warga</label>
                        <input type="text" value="0812-••••-1234" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none font-mono">
                        <span class="text-[10px] text-brand-muted mt-1 block">Nomor ini muncul di tombol "Telepon Petugas" pada aplikasi warga.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-2">Perangkat Terpasang</label>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded bg-blue-50 text-[#2F5BEA] text-[11px] font-bold">● Node A · Diagonal 1</span>
                            <span class="px-2.5 py-1 rounded bg-amber-50 text-[#C25E0B] text-[11px] font-bold">● Node B · Diagonal 2</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-3 rounded-xl bg-brand-aman text-white font-semibold text-xs hover:bg-[#14662F]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection