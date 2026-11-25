@extends('layouts.app', ['title' => 'Dashboard - Sistem Deteksi Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Dashboard Monitoring', 'subtitle' => ($lokasi ? $lokasi->nama_lokasi . ' · ' : '') . $totalNodes . ' node ESP32 diagonal · konsensus jika selisih waktu < 1,5 detik'])

<main class="p-6 space-y-5 overflow-y-auto">
    {{-- ═══ Action Bar Petugas: Uji Sirine & Nyatakan Situasi Aman ═══ --}}
    <div class="bg-white p-4 rounded-2xl border border-brand-border shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
            </div>
            <div>
                <h3 class="text-xs font-bold text-brand-text uppercase tracking-wide">Panel Tindakan Cepat Petugas</h3>
                <p class="text-[11px] text-brand-muted">Kendali uji coba sirine dan status kedaruratan wilayah RT</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($ujiSirineAktif)
                <form action="{{ route('dashboard.uji-sirine.stop') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-2 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-white"></span>
                        Uji Sirine Aktif · Hentikan
                    </button>
                </form>
            @else
                <form action="{{ route('dashboard.uji-sirine') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        Uji Coba Sirine (60 Detik)
                    </button>
                </form>
            @endif

            <form action="{{ route('dashboard.situasi-aman') }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menyatakan situasi wilayah kembali aman dan terkendali?')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Nyatakan Situasi Aman / Terkendali
                </button>
            </form>
        </div>
    </div>

    {{-- ═══ 4 Summary Cards ═══ --}}
    <div class="grid grid-cols-4 gap-4">
        {{-- Card 1: Status Sistem --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">STATUS SISTEM</span>
            <div class="mt-2">
                <span id="systemStatusBadge" class="inline-flex items-center justify-center px-5 py-2 rounded-lg text-2xl font-black tracking-wider {{ $systemStatus['class'] }}">
                    {{ $systemStatus['label'] }}
                </span>
            </div>
            <div class="flex items-center gap-2 mt-3">
                <span class="inline-block w-2.5 h-2.5 rounded-full {{ $systemStatus['key'] === 'safe' ? 'bg-brand-aman' : 'bg-gray-300' }}"></span>
                <span class="text-[9px] font-bold {{ $systemStatus['key'] === 'safe' ? 'text-brand-aman' : 'text-gray-400' }}">SAFE</span>
                <span class="inline-block w-2.5 h-2.5 rounded-full {{ $systemStatus['key'] === 'warning' ? 'bg-amber-500' : 'bg-gray-300' }}"></span>
                <span class="text-[9px] font-bold {{ $systemStatus['key'] === 'warning' ? 'text-amber-600' : 'text-gray-400' }}">WARNING</span>
                <span class="inline-block w-2.5 h-2.5 rounded-full {{ $systemStatus['key'] === 'danger' ? 'bg-red-600' : 'bg-gray-300' }}"></span>
                <span class="text-[9px] font-bold {{ $systemStatus['key'] === 'danger' ? 'text-red-600' : 'text-gray-400' }}">DANGER</span>
            </div>
        </div>

        {{-- Card 2: Node IoT --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">NODE IOT</span>
            <p id="nodeOnlineCount" class="text-3xl font-bold mt-2">{{ $onlineCount }}/{{ $totalNodes }} <span class="text-base font-semibold text-brand-muted">Online</span></p>
            @php
                $latestHb = collect($nodeStatus)->min('heartbeat_age_seconds');
            @endphp
            <p id="nodeHeartbeatInfo" class="text-[11px] text-brand-muted mt-1">
                Heartbeat terakhir {{ $latestHb !== null ? ($latestHb < 60 ? $latestHb . ' dtk lalu' : round($latestHb / 60) . ' mnt lalu') : '-' }}
            </p>
        </div>

        {{-- Card 3: Peak Getaran Hari Ini --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">PEAK GETARAN HARI INI</span>
            <p id="peakToday" class="text-3xl font-bold mt-2">{{ $peakToday !== null ? number_format((float)$peakToday, 2, ',', '.') : '-' }} <span class="text-base font-semibold text-brand-muted">g</span></p>
            <p id="peakTodayTime" class="text-[11px] text-brand-muted mt-1">{{ $peakTodayTime ? $peakTodayTime . ' · uji sistem terjadwal' : 'Belum ada data hari ini' }}</p>
        </div>

        {{-- Card 4: Peringatan Terkirim --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">PERINGATAN TERKIRIM</span>
            <p id="notifCount" class="text-3xl font-bold mt-2">{{ $totalNotifikasi }} <span class="text-base font-semibold text-brand-muted">notifikasi</span></p>
            <p class="text-[11px] text-brand-muted mt-1">Hari ini, ke {{ $notifikasiHariIni }} warga terdaftar</p>
        </div>
    </div>

    {{-- ═══ Row 2: Chart + Monitoring Perangkat ═══ --}}
    <div class="grid grid-cols-5 gap-4">
        {{-- Chart: 3 cols --}}
        <div class="col-span-3 bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-xs font-bold text-brand-text uppercase tracking-wide">Telemetri Getaran Real-Time</h3>
                    <p class="text-[10px] text-brand-muted mt-0.5">Node A vs Node B · 60 data terakhir</p>
                </div>
                <div class="flex items-center gap-5 text-[10px] font-semibold text-brand-muted">
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 h-0.5 bg-brand-nodeA rounded"></span> Node A
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 h-0.5 bg-brand-nodeB rounded"></span> Node B
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 border-t border-dashed border-red-400"></span> Ambang batas
                    </div>
                </div>
            </div>
            <div style="height: 200px;">
                <canvas id="vibrationChart"></canvas>
            </div>
        </div>

        {{-- Monitoring Perangkat: 2 cols --}}
        <div class="col-span-2 bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-brand-text uppercase tracking-wide">Monitoring Perangkat</h3>
                <span id="monitorOnlineBadge" class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $onlineCount === $totalNodes ? 'bg-brand-amanBg text-brand-aman' : 'bg-red-50 text-red-600' }}">
                    {{ $onlineCount }}/{{ $totalNodes }} ONLINE
                </span>
            </div>

            <div class="space-y-3">
                @foreach($nodeStatus as $i => $node)
                    <div class="flex items-start gap-3 p-3 rounded-xl {{ $node['online'] ? 'bg-gray-50' : 'bg-red-50/50' }} border border-brand-border/50">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $i === 0 ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-brand-text">{{ $node['mikrokontroler'] }} {{ $node['code'] }}</span>
                                <span id="nodeOnline{{ $i }}" class="flex items-center gap-1 text-[10px] font-bold {{ $node['online'] ? 'text-brand-aman' : 'text-red-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $node['online'] ? 'bg-brand-aman animate-pulse' : 'bg-red-400' }}"></span>
                                    {{ $node['online'] ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-0.5 mt-1.5 text-[10px] text-brand-muted">
                                <div><span class="text-brand-text font-medium">Posisi:</span> {{ $node['posisi'] ?? '-' }}</div>
                                <div><span class="text-brand-text font-medium">Heartbeat:</span>
                                    <span id="nodeHb{{ $i }}">{{ $node['heartbeat_age_seconds'] !== null ? ($node['heartbeat_age_seconds'] < 60 ? $node['heartbeat_age_seconds'] . ' dtk lalu' : round($node['heartbeat_age_seconds'] / 60) . ' mnt lalu') : '-' }}</span>
                                </div>
                                @foreach($node['sensors'] as $sensor)
                                    <div><span class="text-brand-text font-medium">Sensor {{ strtoupper(substr($sensor['jenis'], 0, 3)) }}:</span></div>
                                    <div>{{ number_format($node['vibration_g'] ?? 0, 3, ',', '.') }} g · {{ $sensor['status'] }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ Row 3: Konfirmasi Warga + Perlu Ditindaklanjuti ═══ --}}
    <div class="grid grid-cols-5 gap-4">
        {{-- Konfirmasi Warga: 2 cols --}}
        <div class="col-span-2 bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-brand-text uppercase tracking-wide">Konfirmasi Warga</h3>
            </div>
            <p class="text-[10px] text-brand-muted -mt-2 mb-3">
                @if($responsWarga['waktu'])
                    Kejadian terakhir · {{ \Carbon\Carbon::parse($responsWarga['waktu'])->timezone(config('app.display_timezone'))->format('d/m H:i') }}
                @else
                    Belum ada kejadian gempa
                @endif
            </p>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-brand-amanBg rounded-xl p-3">
                    <p id="wargaAman" class="text-3xl font-bold text-brand-aman">{{ $responsWarga['aman'] }}</p>
                    <p class="text-[10px] font-semibold text-brand-aman mt-1">Aman</p>
                </div>
                <div class="bg-brand-bahayaBg rounded-xl p-3">
                    <p id="wargaBantuan" class="text-3xl font-bold text-brand-bahaya">{{ $responsWarga['butuh_bantuan'] }}</p>
                    <p class="text-[10px] font-semibold text-brand-bahaya mt-1">Butuh bantuan</p>
                </div>
                <div class="bg-gray-100 rounded-xl p-3">
                    <p id="wargaBelum" class="text-3xl font-bold text-gray-500">{{ $responsWarga['belum_merespons'] }}</p>
                    <p class="text-[10px] font-semibold text-gray-500 mt-1">Belum merespons</p>
                </div>
            </div>
        </div>

        {{-- Respons Warga Real-Time: 3 cols --}}
        <div class="col-span-3 bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-bold text-brand-text uppercase tracking-wide">Monitoring Respons Warga (Real-Time)</h3>
                    <p class="text-[10px] text-brand-muted mt-0.5">Laporan keselamatan & permohonan bantuan dari warga</p>
                </div>
                @if($responsWarga['butuh_bantuan'] > 0)
                    <span id="badgeButuhBantuan" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 animate-pulse">
                        {{ $responsWarga['butuh_bantuan'] }} BUTUH BANTUAN
                    </span>
                @else
                    <span id="badgeButuhBantuan" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        SEMUA AMAN
                    </span>
                @endif
            </div>
            <div class="space-y-2 max-h-[170px] overflow-y-auto" id="wargaResponsList">
                @forelse($responsWarga['daftar_respons'] ?? [] as $w)
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $w['status'] === 'butuh_bantuan' ? 'bg-red-50/80 border border-red-200' : 'bg-gray-50 border border-brand-border/50' }}">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-bold text-brand-text">{{ $w['nama'] }}</p>
                                <span class="text-[10px] text-brand-muted font-medium">({{ $w['nomor_rumah'] ?? '-' }})</span>
                            </div>
                            <p class="text-[10px] text-brand-muted mt-0.5">{{ $w['no_telepon'] }} · Lapor {{ $w['waktu'] }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold {{ $w['status'] === 'butuh_bantuan' ? 'bg-red-600 text-white' : 'bg-brand-amanBg text-brand-aman' }}">
                                {{ $w['status_label'] }}
                            </span>
                            @if(!empty($w['no_telepon']) && $w['no_telepon'] !== '-')
                                <a href="tel:{{ $w['no_telepon'] }}" class="px-3 py-1 rounded-lg border border-brand-border text-[10px] font-semibold text-brand-text hover:bg-gray-100 transition">Hubungi</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-brand-muted text-xs">
                        <p>Belum ada laporan dari warga saat ini</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══ Row 4: Log Real-Time ═══ --}}
    <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xs font-bold text-brand-text uppercase tracking-wide">Log Real-Time</h3>
                <p class="text-[10px] text-brand-muted mt-0.5">Riwayat deteksi, log alarm, dan telemetri level getaran terbaru</p>
            </div>
        </div>

        {{-- Tab filters --}}
        <div class="flex items-center gap-2 mb-4" id="logTabContainer">
            <button data-log-filter="" class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition bg-brand-text text-white shadow-sm">Semua</button>
            <button data-log-filter="deteksi" class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition bg-[#F3F5F1] text-brand-muted hover:text-brand-text">Deteksi</button>
            <button data-log-filter="log-alarm" class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition bg-[#F3F5F1] text-brand-muted hover:text-brand-text">Log Alarm</button>
            <button data-log-filter="telemetri" class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition bg-[#F3F5F1] text-brand-muted hover:text-brand-text">Telemetri</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b text-brand-muted">
                        <th class="py-3 w-36">WAKTU</th>
                        <th class="w-28">JENIS</th>
                        <th class="w-28">SUMBER</th>
                        <th class="pr-6">DETAIL</th>
                        <th class="w-36 text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody id="logRows" class="divide-y">
                    @forelse($realtimeLog as $log)
                        @php
                            $statusColors = match($log['status_key']) {
                                'bahaya' => 'bg-red-100 text-red-800',
                                'waspada' => 'bg-amber-100 text-amber-800',
                                'gangguan' => 'bg-amber-100 text-amber-800',
                                'selesai' => 'bg-blue-100 text-blue-800',
                                default => 'bg-emerald-100 text-emerald-800',
                            };
                        @endphp
                        <tr data-live-id="{{ $log['id'] }}">
                            <td class="py-2.5 font-mono text-[11px]">{{ $log['waktu'] ? \Carbon\Carbon::parse($log['waktu'])->timezone(config('app.display_timezone'))->format('H:i:s.v') : '-' }}</td>
                            <td><span class="text-[10px] font-semibold">{{ $log['jenis'] }}</span></td>
                            <td><span class="text-[10px] font-medium text-brand-muted">{{ $log['sumber'] }}</span></td>
                            <td class="text-[10px] text-brand-muted pr-6 truncate">{{ $log['detail'] }}</td>
                            <td class="text-center">
                                <span class="inline-flex items-center justify-center w-28 px-2 py-1 text-[10px] font-bold rounded-full {{ $statusColors }}">{{ $log['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr data-live-empty>
                            <td colspan="5" class="py-8 text-center text-brand-muted">Belum ada data log</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Navigasi Pagination --}}
        <div class="mt-4">
            {{ $realtimeLog->links() }}
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
(() => {
    const live = window.liveTable;
    const perangkatList = @json(collect($nodeStatus)->pluck('code')->values());
    const nodeColors = ['#2F5BEA', '#C25E0B'];
    const nodeBgColors = ['rgba(47,91,234,0.08)', 'rgba(194,94,11,0.08)'];
    let activeLogFilter = '';

    // Cek parameter page pada URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = parseInt(urlParams.get('page') || '1', 10);

    // ═══ Chart Setup ═══
    const chartCtx = document.getElementById('vibrationChart').getContext('2d');
    const initialChartData = @json($chartData);

    const chartDatasets = perangkatList.map((code, i) => ({
        label: `Node ${String.fromCharCode(65 + i)} · ${code}`,
        data: (initialChartData[code] || []).map(d => d.vibration_g),
        borderColor: nodeColors[i] || '#888',
        backgroundColor: nodeBgColors[i] || 'rgba(0,0,0,0.05)',
        borderWidth: 2,
        pointRadius: 1.5,
        pointHoverRadius: 4,
        tension: 0.3,
        fill: true,
    }));

    // Threshold line
    chartDatasets.push({
        label: 'Ambang batas',
        data: Array(60).fill(0.5),
        borderColor: '#EF4444',
        borderWidth: 1,
        borderDash: [4, 4],
        pointRadius: 0,
        fill: false,
    });

    const firstNodeData = initialChartData[perangkatList[0]] || [];
    const chartLabels = firstNodeData.map(d => d.time);

    const vibrationChart = new Chart(chartCtx, {
        type: 'line',
        data: { labels: chartLabels, datasets: chartDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y !== null ? ctx.parsed.y.toFixed(3) + ' g' : '-'}`
                    }
                }
            },
            scales: {
                x: { display: true, grid: { display: false }, ticks: { font: { size: 8 }, maxRotation: 0, maxTicksLimit: 10 } },
                y: { display: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 9 }, callback: v => v.toFixed(2) + ' g' }, beginAtZero: true }
            }
        }
    });

    // ═══ Status style helpers ═══
    const systemStatusStyles = {
        safe: { label: 'AMAN', class: 'bg-brand-aman text-white' },
        warning: { label: 'WASPADA', class: 'bg-amber-500 text-white' },
        danger: { label: 'BAHAYA', class: 'bg-red-600 text-white' },
    };

    const logStatusColors = key => {
        const map = { bahaya: 'bg-red-100 text-red-800', waspada: 'bg-amber-100 text-amber-800', gangguan: 'bg-amber-100 text-amber-800', selesai: 'bg-blue-100 text-blue-800' };
        return map[key] || 'bg-emerald-100 text-emerald-800';
    };

    function formatHbAge(seconds) {
        if (seconds === null || seconds === undefined) return '-';
        return seconds < 60 ? seconds + ' dtk lalu' : Math.round(seconds / 60) + ' mnt lalu';
    }

    // ═══ Update functions ═══
    function updateSummaryCards(result) {
        // System status
        const badge = document.getElementById('systemStatusBadge');
        if (badge && result.system_status) {
            const st = systemStatusStyles[result.system_status.key] || systemStatusStyles.safe;
            live.setText(badge, st.label);
            badge.className = `inline-flex items-center justify-center px-5 py-2 rounded-lg text-2xl font-black tracking-wider ${st.class}`;
        }

        // Node count
        const s = result.summary;
        live.setText(document.getElementById('nodeOnlineCount'),
            `${s.online_count}/${s.total_nodes} Online`);

        // Peak today
        const peakEl = document.getElementById('peakToday');
        if (peakEl) live.setText(peakEl,
            s.peak_today !== null ? `${live.formatNumber(s.peak_today, 2)} g` : '- g');

        const peakTimeEl = document.getElementById('peakTodayTime');
        if (peakTimeEl) live.setText(peakTimeEl, s.peak_today_time ? s.peak_today_time + ' · uji sistem terjadwal' : 'Belum ada data hari ini');

        // Notif
        live.setText(document.getElementById('notifCount'), `${s.total_notifikasi} notifikasi`);
    }

    function updateNodeMonitoring(nodes) {
        if (!nodes) return;
        const monBadge = document.getElementById('monitorOnlineBadge');
        const online = nodes.filter(n => n.online).length;
        const total = nodes.length;
        if (monBadge) {
            live.setText(monBadge, `${online}/${total} ONLINE`);
            monBadge.className = `text-[10px] font-bold px-2.5 py-1 rounded-full ${online === total ? 'bg-brand-amanBg text-brand-aman' : 'bg-red-50 text-red-600'}`;
        }
        nodes.forEach((n, i) => {
            const onlineEl = document.getElementById(`nodeOnline${i}`);
            if (onlineEl) {
                const dot = onlineEl.querySelector('span');
                if (dot) dot.className = `w-1.5 h-1.5 rounded-full ${n.online ? 'bg-brand-aman animate-pulse' : 'bg-red-400'}`;
                onlineEl.className = `flex items-center gap-1 text-[10px] font-bold ${n.online ? 'text-brand-aman' : 'text-red-500'}`;
                const textNode = onlineEl.lastChild;
                if (textNode && textNode.nodeType === 3) {
                    const desired = n.online ? ' Online' : ' Offline';
                    if (textNode.textContent.trim() !== desired.trim()) textNode.textContent = desired;
                }
            }
            const hbEl = document.getElementById(`nodeHb${i}`);
            if (hbEl) live.setText(hbEl, formatHbAge(n.heartbeat_age_seconds));
        });
    }

    function updateChart(chartDataNew) {
        if (!chartDataNew) return;
        perangkatList.forEach((code, i) => {
            const nodeData = chartDataNew[code] || [];
            vibrationChart.data.datasets[i].data = nodeData.map(d => d.vibration_g);
            if (i === 0) {
                vibrationChart.data.labels = nodeData.map(d => d.time);
                // Update threshold line length
                vibrationChart.data.datasets[perangkatList.length].data = Array(nodeData.length).fill(0.5);
            }
        });
        vibrationChart.update('none');
    }

    function updateWarga(rw) {
        if (!rw) return;
        live.setText(document.getElementById('wargaAman'), rw.aman);
        live.setText(document.getElementById('wargaBantuan'), rw.butuh_bantuan);
        live.setText(document.getElementById('wargaBelum'), rw.belum_merespons);
    }

    function updateLogTable(logs) {
        const tbody = document.getElementById('logRows');
        if (!tbody || !logs) return;

        if (!logs.length) {
            if (!tbody.querySelector('[data-live-empty]')) {
                const row = document.createElement('tr');
                row.dataset.liveEmpty = '';
                const cell = document.createElement('td');
                cell.colSpan = 5;
                cell.className = 'py-8 text-center text-brand-muted';
                cell.textContent = 'Belum ada data log';
                row.appendChild(cell);
                tbody.replaceChildren(row);
            }
            return;
        }

        const existing = new Map(
            Array.from(tbody.querySelectorAll('tr[data-live-id]'))
                .map(r => [r.dataset.liveId, r])
        );
        tbody.querySelector('[data-live-empty]')?.remove();

        logs.forEach((log, idx) => {
            const id = log.id;
            let row = existing.get(id);
            if (!row) {
                row = document.createElement('tr');
                row.dataset.liveId = id;
                for (let c = 0; c < 5; c++) {
                    const td = document.createElement('td');
                    if (c === 0) td.className = 'py-2.5 font-mono text-[11px]';
                    if (c === 3) td.className = 'text-[10px] text-brand-muted pr-6 truncate';
                    if (c === 4) {
                        td.className = 'text-center';
                        const badge = document.createElement('span');
                        badge.className = 'inline-flex items-center justify-center w-28 px-2 py-1 text-[10px] font-bold rounded-full';
                        td.appendChild(badge);
                    }
                    row.appendChild(td);
                }
            }
            existing.delete(id);

            const cells = row.cells;
            live.setText(cells[0], log.waktu ? new Date(log.waktu).toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit', fractionalSecondDigits: 3 }) : '-');
            cells[1].innerHTML = `<span class="text-[10px] font-semibold">${log.jenis}</span>`;
            cells[2].innerHTML = `<span class="text-[10px] font-medium text-brand-muted">${log.sumber}</span>`;
            cells[3].className = 'text-[10px] text-brand-muted pr-6 truncate';
            live.setText(cells[3], log.detail);
            const statusBadge = cells[4].firstElementChild;
            if (statusBadge) {
                live.setText(statusBadge, log.status);
                statusBadge.className = `inline-flex items-center justify-center w-28 px-2 py-1 text-[10px] font-bold rounded-full ${logStatusColors(log.status_key)}`;
            }

            const cur = tbody.children[idx];
            if (cur !== row) tbody.insertBefore(row, cur || null);
        });
        existing.forEach(r => r.remove());
    }

    // ═══ Log tab switching ═══
    document.getElementById('logTabContainer').addEventListener('click', e => {
        const btn = e.target.closest('[data-log-filter]');
        if (!btn) return;
        activeLogFilter = btn.dataset.logFilter;
        document.querySelectorAll('#logTabContainer button').forEach(b => {
            const isActive = b.dataset.logFilter === activeLogFilter;
            b.className = `rounded-full px-4 py-1.5 text-[11px] font-semibold transition ${isActive ? 'bg-brand-text text-white shadow-sm' : 'bg-[#F3F5F1] text-brand-muted hover:text-brand-text'}`;
        });
        pollNow();
    });

    // ═══ Live Polling ═══
    let pollTimer = null;
    const baseUrl = @json(route('dashboard.data'));

    async function pollNow() {
        try {
            const url = new URL(baseUrl, window.location.origin);
            url.searchParams.set('page', 1);
            if (activeLogFilter) url.searchParams.set('filter', activeLogFilter);

            const resp = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const result = await resp.json();

            updateSummaryCards(result);
            updateNodeMonitoring(result.node_status);
            updateChart(result.chart_data);
            updateWarga(result.respons_warga);

            // Perbarui baris tabel HANYA jika pengguna berada di halaman 1
            if (currentPage === 1) {
                updateLogTable(result.realtime_log);
            }

            live.setText(document.getElementById('liveConnectionLabel'), 'LIVE');
            const dot = document.getElementById('liveConnectionDot');
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
        } catch (err) {
            live.setText(document.getElementById('liveConnectionLabel'), 'TERPUTUS');
            const dot = document.getElementById('liveConnectionDot');
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-red-500';
            console.error('Dashboard poll error:', err);
        }
    }

    function startPolling() {
        pollNow();
        pollTimer = setInterval(pollNow, 2000);
    }

    startPolling();
})();
});
</script>
@endsection
