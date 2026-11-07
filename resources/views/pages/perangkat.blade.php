@extends('layouts.app', ['title' => 'Kelola Perangkat Node - Sistem Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Perangkat',
    'subtitle' => 'Kesehatan dan pengaturan 2 node ESP32 di RT 01'
])

<main class="p-8 space-y-6">
    <!-- Header Tombol Aksi -->
    <div class="flex justify-between items-center">
        <!-- 4 Kotak Ringkasan -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
            <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
                <span class="text-[11px] font-bold text-brand-muted uppercase">TOTAL PERANGKAT</span>
                <p class="text-3xl font-mono font-bold mt-1">2</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
                <span class="text-[11px] font-bold text-brand-muted uppercase">ONLINE</span>
                <p class="text-3xl font-mono font-bold text-brand-aman mt-1">2</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
                <span class="text-[11px] font-bold text-brand-muted uppercase">OFFLINE</span>
                <p class="text-3xl font-mono font-bold text-gray-400 mt-1">0</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
                <span class="text-[11px] font-bold text-brand-muted uppercase">SYARAT KONSENSUS</span>
                <p class="text-2xl font-mono font-bold text-brand-text mt-1">Δt &lt; 1,5 dtk</p>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Perangkat -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between pb-4 border-b border-brand-border gap-4">
            <h2 class="font-bold text-base text-brand-text">Daftar Perangkat</h2>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <input type="text" placeholder="Cari perangkat..." class="px-4 py-2 border border-brand-border rounded-xl text-xs w-full md:w-64 focus:outline-none">
                <select class="px-3 py-2 border border-brand-border rounded-xl text-xs bg-white focus:outline-none">
                    <option>Semua status</option>
                    <option>Online</option>
                    <option>Offline</option>
                </select>
                <button class="px-4 py-2 rounded-xl bg-brand-text text-white text-xs font-semibold whitespace-nowrap">+ Tambah Perangkat</button>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="text-brand-muted uppercase text-[10px] tracking-wider border-b border-brand-border font-sans">
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Posisi Node</th>
                        <th class="py-3 px-3">Lokasi</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Heartbeat</th>
                        <th class="py-3 px-3 font-sans">Sensor</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border font-sans">
                    <tr>
                        <td class="py-4 px-3 font-mono text-brand-muted">ND-001</td>
                        <td class="py-4 px-3 font-semibold flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#2F5BEA]"></span>
                            Node A Diagonal 1
                        </td>
                        <td class="py-4 px-3">RT 01</td>
                        <td class="py-4 px-3"><span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold font-mono">Online</span></td>
                        <td class="py-4 px-3 font-mono text-brand-muted">2 detik lalu</td>
                        <td class="py-4 px-3 text-brand-muted text-[11px]">SW420 + Akselerometer</td>
                        <td class="py-4 px-3 text-right">
                            <button class="px-3 py-1.5 rounded-lg border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-3 font-mono text-brand-muted">ND-002</td>
                        <td class="py-4 px-3 font-semibold flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#C25E0B]"></span>
                            Node B Diagonal 2
                        </td>
                        <td class="py-4 px-3">RT 01</td>
                        <td class="py-4 px-3"><span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold font-mono">Online</span></td>
                        <td class="py-4 px-3 font-mono text-brand-muted">3 detik lalu</td>
                        <td class="py-4 px-3 text-brand-muted text-[11px]">SW420 + Akselerometer</td>
                        <td class="py-4 px-3 text-right">
                            <button class="px-3 py-1.5 rounded-lg border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Tambah / Ubah & Info Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
            <h3 class="font-bold text-sm text-brand-text mb-4">Tambah / Ubah Perangkat</h3>
            <form class="space-y-4 text-xs">
                <div>
                    <label class="block text-brand-muted uppercase font-bold text-[10px] mb-1">Lokasi</label>
                    <select class="w-full px-3 py-2.5 border border-brand-border rounded-xl bg-white focus:outline-none">
                        <option>RT 01</option>
                    </select>
                </div>
                <div>
                    <label class="block text-brand-muted uppercase font-bold text-[10px] mb-1">Posisi Node</label>
                    <select class="w-full px-3 py-2.5 border border-brand-border rounded-xl bg-white focus:outline-none">
                        <option>Node A Diagonal 1</option>
                        <option>Node B Diagonal 2</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-xl border border-brand-border hover:bg-gray-50 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-text text-white font-semibold">Simpan</button>
                </div>
            </form>
        </div>

        <div class="bg-emerald-50/60 border border-emerald-200 p-6 rounded-2xl flex flex-col justify-center">
            <h4 class="font-bold text-emerald-900 text-sm">Semua node online</h4>
            <p class="text-xs text-emerald-800 mt-2 leading-relaxed">
                Kedua node diagonal RT 01 aktif, jadi konsensus gempa berjalan normal. Jika salah satu node offline, alarm tidak bisa dikonfirmasi dan warga akan melihat status "Pemantauan tidak aktif".
            </p>
        </div>
    </div>
</main>
@endsection