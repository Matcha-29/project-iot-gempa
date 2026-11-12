@extends('layouts.app', ['title' => 'Log Alarm - Sistem Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Log Alarm', 'subtitle' => 'Riwayat alarm dan notifikasi'])
<main class="p-8"><div class="bg-white p-6 rounded-2xl border shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr class="border-b text-brand-muted"><th class="py-3">Waktu</th><th>Jenis</th><th>Status</th><th>Durasi</th><th>Deteksi</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody id="alarmRows" class="divide-y">
@forelse($logAlarm as $item)
<tr data-live-id="{{ $item->getKey() }}"><td class="py-3">{{ $item->waktu_alarm?->timezone(config('app.display_timezone'))->format('d M Y H:i:s') ?? '-' }}</td><td>{{ $item->jenis_alarm }}</td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->status_alarm === 'menyala' ? 'bg-red-100 text-red-800' : ($item->status_alarm === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($item->status_alarm === 'dibatalkan' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-800')) }}">{{ ucfirst(str_replace('_', ' ', $item->status_alarm)) }}</span></td><td>{{ $item->durasi_detik !== null ? $item->durasi_detik.' dtk' : '-' }}</td><td>{{ optional($item->deteksi)->id_deteksi ?? '-' }}</td><td>{{ optional($item->petugas)->nama ?? '-' }}</td><td>{{ $item->catatan ?? '-' }}</td></tr>
@empty
<tr data-live-empty><td colspan="7" class="py-8 text-center text-brand-muted">Belum ada log alarm</td></tr>
@endforelse
</tbody></table></div><div class="mt-4">{{ $logAlarm->links() }}</div></div></main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    (() => {
        const live = window.liveTable;
        const body = document.getElementById('alarmRows');
        const makeRow = () => {
            const row = document.createElement('tr');
            for (let index = 0; index < 7; index++) {
                const cell = document.createElement('td');
                if (index === 0) cell.className = 'py-3';
                if (index === 2) {
                    const badge = document.createElement('span');
                    badge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                    cell.appendChild(badge);
                }
                row.appendChild(cell);
            }
            return row;
        };
        const updateRow = (row, item) => {
            live.setText(row.cells[0], live.formatDate(item.alarm_at));
            live.setText(row.cells[1], item.type);
            const status = (item.status || '').toLowerCase();
            const colors = status === 'menyala' ? 'bg-red-100 text-red-800'
                : (status === 'selesai' ? 'bg-emerald-100 text-emerald-800'
                    : (status === 'dibatalkan' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-800'));
            const label = status ? status.replaceAll('_', ' ').replace(/\b\w/g, char => char.toUpperCase()) : '-';
            live.setBadge(row.cells[2].firstElementChild, label, colors);
            live.setText(row.cells[3], item.duration_seconds === null ? '-' : `${item.duration_seconds} dtk`);
            live.setText(row.cells[4], item.detection_id);
            live.setText(row.cells[5], item.officer);
            live.setText(row.cells[6], item.notes);
        };

        live.poll(@json(route('log-alarm.data')), result => {
            live.syncRows(body, result.items || [], makeRow, updateRow, 'Belum ada log alarm', 7);
        }, @json($logAlarm->currentPage()));
    })();
    });
</script>
@endsection