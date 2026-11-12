@extends('layouts.app', ['title' => 'Dashboard - Sistem Deteksi Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Dashboard Monitoring', 'subtitle' => 'Ringkasan kondisi perangkat dan deteksi gempa'])
<main class="p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm"><span class="text-[11px] font-bold text-brand-muted uppercase">Perangkat Aktif</span><p id="dashboardActiveDevices" class="text-3xl font-bold mt-2">{{ $totalPerangkatAktif }}</p></div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm"><span class="text-[11px] font-bold text-brand-muted uppercase">Deteksi Gempa</span><p id="dashboardDetectionCount" class="text-3xl font-bold mt-2">{{ $jumlahDeteksiTerbaru }}</p></div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm"><span class="text-[11px] font-bold text-brand-muted uppercase">Alarm Aktif</span><p id="dashboardActiveAlarms" class="text-3xl font-bold mt-2">{{ $logAlarmAktif }}</p></div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm"><span class="text-[11px] font-bold text-brand-muted uppercase">Getaran Terakhir</span><p id="dashboardLatestVibration" class="text-3xl font-bold mt-2">{{ $telemetriTerakhir && $telemetriTerakhir->nilai_getaran_g !== null ? number_format($telemetriTerakhir->nilai_getaran_g, 3, ',', '.') . ' g' : '-' }}</p></div>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
        <h2 class="font-bold mb-4">Deteksi Gempa Terbaru</h2>
        <div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr class="border-b text-brand-muted"><th class="py-3">Waktu</th><th>Jenis</th><th>Status</th><th>Keparahan</th><th>Node</th></tr></thead><tbody id="dashboardDetectionRows" class="divide-y">
        @forelse($deteksiTerbaru as $item)
            <tr data-live-id="{{ $item->getKey() }}"><td class="py-3">{{ $item->waktu_node_a?->timezone(config('app.display_timezone'))->format('d M Y H:i:s') ?? '-' }}</td><td>{{ $item->jenis }}</td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->status_konsensus ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $item->status_konsensus ? 'Terpenuhi' : 'Tidak terpenuhi' }}</span></td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->tingkat_keparahan === 'kuat' ? 'bg-red-100 text-red-800' : ($item->tingkat_keparahan === 'sedang' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">{{ $item->tingkat_keparahan ? ucfirst($item->tingkat_keparahan) : '-' }}</span></td><td>{{ optional($item->perangkatA)->kode_perangkat }}{{ $item->perangkatB ? ' + '.optional($item->perangkatB)->kode_perangkat : '' }}</td></tr>
        @empty
            <tr data-live-empty><td colspan="5" class="py-8 text-center text-brand-muted">Belum ada data deteksi gempa</td></tr>
        @endforelse
        </tbody></table></div>
        <div class="mt-4">{{ $deteksiTerbaru->links() }}</div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    (() => {
        const live = window.liveTable;
        const body = document.getElementById('dashboardDetectionRows');
        const makeRow = () => {
            const row = document.createElement('tr');
            for (let index = 0; index < 5; index++) {
                const cell = document.createElement('td');
                if (index === 0) cell.className = 'py-3';
                if (index === 2 || index === 3) {
                    const badge = document.createElement('span');
                    badge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                    cell.appendChild(badge);
                }
                row.appendChild(cell);
            }
            return row;
        };
        const updateRow = (row, item) => {
            live.setText(row.cells[0], live.formatDate(item.detected_at));
            live.setText(row.cells[1], item.kind);
            live.setBadge(row.cells[2].firstElementChild, item.consensus ? 'Terpenuhi' : 'Tidak terpenuhi',
                item.consensus ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700');
            const severity = (item.severity || '').toLowerCase();
            const severityColors = severity === 'kuat' ? 'bg-red-100 text-red-800'
                : (severity === 'sedang' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
            live.setBadge(row.cells[3].firstElementChild, severity ? severity[0].toUpperCase() + severity.slice(1) : '-', severityColors);
            live.setText(row.cells[4], [item.node_a, item.node_b].filter(Boolean).join(' + '));
        };

        live.poll(@json(route('dashboard.data')), result => {
            live.setText(document.getElementById('dashboardActiveDevices'), result.summary.active_devices);
            live.setText(document.getElementById('dashboardDetectionCount'), result.summary.earthquake_detections);
            live.setText(document.getElementById('dashboardActiveAlarms'), result.summary.active_alarms);
            const vibration = result.summary.latest_vibration_g;
            live.setText(document.getElementById('dashboardLatestVibration'),
                vibration === null ? '-' : `${live.formatNumber(vibration)} g`);
            live.syncRows(body, result.items || [], makeRow, updateRow, 'Belum ada data deteksi gempa', 5);
        }, @json($deteksiTerbaru->currentPage()));
    })();
    });
</script>
@endsection