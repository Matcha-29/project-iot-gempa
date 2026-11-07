@extends('layouts.app', ['title' => 'Dashboard Monitoring - Sistem Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Dashboard Monitoring',
    'subtitle' => 'RT 01 · 2 node ESP32 diagonal · konsensus jika selisih waktu < 1,5 detik'
])

<main class="p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase tracking-wider">Status Sistem</span>
            <div class="mt-2 flex items-center gap-2">
                <span class="w-3.5 h-3.5 rounded-full bg-brand-aman inline-block"></span>
                <span class="text-2xl font-bold text-brand-aman">AMAN</span>
            </div>
            <div class="mt-3 flex gap-1 text-[10px] font-bold">
                <span class="px-2 py-0.5 rounded bg-brand-aman text-white">SAFE</span>
                <span class="px-2 py-0.5 rounded bg-warning-100 text-gray-400">WARNING</span>
                <span class="px-2 py-0.5 rounded bg--100 text-gray-400">DANGER</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase tracking-wider">Node IoT</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-brand-text">2/2</span>
                <span class="text-xs font-semibold text-brand-aman">online</span>
            </div>
            <p class="text-[11px] text-brand-muted mt-3">Heartbeat terakhir 2 detik lalu</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase tracking-wider">Peak Getaran Hari Ini</span>
            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-3xl font-bold font-mono text-brand-text">0,42</span>
                <span class="text-xs text-brand-muted font-mono">g</span>
            </div>
            <p class="text-[11px] text-brand-muted mt-3 font-mono">08:00 · uji sistem terjadwal</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase tracking-wider">Peringatan Terkirim</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-brand-text">48</span>
                <span class="text-xs text-brand-muted">notifikasi</span>
            </div>
            <p class="text-[11px] text-brand-muted mt-3">Hari ini, ke 24 warga terdaftar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-brand-border shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-brand-border/60 pb-3">
                    <div>
                        <h2 class="font-bold text-base text-brand-text">Telemetri Getaran Real-Time</h2>
                        <p class="text-xs text-brand-muted">Node A vs Node B · 60 detik terakhir</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-mono">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#2F5BEA]"></span>Node A</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#C25E0B]"></span>Node B</span>
                        <span class="flex items-center gap-1.5 text-red-500 font-sans"><span class="w-3 border-t-2 border-dashed border-red-500"></span>Ambang batas</span>
                    </div>
                </div>

                <!-- Grafik Canvas -->
                <div class="h-56 mt-4 relative">
                    <canvas id="realtimeChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 pt-4 border-t border-brand-border text-xs mt-4">
                <div>
                    <span class="text-brand-muted block text-[11px]">Selisih waktu node terakhir</span>
                    <span class="font-mono font-bold text-sm text-brand-text">- (1 node)</span>
                </div>
                <div>
                    <span class="text-brand-muted block text-[11px]">Syarat konsensus</span>
                    <span class="font-mono font-bold text-sm text-brand-text">Δt &lt; 1,5 dtk</span>
                </div>
                <div class="bg-amber-50 p-2 rounded-xl border border-amber-200">
                    <span class="text-[10px] text-amber-800 block font-semibold">Kejadian terakhir</span>
                    <span class="text-xs text-amber-900 font-mono font-medium">10:47:04 · diabaikan, alarm tidak aktif</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-brand-border">
                    <h2 class="font-bold text-base text-brand-text">Monitoring Perangkat</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">2/2 ONLINE</span>
                </div>

                <div class="my-4 p-4 rounded-xl bg-gray-50 border border-brand-border text-xs relative h-28 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-[#2F5BEA] font-bold"><span class="w-2.5 h-2.5 rounded-full bg-[#2F5BEA]"></span>Node A</span>
                        <span class="text-gray-400 font-mono text-[10px]">RT 01</span>
                    </div>
                    <div class="border-t border-dashed border-gray-300 w-full transform -rotate-6"></div>
                    <div class="flex justify-end">
                        <span class="flex items-center gap-1.5 text-[#C25E0B] font-bold"><span class="w-2.5 h-2.5 rounded-full bg-[#C25E0B]"></span>Node B</span>
                    </div>
                </div>

                <!-- Info Node A & B -->
                <div class="space-y-4 text-xs">
                    <div class="p-3 rounded-xl bg-gray-50 border border-brand-border">
                        <div class="flex justify-between items-center font-bold">
                            <span class="text-brand-text flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#2F5BEA]"></span>ESP32 Node A</span>
                            <span class="text-emerald-700 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Online</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-brand-muted text-[11px]">
                            <div>Posisi: <b class="text-brand-text">Diagonal 1</b></div>
                            <div>Heartbeat: <b class="text-brand-text font-mono">2 dtk lalu</b></div>
                            <div>Sensor SW420: <span class="text-brand-text">Normal · tidak aktif</span></div>
                            <div>Akselerometer: <b class="text-brand-text font-mono">0,04 g · sehat</b></div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-gray-50 border border-brand-border">
                        <div class="flex justify-between items-center font-bold">
                            <span class="text-brand-text flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#C25E0B]"></span>ESP32 Node B</span>
                            <span class="text-emerald-700 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Online</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-brand-muted text-[11px]">
                            <div>Posisi: <b class="text-brand-text">Diagonal 2</b></div>
                            <div>Heartbeat: <b class="text-brand-text font-mono">3 dtk lalu</b></div>
                            <div>Sensor SW420: <span class="text-brand-text">Normal · tidak aktif</span></div>
                            <div>Akselerometer: <b class="text-brand-text font-mono">0,03 g · sehat</b></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Konfirmasi Warga & Perlu Ditindaklanjuti -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
            <h2 class="font-bold text-base text-brand-text">Konfirmasi Warga</h2>
            <p class="text-xs text-brand-muted mt-1">Kejadian terakhir: uji sistem 08:00 · 24 warga RT 01</p>

            <div class="mt-6 flex gap-6 items-center">
                <div class="text-center">
                    <span class="text-4xl font-bold font-mono text-brand-aman">19</span>
                    <span class="block text-xs font-semibold text-brand-muted mt-1">Aman</span>
                </div>
                <div class="text-center">
                    <span class="text-4xl font-bold font-mono text-brand-bahaya">2</span>
                    <span class="block text-xs font-semibold text-brand-muted mt-1">Butuh bantuan</span>
                </div>
                <div class="text-center">
                    <span class="text-4xl font-bold font-mono text-gray-400">3</span>
                    <span class="block text-xs font-semibold text-brand-muted mt-1">Belum merespons</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-base text-brand-text">Perlu Ditindaklanjuti</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/deteksi') }}" class="text-xs font-semibold text-brand-muted hover:text-brand-text">Lihat di peta</a>
                    <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-brand-bahaya font-bold text-[11px]">2 BUTUH BANTUAN</span>
                </div>
            </div>

            <div class="divide-y divide-brand-border text-xs">
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-brand-text">Rosa</div>
                        <div class="text-brand-muted text-[11px]">RT 01 · lapor 08:02 · 0812-••••-3341</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-red-50 text-brand-bahaya font-bold text-[10px]">Butuh bantuan</span>
                        <a href="tel:08123341" class="px-3 py-1.5 rounded-xl border border-brand-border hover:bg-gray-50 font-semibold">Hubungi</a>
                    </div>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-brand-text">Alfi</div>
                        <div class="text-brand-muted text-[11px]">RT 01 · lapor 08:03 · 0813-••••-7720</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-red-50 text-brand-bahaya font-bold text-[10px]">Butuh bantuan</span>
                        <a href="tel:08137720" class="px-3 py-1.5 rounded-xl border border-brand-border hover:bg-gray-50 font-semibold">Hubungi</a>
                    </div>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-brand-text">Rina</div>
                        <div class="text-brand-muted text-[11px]">RT 01 · notifikasi terkirim 08:00 · 0857-••••-1189</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded bg-gray-100 text-brand-muted font-bold text-[10px]">Belum merespons</span>
                        <a href="tel:08571189" class="px-3 py-1.5 rounded-xl border border-brand-border hover:bg-gray-50 font-semibold">Hubungi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Log Real-Time Tabel -->
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-brand-border">
            <div>
                <h2 class="font-bold text-base text-brand-text">Log Real-Time</h2>
                <p class="text-xs text-brand-muted">Riwayat deteksi, log alarm, dan telemetri level getaran terbaru</p>
            </div>
            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-medium">
                <button class="px-3 py-1.5 bg-brand-text text-white rounded-lg">Semua</button>
                <button class="px-3 py-1.5 text-brand-muted hover:text-brand-text rounded-lg">Deteksi</button>
                <button class="px-3 py-1.5 text-brand-muted hover:text-brand-text rounded-lg">Log Alarm</button>
                <button class="px-3 py-1.5 text-brand-muted hover:text-brand-text rounded-lg">Telemetri</button>
            </div>
        </div>

        <div class="overflow-x-auto mt-2">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="text-brand-muted uppercase text-[10px] tracking-wider border-b border-brand-border font-sans">
                        <th class="py-3 px-2">Waktu</th>
                        <th class="py-3 px-2">Jenis</th>
                        <th class="py-3 px-2">Sumber</th>
                        <th class="py-3 px-2 font-mono">Detail</th>
                        <th class="py-3 px-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border">
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">10:47:30</td>
                        <td class="py-3 px-2 font-sans font-semibold">Telemetri</td>
                        <td class="py-3 px-2 font-sans">Node A</td>
                        <td class="py-3 px-2 text-brand-muted">Getaran 0,04 g · SW420 tidak aktif</td>
                        <td class="py-3 px-2 font-sans"><span class="px-2 py-0.5 rounded text-emerald-800 font-bold bg-emerald-100">Normal</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">10:47:30</td>
                        <td class="py-3 px-2 font-sans font-semibold">Telemetri</td>
                        <td class="py-3 px-2 font-sans">Node B</td>
                        <td class="py-3 px-2 text-brand-muted">Getaran 0,03 g · SW420 tidak aktif</td>
                        <td class="py-3 px-2 font-sans"><span class="px-2 py-0.5 rounded text-emerald-800 font-bold bg-emerald-100">Normal</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">10:47:04</td>
                        <td class="py-3 px-2 font-sans font-semibold ">Deteksi</td>
                        <td class="py-3 px-2 font-sans">Node A</td>
                        <td class="py-3 px-2 text-brand-text">Getaran 0,24 g · Node B tidak terpicu · konsensus tidak terpenuhi</td>
                        <td class="py-3 px-2 font-sans"><span class="px-2 py-0.5 rounded bg-amber-100 text-brand-waspada font-bold ">Gangguan</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">10:47:04</td>
                        <td class="py-3 px-2 font-sans font-semibold">Log Alarm</td>
                        <td class="py-3 px-2 font-sans">Sirine</td>
                        <td class="py-3 px-2 text-brand-muted">Sirine tidak dibunyikan · notifikasi tidak dikirim</td>
                        <td class="py-3 px-2 font-sans"><span class="text-gray-500 font-medium px-2 py-0.5 rounded ">Tidak aktif</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">08:00:12</td>
                        <td class="py-3 px-2 font-sans font-semibold">Log Alarm</td>
                        <td class="py-3 px-2 font-sans">Sirine</td>
                        <td class="py-3 px-2 text-brand-muted">Sirine aktif 6 detik · uji sistem terjadwal</td>
                        <td class="py-3 px-2 font-sans"><span class=" px-2 py-0.5 text-blue-700 font-bold bg-blue-100 rounded">Selesai</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">08:00:06</td>
                        <td class="py-3 px-2 font-sans font-semibold">Deteksi</td>
                        <td class="py-3 px-2 font-sans">Node A + B</td>
                        <td class="py-3 px-2 text-brand-text">Δt 0,6 dtk · konsensus terpenuhi · puncak 0,42 g · uji sistem</td>
                        <td class="py-3 px-2 font-sans"><span class=" px-2 py-0.5 text-blue-700 font-bold bg-blue-100 rounded">Uji coba</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-2 text-brand-muted">06:31:18</td>
                        <td class="py-3 px-2 font-sans font-semibold">Telemetri</td>
                        <td class="py-3 px-2 font-sans">Node B</td>
                        <td class="py-3 px-2 text-brand-muted">Heartbeat kembali normal setelah 64 detik offline</td>
                        <td class="py-3 px-2 font-sans"><span class="px-4 py-0.5 rounded text-emerald-800 font-bold bg-emerald-100">Pulih</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    const ctx = document.getElementById('realtimeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['-60 dtk', '-50 dtk', '-40 dtk', '-30 dtk', '-20 dtk', '-10 dtk', 'sekarang'],
            datasets: [
                {
                    label: 'Node A',
                    data: [0.03, 0.04, 0.03, 0.24, 0.04, 0.03, 0.04],
                    borderColor: '#2F5BEA',
                    backgroundColor: '#2F5BEA',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: [0, 0, 0, 5, 0, 0, 0]
                },
                {
                    label: 'Node B',
                    data: [0.03, 0.03, 0.04, 0.05, 0.03, 0.03, 0.03],
                    borderColor: '#C25E0B',
                    backgroundColor: '#C25E0B',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 0
                },
                {
                    label: 'Ambang Batas (0,20 g)',
                    data: [0.20, 0.20, 0.20, 0.20, 0.20, 0.20, 0.20],
                    borderColor: '#B3261E',
                    borderDash: [5, 5],
                    borderWidth: 1.5,
                    pointRadius: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0.00, max: 0.30, grid: { color: '#E1E6DF' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endsection