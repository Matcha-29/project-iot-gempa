@extends('layouts.app', ['title' => 'Catatan Sirine & Alarm - Sistem Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Log Alarm',
    'subtitle' => 'Catatan setiap kali sirine dibunyikan, beserta status dan catatan petugas'
])

<main class="p-8 space-y-6">
    <!-- 3 Kotak Indikator Log Alarm -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">ALARM BULAN INI</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">5</p>
            <span class="text-xs text-brand-muted mt-2 block">1 gempa, 4 uji coba</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">SIRINE TERAKHIR</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">08:00</p>
            <span class="text-xs text-brand-muted mt-2 block">Hari ini · uji coba · 6 detik</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">STATUS SIRINE</span>
            <p class="text-2xl font-bold mt-1 text-brand-aman">Siap</p>
            <span class="text-xs text-brand-muted mt-2 block">Uji terakhir berhasil</span>
        </div>
    </div>

    <!-- Riwayat Alarm -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-brand-border">
            <h2 class="font-bold text-base text-brand-text">Riwayat Alarm</h2>
            <select class="px-3 py-2 border border-brand-border rounded-xl text-xs bg-white focus:outline-none">
                <option>Semua status</option>
                <option>Selesai</option>
                <option>Gagal</option>
                <option>Dibatalkan</option>
            </select>
        </div>

        <div class="overflow-x-auto mt-2">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="text-brand-muted uppercase text-[10px] tracking-wider border-b border-brand-border font-sans">
                        <th class="py-3 px-3">Waktu Alarm</th>
                        <th class="py-3 px-3">Deteksi Terkait</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Durasi</th>
                        <th class="py-3 px-3 font-sans">Catatan Petugas</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border font-sans">
                    <tr>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">Hari ini 08:00:12</td>
                        <td class="py-3.5 px-3 font-semibold text-blue-700">Uji coba · RT 01</td>
                        <td class="py-3.5 px-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Selesai</span></td>
                        <td class="py-3.5 px-3 font-mono">6 dtk</td>
                        <td class="py-3.5 px-3 text-brand-muted text-[11px]">Uji rutin harian, sirine normal.</td>
                        <td class="py-3.5 px-3 text-right"><button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button></td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">Kemarin 21:14:38</td>
                        <td class="py-3.5 px-3 font-semibold text-brand-waspada">Sedang · RT 01</td>
                        <td class="py-3.5 px-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Selesai</span></td>
                        <td class="py-3.5 px-3 font-mono">45 dtk</td>
                        <td class="py-3.5 px-3 text-brand-muted text-[11px]">Gempa terkonfirmasi. 1 warga minta bantuan, sudah ditangani.</td>
                        <td class="py-3.5 px-3 text-right"><button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button></td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">27 Okt 16:05:15</td>
                        <td class="py-3.5 px-3 font-semibold text-emerald-700">Ringan · RT 01</td>
                        <td class="py-3.5 px-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Selesai</span></td>
                        <td class="py-3.5 px-3 font-mono">20 dtk</td>
                        <td class="py-3.5 px-3 text-brand-muted text-[11px]">—</td>
                        <td class="py-3.5 px-3 text-right"><button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button></td>
                    </tr>
                    <tr class="bg-red-50/40">
                        <td class="py-3.5 px-3 font-mono text-brand-muted">25 Okt 08:00:10</td>
                        <td class="py-3.5 px-3 font-semibold text-blue-700">Uji coba · RT 01</td>
                        <td class="py-3.5 px-3"><span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-[10px] font-bold">Gagal</span></td>
                        <td class="py-3.5 px-3 font-mono">0 dtk</td>
                        <td class="py-3.5 px-3 text-red-700 text-[11px]">Relay sirine tidak merespons. Sudah diperbaiki 26 Okt.</td>
                        <td class="py-3.5 px-3 text-right"><button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button></td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">24 Okt 12:40:02</td>
                        <td class="py-3.5 px-3 font-semibold text-blue-700">Uji coba · RT 01</td>
                        <td class="py-3.5 px-3"><span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[10px] font-bold">Dibatalkan</span></td>
                        <td class="py-3.5 px-3 font-mono">2 dtk</td>
                        <td class="py-3.5 px-3 text-brand-muted text-[11px]">Dibatalkan petugas, salah tekan.</td>
                        <td class="py-3.5 px-3 text-right"><button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Ubah</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Input Catatan Petugas -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <h3 class="font-bold text-sm text-brand-text mb-3">Catatan untuk alarm 25 Okt 08:00:10</h3>
        <textarea rows="3" class="w-full p-4 border border-brand-border rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-brand-aman/20">Relay sirine tidak merespons. Sudah diperbaiki 26 Okt.</textarea>
        <div class="flex justify-end mt-3">
            <button class="px-5 py-2.5 rounded-xl bg-brand-aman text-white text-xs font-semibold hover:bg-[#14662F]">Simpan Catatan</button>
        </div>
    </div>
</main>
@endsection