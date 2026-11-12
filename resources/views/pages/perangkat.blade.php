@extends('layouts.app', ['title' => 'Perangkat - Sistem Deteksi Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Perangkat', 'subtitle' => 'Daftar perangkat dan sensor IoT'])
<main class="p-8 space-y-6">
    <div class="grid grid-cols-3 gap-5"><div class="bg-white p-5 rounded-2xl border"><span class="text-xs font-bold text-brand-muted">TOTAL</span><p id="deviceTotal" class="text-3xl font-bold">{{ $total }}</p></div><div class="bg-white p-5 rounded-2xl border"><span class="text-xs font-bold text-brand-muted">ONLINE</span><p id="deviceOnline" class="text-3xl font-bold text-brand-aman">{{ $online }}</p></div><div class="bg-white p-5 rounded-2xl border"><span class="text-xs font-bold text-brand-muted">OFFLINE</span><p id="deviceOffline" class="text-3xl font-bold">{{ $offline }}</p></div></div>
    <div class="bg-white p-6 rounded-2xl border shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr class="border-b text-brand-muted uppercase"><th class="py-3">Kode</th><th>Posisi</th><th>Lokasi</th><th>Status</th><th>Heartbeat</th><th>Sensor</th></tr></thead><tbody id="deviceRows" class="divide-y">
    @forelse($perangkat as $item)
        <tr data-live-id="{{ $item->getKey() }}"><td class="py-4 font-mono">{{ $item->kode_perangkat }}</td><td>{{ $item->posisi_node }}</td><td>{{ optional($item->lokasi)->nama_lokasi ?? '-' }}</td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->status === 'online' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($item->status) }}</span></td><td>{{ $item->heartbeat_terakhir?->timezone(config('app.display_timezone'))->format('d M Y H:i:s') ?? '-' }}</td><td>{{ $item->sensor->pluck('jenis_sensor')->implode(', ') ?: '-' }}</td></tr>
    @empty
        <tr data-live-empty><td colspan="6" class="py-8 text-center text-brand-muted">Belum ada data perangkat</td></tr>
    @endforelse
    </tbody></table></div><div class="mt-4">{{ $perangkat->links() }}</div></div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    (() => {
        const live = window.liveTable;
        const body = document.getElementById('deviceRows');
        const makeRow = () => {
            const row = document.createElement('tr');
            for (let index = 0; index < 6; index++) {
                const cell = document.createElement('td');
                if (index === 0) cell.className = 'py-4 font-mono';
                if (index === 3) {
                    const badge = document.createElement('span');
                    badge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                    cell.appendChild(badge);
                }
                row.appendChild(cell);
            }
            return row;
        };
        const updateRow = (row, item) => {
            live.setText(row.cells[0], item.code);
            live.setText(row.cells[1], item.position);
            live.setText(row.cells[2], item.location);
            const online = item.status === 'online';
            live.setBadge(row.cells[3].firstElementChild, online ? 'Online' : 'Offline',
                online ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600');
            live.setText(row.cells[4], live.formatDate(item.heartbeat_at));
            live.setText(row.cells[5], item.sensors);
        };

        live.poll(@json(route('perangkat.data')), result => {
            live.setText(document.getElementById('deviceTotal'), result.summary.total);
            live.setText(document.getElementById('deviceOnline'), result.summary.online);
            live.setText(document.getElementById('deviceOffline'), result.summary.offline);
            live.syncRows(body, result.items || [], makeRow, updateRow, 'Belum ada data perangkat', 6);
        }, @json($perangkat->currentPage()));
    })();
    });
</script>
@endsection