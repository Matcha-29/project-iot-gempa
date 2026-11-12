@extends('layouts.app', ['title' => 'Deteksi - Sistem Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Deteksi', 'subtitle' => 'Riwayat hasil deteksi dan konsensus perangkat'])
<main class="p-8"><div class="bg-white p-6 rounded-2xl border shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr class="border-b text-brand-muted"><th class="py-3">Waktu</th><th>Jenis</th><th>Node A</th><th>Node B</th><th>Jeda</th><th>Konsensus</th><th>Keparahan</th><th>Deskripsi</th></tr></thead><tbody id="detectionRows" class="divide-y">
@forelse($deteksi as $item)
<tr data-live-id="{{ $item->getKey() }}"><td class="py-3">{{ $item->waktu_node_a?->timezone(config('app.display_timezone'))->format('d M Y H:i:s') ?? '-' }}</td><td>{{ $item->jenis }}</td><td>{{ optional($item->perangkatA)->kode_perangkat ?? '-' }}</td><td>{{ optional($item->perangkatB)->kode_perangkat ?? '-' }}</td><td>{{ $item->jeda_waktu_node !== null ? $item->jeda_waktu_node.' ms' : '-' }}</td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->status_konsensus ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $item->status_konsensus ? 'Terpenuhi' : 'Tidak terpenuhi' }}</span></td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->tingkat_keparahan === 'kuat' ? 'bg-red-100 text-red-800' : ($item->tingkat_keparahan === 'sedang' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">{{ $item->tingkat_keparahan ? ucfirst($item->tingkat_keparahan) : '-' }}</span></td><td>{{ $item->deskripsi ?? '-' }}</td></tr>
@empty
<tr data-live-empty><td colspan="8" class="py-8 text-center text-brand-muted">Belum ada data deteksi gempa</td></tr>
@endforelse
</tbody></table></div><div class="mt-4">{{ $deteksi->links() }}</div></div></main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    (() => {
        const live = window.liveTable;
        const body = document.getElementById('detectionRows');
        const makeRow = () => {
            const row = document.createElement('tr');
            for (let index = 0; index < 8; index++) {
                const cell = document.createElement('td');
                if (index === 0) cell.className = 'py-3';
                if (index === 5 || index === 6) {
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
            live.setText(row.cells[2], item.node_a);
            live.setText(row.cells[3], item.node_b);
            live.setText(row.cells[4], item.delay_ms === null ? '-' : `${item.delay_ms} ms`);
            live.setBadge(row.cells[5].firstElementChild, item.consensus ? 'Terpenuhi' : 'Tidak terpenuhi',
                item.consensus ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700');
            const severity = (item.severity || '').toLowerCase();
            const colors = severity === 'kuat' ? 'bg-red-100 text-red-800'
                : (severity === 'sedang' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
            live.setBadge(row.cells[6].firstElementChild, severity ? severity[0].toUpperCase() + severity.slice(1) : '-', colors);
            live.setText(row.cells[7], item.description);
        };

        live.poll(@json(route('deteksi.data')), result => {
            live.syncRows(body, result.items || [], makeRow, updateRow, 'Belum ada data deteksi gempa', 8);
        }, @json($deteksi->currentPage()));
    })();
    });
</script>
@endsection