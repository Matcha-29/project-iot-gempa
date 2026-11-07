@extends('layouts.app', ['title' => 'Telemetri Getaran Realtime - Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Telemetri',
    'subtitle' => 'Sinyal getaran Node A dan Node B dalam rentang waktu pilihan'
])

<main class="p-8 space-y-6">
    <!-- Filter Range Waktu & Download -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <select class="px-3 py-2 border border-brand-border rounded-xl text-xs bg-white focus:outline-none font-semibold">
                <option>RT 01</option>
            </select>
            <div class="flex items-center bg-gray-100 p-1 rounded-xl text-xs font-medium">
                <button class="px-3 py-1 text-brand-muted hover:text-brand-text">1 jam</button>
                <button class="px-3 py-1 text-brand-muted hover:text-brand-text">6 jam</button>
                <button class="px-3 py-1 bg-brand-text text-white rounded-lg">24 jam</button>
                <button class="px-3 py-1 text-brand-muted hover:text-brand-text">7 hari</button>
            </div>
        </div>
        <button class="px-4 py-2 border border-brand-border rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Unduh CSV
        </button>
    </div>

    <!-- Grafik Rentang Waktu 24 Jam -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-brand-border pb-3 gap-2">
            <div>
                <h2 class="font-bold text-base text-brand-text">Getaran 24 Jam Terakhir · RT 01</h2>
                <p class="text-xs text-brand-muted">Nilai getaran dalam satuan g. Garis merah putus-putus adalah ambang batas.</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-mono">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#2F5BEA]"></span>Node A</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#C25E0B]"></span>Node B</span>
                <span class="flex items-center gap-1.5 text-red-500 font-sans"><span class="w-3 border-t-2 border-dashed border-red-500"></span>Ambang batas</span>
            </div>
        </div>

        <div class="h-64 mt-4 relative">
            <canvas id="chart24Jam"></canvas>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">PUNCAK NODE A</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">0,42 <span class="text-sm font-normal">g</span></p>
            <span class="text-[11px] font-mono text-brand-muted mt-2 block">08:00:06</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">PUNCAK NODE B</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">0,40 <span class="text-sm font-normal">g</span></p>
            <span class="text-[11px] font-mono text-brand-muted mt-2 block">08:00:07</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">RATA-RATA GETARAN</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">0,03 <span class="text-sm font-normal">g</span></p>
            <span class="text-[11px] text-brand-muted mt-2 block">kondisi normal</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">MELEWATI AMBANG</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">3 <span class="text-sm font-normal">kali</span></p>
            <span class="text-[11px] text-brand-muted mt-2 block">1 gangguan lokal, 2 uji sistem</span>
        </div>
    </div>

    <!-- Tabel Data Level Getaran Terbaru -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <h3 class="font-bold text-base text-brand-text mb-4">Data Level Getaran Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="text-brand-muted uppercase text-[10px] tracking-wider border-b border-brand-border font-sans">
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3">Node</th>
                        <th class="py-3 px-3">Getaran</th>
                        <th class="py-3 px-3">SW420</th>
                        <th class="py-3 px-3 font-sans">Kategori Level</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border">
                    <tr>
                        <td class="py-3 px-3 text-brand-muted">10:47:30</td>
                        <td class="py-3 px-3 font-bold text-[#2F5BEA]">Node A</td>
                        <td class="py-3 px-3 font-bold">0,04 g</td>
                        <td class="py-3 px-3 text-brand-muted">Tidak aktif</td>
                        <td class="py-3 px-3 font-sans"><span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Normal</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 text-brand-muted">10:47:30</td>
                        <td class="py-3 px-3 font-bold text-[#C25E0B]">Node B</td>
                        <td class="py-3 px-3 font-bold">0,03 g</td>
                        <td class="py-3 px-3 text-brand-muted">Tidak aktif</td>
                        <td class="py-3 px-3 font-sans"><span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Normal</span></td>
                    </tr>
                    <tr">
                        <td class="py-3 px-3 text-brand-muted">10:47:04</td>
                        <td class="py-3 px-3 font-bold text-[#2F5BEA]">Node A</td>
                        <td class="py-3 px-3 font-bold text-amber-900">0,24 g</td>
                        <td class="py-3 px-3 text-brand-bahaya font-semibold">Aktif</td>
                        <td class="py-3 px-3 font-sans"><span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Tinggi</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 text-brand-muted">10:47:04</td>
                        <td class="py-3 px-3 font-bold text-[#C25E0B]">Node B</td>
                        <td class="py-3 px-3 font-bold">0,05 g</td>
                        <td class="py-3 px-3 text-brand-muted">Tidak aktif</td>
                        <td class="py-3 px-3 font-sans"><span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Normal</span></td>
                    </tr>
                    <tr class="bg-red-50/40">
                        <td class="py-3 px-3 text-brand-muted">08:00:07</td>
                        <td class="py-3 px-3 font-bold text-[#C25E0B]">Node B</td>
                        <td class="py-3 px-3 font-bold text-brand-bahaya">0,40 g</td>
                        <td class="py-3 px-3 text-brand-bahaya font-semibold">Aktif</td>
                        <td class="py-3 px-3 font-sans"><span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 text-[10px] font-bold">Sangat tinggi</span></td>
                    </tr>
                    <tr class="bg-red-50/40">
                        <td class="py-3 px-3 text-brand-muted">08:00:06</td>
                        <td class="py-3 px-3 font-bold text-[#2F5BEA]">Node A</td>
                        <td class="py-3 px-3 font-bold text-brand-bahaya">0,42 g</td>
                        <td class="py-3 px-3 text-brand-bahaya font-semibold">Aktif</td>
                        <td class="py-3 px-3 font-sans"><span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 text-[10px] font-bold">Sangat tinggi</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    const ctx24 = document.getElementById('chart24Jam').getContext('2d');
    new Chart(ctx24, {
        type: 'line',
        data: {
            labels: ['-24 jam', '-18 jam', '-12 jam', '-6 jam', '08:00 (uji)', '10:47 (lokal)', 'sekarang'],
            datasets: [
                {
                    label: 'Node A',
                    data: [0.03, 0.04, 0.12, 0.03, 0.42, 0.24, 0.04],
                    borderColor: '#2F5BEA',
                    backgroundColor: '#2F5BEA',
                    tension: 0.2,
                    borderWidth: 2,
                    pointRadius: [0, 0, 0, 0, 5, 5, 0]
                },
                {
                    label: 'Node B',
                    data: [0.03, 0.03, 0.04, 0.03, 0.40, 0.05, 0.03],
                    borderColor: '#C25E0B',
                    backgroundColor: '#C25E0B',
                    tension: 0.2,
                    borderWidth: 2,
                    pointRadius: [0, 0, 0, 0, 5, 0, 0]
                },
                {
                    label: 'Ambang Batas',
                    data: [0.20, 0.20, 0.20, 0.20, 0.20, 0.20, 0.20],
                    borderColor: '#B3261E',
                    borderDash: [5, 5],
                    borderWidth: 1.5,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0.00, max: 0.50, grid: { color: '#E1E6DF' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endsection