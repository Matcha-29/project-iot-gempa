@extends('layouts.app', ['title' => 'Deteksi & Peta Respons Warga - Sistem IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Deteksi',
    'subtitle' => 'Riwayat kejadian getaran dan hasil verifikasi konsensus 2 node'
])

<main class="p-8 space-y-6">
    <!-- Filter & Unduh Laporan -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="flex items-center bg-gray-100 p-1 rounded-xl text-xs font-medium">
                <button class="px-3 py-1.5 bg-brand-text text-white rounded-lg">Semua</button>
                <button class="px-3 py-1.5 text-brand-muted hover:text-brand-text">Gempa terkonfirmasi</button>
                <button class="px-3 py-1.5 text-brand-muted hover:text-brand-text">Gangguan lokal</button>
                <button class="px-3 py-1.5 text-brand-muted hover:text-brand-text">Uji coba</button>
            </div>
            <select class="px-3 py-2 border border-brand-border rounded-xl text-xs bg-white focus:outline-none">
                <option>7 hari terakhir</option>
                <option>30 hari terakhir</option>
            </select>
        </div>
        <button class="px-4 py-2 border border-brand-border rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 shadow-sm">
            Unduh Laporan
        </button>
    </div>

    <!-- Tabel Riwayat Kejadian Deteksi -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="text-brand-muted uppercase text-[10px] tracking-wider border-b border-brand-border font-sans">
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3">Lokasi</th>
                        <th class="py-3 px-3">Node</th>
                        <th class="py-3 px-3">Selisih</th>
                        <th class="py-3 px-3">Konsensus</th>
                        <th class="py-3 px-3 font-sans">Keparahan</th>
                        <th class="py-3 px-3 font-sans">Respons Warga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border font-sans">
                    <tr class="bg-gray-50/40">
                        <td class="py-3.5 px-3 font-mono text-brand-muted">Hari ini 10:47:04</td>
                        <td class="py-3.5 px-3 font-mono">RT 01</td>
                        <td class="py-3.5 px-3 font-semibold text-[#2F5BEA]">Node A</td>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">-</td>
                        <td class="py-3.5 px-3"><span class="text-gray-500 font-medium">Tidak terpenuhi</span></td>
                        <td class="py-3.5 px-3"><span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Gangguan lokal</span></td>
                        <td class="py-3.5 px-3 text-brand-muted text-[11px]">Tidak ada notifikasi</td>
                    </tr>
                    <tr class="bg-blue-50/20">
                        <td class="py-3.5 px-3 font-mono text-brand-muted">Hari ini 08:00:06</td>
                        <td class="py-3.5 px-3 font-mono">RT 01</td>
                        <td class="py-3.5 px-3 font-semibold">Node A + B</td>
                        <td class="py-3.5 px-3 font-mono font-bold text-blue-700">0,6 dtk</td>
                        <td class="py-3.5 px-3"><span class="text-emerald-700 font-bold">Terpenuhi</span></td>
                        <td class="py-3.5 px-3"><span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">Uji coba</span></td>
                        <td class="py-3.5 px-3 text-[11px] font-mono">19 aman · 2 bantuan · 3 belum</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">Kemarin 21:14:33</td>
                        <td class="py-3.5 px-3 font-mono">RT 01</td>
                        <td class="py-3.5 px-3 font-semibold">Node A + B</td>
                        <td class="py-3.5 px-3 font-mono font-bold">0,9 dtk</td>
                        <td class="py-3.5 px-3"><span class="text-emerald-700 font-bold">Terpenuhi</span></td>
                        <td class="py-3.5 px-3"><span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Sedang</span></td>
                        <td class="py-3.5 px-3 text-[11px] font-mono">21 aman · 1 bantuan · 2 belum</td>
                    </tr>
                    <tr class="bg-gray-50/40">
                        <td class="py-3.5 px-3 font-mono text-brand-muted">Kemarin 03:31:50</td>
                        <td class="py-3.5 px-3 font-mono">RT 01</td>
                        <td class="py-3.5 px-3 font-semibold text-[#C25E0B]">Node B</td>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">-</td>
                        <td class="py-3.5 px-3"><span class="text-gray-500 font-medium">Tidak terpenuhi</span></td>
                        <td class="py-3.5 px-3"><span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Gangguan lokal</span></td>
                        <td class="py-3.5 px-3 text-brand-muted text-[11px]">Tidak ada notifikasi</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-3 font-mono text-brand-muted">27 Okt 16:05:12</td>
                        <td class="py-3.5 px-3 font-mono">RT 01</td>
                        <td class="py-3.5 px-3 font-semibold">Node A + B</td>
                        <td class="py-3.5 px-3 font-mono font-bold">1,1 dtk</td>
                        <td class="py-3.5 px-3"><span class="text-emerald-700 font-bold">Terpenuhi</span></td>
                        <td class="py-3.5 px-3"><span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Ringan</span></td>
                        <td class="py-3.5 px-3 text-[11px] font-mono">22 aman · 0 bantuan · 2 belum</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Deteksi Terpilih & Peta Sebaran Respons Warga -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detail Deteksi Terpilih -->
        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                    <h3 class="font-bold text-sm text-brand-text">Detail Deteksi</h3>
                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-bold text-[10px]">Uji coba</span>
                </div>
                <p class="text-xs font-mono text-brand-muted mt-2">Hari ini, 08:00:06 · RT 01</p>

                <div class="mt-4 space-y-3 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-brand-border">
                        <div class="font-bold flex items-center gap-1.5 text-[#2F5BEA]">
                            <span class="w-2 h-2 rounded-full bg-[#2F5BEA]"></span>Node A terpicu
                        </div>
                        <p class="font-mono text-[11px] text-brand-muted mt-1">08:00:06,2 · 0,42 g · SW420 aktif</p>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-brand-border">
                        <div class="font-bold flex items-center gap-1.5 text-[#C25E0B]">
                            <span class="w-2 h-2 rounded-full bg-[#C25E0B]"></span>Node B terpicu
                        </div>
                        <p class="font-mono text-[11px] text-brand-muted mt-1">08:00:06,8 · 0,40 g · selisih 0,6 detik</p>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                        <div class="font-bold text-emerald-800">Konsensus terpenuhi</div>
                        <p class="text-[11px] text-emerald-700 font-mono mt-1">Δt 0,6 dtk &lt; 1,5 dtk · dikonfirmasi (mode uji)</p>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-brand-border">
                        <div class="font-bold text-brand-text">Sirine dan notifikasi</div>
                        <p class="font-mono text-[11px] text-brand-muted mt-1">08:00:12 · sirine 6 detik · 48 notifikasi terkirim</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peta Interaktif Sebaran Rumah Warga & Respons -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-brand-border shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-brand-border gap-2">
                    <h3 class="font-bold text-sm text-brand-text">Lokasi dan Respons Warga</h3>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Aman 19</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Butuh bantuan 2</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>Belum merespons 3</span>
                    </div>
                </div>

                <!-- Ilustrasi Kanvas Peta -->
                <div class="mt-4 p-4 rounded-xl bg-[#E8EDE6] border border-brand-border h-60 relative overflow-hidden flex items-center justify-center">
                    <!-- Diagonal Line -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-full border-t-2 border-dashed border-gray-400 transform -rotate-12"></div>
                    </div>

                    <!-- Marker Node A & B -->
                    <div class="absolute top-4 left-6 px-2.5 py-1 rounded bg-[#2F5BEA] text-white font-bold text-xs shadow">Node A</div>
                    <div class="absolute bottom-4 right-6 px-2.5 py-1 rounded bg-[#C25E0B] text-white font-bold text-xs shadow">Node B</div>

                    <!-- Titik-Titik Rumah Warga -->
                    <div class="absolute top-10 left-32 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow"></div>
                    <div class="absolute top-20 left-48 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow"></div>
                    <div class="absolute bottom-16 left-28 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow"></div>
                    <div class="absolute top-14 right-44 w-4 h-4 rounded-full bg-gray-400 border-2 border-white shadow"></div>
                    <div class="absolute bottom-20 right-36 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow"></div>

                    <!-- Warga Butuh Bantuan (Merah) -->
                    <div class="absolute top-24 left-64 flex flex-col items-center">
                        <div class="w-5 h-5 rounded-full bg-red-600 border-2 border-white shadow animate-bounce"></div>
                    </div>
                    <div class="absolute bottom-10 left-96 flex flex-col items-center">
                        <div class="w-5 h-5 rounded-full bg-red-600 border-2 border-white shadow animate-bounce"></div>
                    </div>
                </div>
                <p class="text-[10px] text-brand-muted mt-2">Peta ilustrasi. Saat dibangun dapat digantikan dengan Leaflet / Google Maps sungguhan.</p>
            </div>

            <!-- List Warga Butuh Bantuan Terkait Deteksi Ini -->
            <div class="divide-y divide-brand-border mt-4 text-xs">
                <div class="py-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600 shrink-0"></span>
                        <div>
                            <span class="font-bold text-brand-text">Rosa</span>
                            <span class="text-brand-muted text-[11px] block">Lapor 08:02 · akurasi ±12 m</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1 border border-brand-border rounded-lg hover:bg-gray-50 font-semibold">Buka di peta</button>
                        <button class="px-3 py-1 rounded-lg bg-brand-text text-white font-semibold">Hubungi</button>
                    </div>
                </div>
                <div class="py-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600 shrink-0"></span>
                        <div>
                            <span class="font-bold text-brand-text">Risa</span>
                            <span class="text-brand-muted text-[11px] block">Lapor 08:03 · akurasi ±35 m</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1 border border-brand-border rounded-lg hover:bg-gray-50 font-semibold">Buka di peta</button>
                        <button class="px-3 py-1 rounded-lg bg-brand-text text-white font-semibold">Hubungi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection