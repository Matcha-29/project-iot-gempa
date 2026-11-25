@extends('layouts.app', ['title' => 'Deteksi - Sistem Deteksi Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Deteksi dan Peta Respons Warga', 'subtitle' => 'Riwayat kejadian getaran dan hasil verifikasi konsensus 2 node'])

<main class="p-6 space-y-5">
    {{-- ═══ Header Row ═══ --}}
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-bold text-brand-text">Deteksi</h2>
            <p class="text-xs text-brand-muted mt-0.5">Riwayat kejadian getaran dan hasil verifikasi konsensus 2 node</p>
        </div>
        <button class="px-4 py-2 rounded-xl border border-brand-border text-xs font-semibold text-brand-text hover:bg-gray-50 transition flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Unduh Laporan
        </button>
    </div>

    {{-- ═══ Table Card ═══ --}}
    <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
        {{-- Filter tabs --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2" id="filterTabContainer">
                <a href="{{ url('/deteksi') }}" data-filter=""
                   class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition {{ $filter === '' ? 'bg-brand-text text-white shadow-sm' : 'bg-[#F3F5F1] text-brand-muted hover:text-brand-text' }}">
                    Semua
                </a>
                <a href="{{ url('/deteksi?filter=gempa') }}" data-filter="gempa"
                   class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition {{ $filter === 'gempa' ? 'bg-brand-text text-white shadow-sm' : 'bg-[#F3F5F1] text-brand-muted hover:text-brand-text' }}">
                    Gempa terkonfirmasi
                </a>
                <a href="{{ url('/deteksi?filter=gangguan') }}" data-filter="gangguan"
                   class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition {{ $filter === 'gangguan' ? 'bg-brand-text text-white shadow-sm' : 'bg-[#F3F5F1] text-brand-muted hover:text-brand-text' }}">
                    Gangguan lokal
                </a>
                <a href="{{ url('/deteksi?filter=uji_coba') }}" data-filter="uji_coba"
                   class="rounded-full px-4 py-1.5 text-[11px] font-semibold transition {{ $filter === 'uji_coba' ? 'bg-brand-text text-white shadow-sm' : 'bg-[#F3F5F1] text-brand-muted hover:text-brand-text' }}">
                    Uji coba
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b text-brand-muted uppercase text-[10px] font-bold tracking-wide">
                        <th class="py-3">WAKTU</th>
                        <th>LOKASI</th>
                        <th>NODE</th>
                        <th>SELISIH</th>
                        <th>KONSENSUS</th>
                        <th>KEPARAHAN</th>
                        <th>RESPONS WARGA</th>
                    </tr>
                </thead>
                <tbody id="detectionRows" class="divide-y">
                    @forelse($deteksi as $item)
                        @php
                            $lokasiName = optional(optional($item->perangkatA)->lokasi)->nama_lokasi ?? optional($lokasi)->nama_lokasi ?? '-';
                            $nodeLabel = optional($item->perangkatA)->kode_perangkat ?? '-';
                            if ($item->perangkatB) $nodeLabel .= ' + ' . $item->perangkatB->kode_perangkat;
                            $jedaFormatted = $item->jeda_waktu_node !== null ? number_format((float)$item->jeda_waktu_node, 1, ',', '.') . ' dtk' : '–';
                            $isKonsensus = $item->status_konsensus;
                            $keparahanKey = strtolower($item->tingkat_keparahan ?? '');

                            // Respons warga
                            $responsAman = $item->responsWarga->where('status', 'aman')->count();
                            $responsBantuan = $item->responsWarga->where('status', 'butuh_bantuan')->count();
                            $responsBelum = $item->responsWarga->whereNull('status')->count();
                            $responsTotal = $item->responsWarga->count();
                            $responsText = $responsTotal > 0
                                ? "{$responsAman} aman · {$responsBantuan} bantuan · {$responsBelum} belum"
                                : ($item->jenis === 'gangguan_lokal' ? 'Tidak ada notifikasi' : '-');

                            // Jenis label for keparahan badge
                            $keparahanLabel = $item->tingkat_keparahan ? ucfirst($item->tingkat_keparahan) : ($item->jenis === 'gangguan_lokal' ? 'Gangguan lokal' : '-');
                            $keparahanClass = match($keparahanKey) {
                                'kuat' => 'bg-red-100 text-red-800',
                                'sedang' => 'bg-amber-100 text-amber-800',
                                'ringan' => 'bg-gray-100 text-gray-700',
                                default => $item->jenis === 'gangguan_lokal' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700',
                            };

                            // Waktu formatting
                            $waktuCarbon = $item->waktu_node_a?->timezone(config('app.display_timezone'));
                            $isToday = $waktuCarbon && $waktuCarbon->isToday();
                            $isYesterday = $waktuCarbon && $waktuCarbon->isYesterday();
                            $waktuPrefix = $isToday ? 'Hari ini' : ($isYesterday ? 'Kemarin' : ($waktuCarbon ? $waktuCarbon->format('d M') : ''));
                            $waktuTime = $waktuCarbon ? $waktuCarbon->format('H:i:s') : '-';
                        @endphp
                        <tr data-live-id="{{ $item->getKey() }}" data-deteksi-id="{{ $item->getKey() }}" class="cursor-pointer hover:bg-gray-50 transition {{ $loop->first ? 'bg-blue-50/40' : '' }}">
                            <td class="py-3 font-mono text-[11px]">
                                <span class="font-medium text-brand-text">{{ $waktuPrefix }}</span>
                                {{ $waktuTime }}
                            </td>
                            <td class="font-medium">{{ $lokasiName }}</td>
                            <td>
                                <span class="font-medium">{{ str_replace(['ND-001','ND-002'],['Node A','Node B'], $nodeLabel) }}</span>
                            </td>
                            <td class="font-mono">{{ $jedaFormatted }}</td>
                            <td>
                                <span class="status-pill px-2 py-1 text-[10px] font-bold {{ $isKonsensus ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $isKonsensus ? 'Terpenuhi' : 'Tidak terpenuhi' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-pill px-2 py-1 text-[10px] font-bold {{ $keparahanClass }}">
                                    {{ $keparahanLabel }}
                                </span>
                            </td>
                            <td class="text-[10px] text-brand-muted">{{ $responsText }}</td>
                        </tr>
                    @empty
                        <tr data-live-empty>
                            <td colspan="7" class="py-8 text-center text-brand-muted">Belum ada data deteksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $deteksi->links() }}</div>
    </div>

    {{-- ═══ Detail Panel + Lokasi Respons ═══ --}}
    <div class="grid grid-cols-5 gap-4" id="detailSection">
        {{-- Detail Deteksi: 2 cols --}}
        <div class="col-span-2 bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-brand-text">Detail Deteksi</h3>
                @if($deteksiDetail)
                    @php
                        $badgeClass = match($deteksiDetail['jenis_label']) {
                            'Gempa' => 'bg-red-100 text-red-800',
                            'Uji coba' => 'bg-blue-100 text-blue-800',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <span id="detailJenisBadge" class="status-pill px-2 py-1 text-[10px] font-bold {{ $badgeClass }}">{{ $deteksiDetail['jenis_label'] }}</span>
                @endif
            </div>
            <p id="detailSubtitle" class="text-[10px] text-brand-muted mb-5">
                @if($deteksiDetail)
                    {{ $deteksiDetail['waktu_full'] }} · {{ $deteksiDetail['lokasi'] }}
                @else
                    Pilih baris deteksi di tabel
                @endif
            </p>

            {{-- Timeline --}}
            <div id="detailTimeline" class="relative pl-5 space-y-5">
                @if($deteksiDetail && isset($deteksiDetail['timeline']))
                    {{-- Vertical line --}}
                    <div class="absolute left-[7px] top-1 bottom-1 w-px bg-gray-200"></div>

                    @foreach($deteksiDetail['timeline'] as $step)
                        @php
                            $dotColor = match($step['color']) {
                                'blue' => 'bg-blue-500',
                                'amber' => 'bg-amber-500',
                                'green' => 'bg-emerald-500',
                                default => 'bg-gray-400',
                            };
                        @endphp
                        <div class="relative">
                            <span class="absolute -left-5 top-0.5 w-3.5 h-3.5 rounded-full {{ $dotColor }} border-2 border-white shadow-sm"></span>
                            <p class="text-xs font-bold text-brand-text">{{ $step['title'] }}</p>
                            <p class="text-[10px] text-brand-muted mt-0.5">{{ $step['detail'] }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-brand-muted">Tidak ada data detail</p>
                @endif
            </div>
        </div>

        {{-- Lokasi dan Respons Warga: 3 cols --}}
        <div class="col-span-3 bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-brand-text">Lokasi dan Respons Warga</h3>
                @if($deteksiDetail)
                    <div class="flex items-center gap-4 text-[10px] font-semibold">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Aman <span id="respAmanCount" class="text-brand-muted">{{ $deteksiDetail['respons_warga']['aman'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            Butuh bantuan <span id="respBantuanCount" class="text-brand-muted">{{ $deteksiDetail['respons_warga']['butuh_bantuan'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                            Belum merespons <span id="respBelumCount" class="text-brand-muted">{{ $deteksiDetail['respons_warga']['belum_merespons'] }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Map placeholder --}}
            <div class="relative bg-brand-bg rounded-xl border border-brand-border overflow-hidden mb-4" style="height: 220px;">
                {{-- Grid illustration --}}
                <div class="absolute inset-0 p-4">
                    {{-- Grid lines --}}
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#D1D5DB" stroke-width="0.5"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)"/>
                        {{-- Diagonal line from Node A to Node B --}}
                        <line x1="10%" y1="10%" x2="90%" y2="90%" stroke="#94A3B8" stroke-width="1" stroke-dasharray="4"/>
                    </svg>
                    {{-- Node A marker --}}
                    <div class="absolute top-3 left-3 w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shadow">A</div>
                    {{-- Node B marker --}}
                    <div class="absolute bottom-3 right-3 w-7 h-7 rounded-lg bg-amber-600 text-white flex items-center justify-center text-[10px] font-bold shadow">B</div>

                    {{-- Respons dots (illustrative positions) --}}
                    @if($deteksiDetail && $deteksiDetail['respons_warga']['total'] > 0)
                        @php
                            $allResponses = collect($deteksiDetail['respons_warga']['list_bantuan'] ?? []);
                        @endphp
                        @for($dotIdx = 0; $dotIdx < min($deteksiDetail['respons_warga']['aman'], 8); $dotIdx++)
                            <div class="absolute w-3 h-3 rounded-full bg-emerald-500 shadow-sm" style="top: {{ rand(20, 80) }}%; left: {{ rand(15, 85) }}%;"></div>
                        @endfor
                        @for($dotIdx = 0; $dotIdx < min($deteksiDetail['respons_warga']['butuh_bantuan'], 4); $dotIdx++)
                            <div class="absolute w-4 h-4 rounded-full bg-red-500 shadow-sm border border-white" style="top: {{ rand(25, 75) }}%; left: {{ rand(20, 80) }}%;"></div>
                        @endfor
                        @for($dotIdx = 0; $dotIdx < min($deteksiDetail['respons_warga']['belum_merespons'], 6); $dotIdx++)
                            <div class="absolute w-3 h-3 rounded-full bg-gray-400 shadow-sm" style="top: {{ rand(15, 85) }}%; left: {{ rand(10, 90) }}%;"></div>
                        @endfor
                    @endif
                </div>
                <p class="absolute bottom-1 left-0 right-0 text-center text-[8px] text-brand-muted px-2">
                    Peta ilustrasi. Saat dibangun, ganti dengan peta sungguhan (misalnya Leaflet atau Google Maps). Garis diagonal menghubungkan Node A dan Node B.
                </p>
            </div>

            {{-- Warga needing help list --}}
            <div class="space-y-2 max-h-[120px] overflow-y-auto" id="respBantuanList">
                @if($deteksiDetail && count($deteksiDetail['respons_warga']['list_bantuan']) > 0)
                    @foreach($deteksiDetail['respons_warga']['list_bantuan'] as $w)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-brand-border/50">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
                                <div>
                                    <p class="text-xs font-semibold text-brand-text">{{ $w['nama'] }}</p>
                                    <p class="text-[10px] text-brand-muted">Lapor {{ $w['waktu'] ?? '-' }} · akurasi ±{{ $w['akurasi_meter'] ?? '?' }} m</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="text-[10px] font-semibold text-brand-info hover:underline">Buka di peta</button>
                                <button class="px-3 py-1 rounded-lg border border-brand-border text-[10px] font-semibold text-brand-text hover:bg-gray-100 transition">Hubungi</button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center py-4 text-xs text-brand-muted">
                        {{ $deteksiDetail ? 'Tidak ada warga yang membutuhkan bantuan' : 'Pilih baris deteksi untuk melihat detail' }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
(() => {
    const live = window.liveTable;
    const activeFilter = @json($filter);
    const baseDataUrl = @json(route('deteksi.data'));
    const currentPage = @json($deteksi->currentPage());
    let selectedDeteksiId = @json($deteksiDetail['id'] ?? null);

    const body = document.getElementById('detectionRows');

    // ═══ Row click for detail ═══
    body.addEventListener('click', e => {
        const row = e.target.closest('tr[data-deteksi-id]');
        if (!row) return;
        const id = parseInt(row.dataset.deteksiId);
        if (id === selectedDeteksiId) return;
        selectedDeteksiId = id;

        // Highlight selected row
        body.querySelectorAll('tr').forEach(r => r.classList.remove('bg-blue-50/40'));
        row.classList.add('bg-blue-50/40');

        // Fetch detail
        fetchDetail(id);
    });

    async function fetchDetail(deteksiId) {
        try {
            const url = new URL(baseDataUrl, window.location.origin);
            url.searchParams.set('page', currentPage);
            if (activeFilter) url.searchParams.set('filter', activeFilter);
            url.searchParams.set('detail_id', deteksiId);

            const resp = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const result = await resp.json();
            if (result.detail) updateDetailPanel(result.detail);
        } catch (err) {
            console.error('Detail fetch error:', err);
        }
    }

    // ═══ Update Detail Panel ═══
    function updateDetailPanel(detail) {
        if (!detail) return;

        // Subtitle
        const subtitle = document.getElementById('detailSubtitle');
        if (subtitle) live.setText(subtitle, `${detail.waktu_full} · ${detail.lokasi}`);

        // Jenis badge
        const jenisBadge = document.getElementById('detailJenisBadge');
        if (jenisBadge) {
            live.setText(jenisBadge, detail.jenis_label);
            const cls = detail.jenis_label === 'Gempa' ? 'bg-red-100 text-red-800'
                : detail.jenis_label === 'Uji coba' ? 'bg-blue-100 text-blue-800'
                : 'bg-amber-100 text-amber-800';
            jenisBadge.className = `status-pill px-2 py-1 text-[10px] font-bold ${cls}`;
        }

        // Timeline
        const timeline = document.getElementById('detailTimeline');
        if (timeline && detail.timeline) {
            const dotColorMap = { blue: 'bg-blue-500', amber: 'bg-amber-500', green: 'bg-emerald-500', gray: 'bg-gray-400' };
            let html = '<div class="absolute left-[7px] top-1 bottom-1 w-px bg-gray-200"></div>';
            detail.timeline.forEach(step => {
                const dotClass = dotColorMap[step.color] || 'bg-gray-400';
                html += `<div class="relative">
                    <span class="absolute -left-5 top-0.5 w-3.5 h-3.5 rounded-full ${dotClass} border-2 border-white shadow-sm"></span>
                    <p class="text-xs font-bold text-brand-text">${escapeHtml(step.title)}</p>
                    <p class="text-[10px] text-brand-muted mt-0.5">${escapeHtml(step.detail)}</p>
                </div>`;
            });
            timeline.innerHTML = html;
        }

        // Respons counts
        const rw = detail.respons_warga;
        if (rw) {
            live.setText(document.getElementById('respAmanCount'), rw.aman);
            live.setText(document.getElementById('respBantuanCount'), rw.butuh_bantuan);
            live.setText(document.getElementById('respBelumCount'), rw.belum_merespons);

            // List bantuan
            const listEl = document.getElementById('respBantuanList');
            if (listEl) {
                if (rw.list_bantuan && rw.list_bantuan.length > 0) {
                    let html = '';
                    rw.list_bantuan.forEach(w => {
                        html += `<div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-brand-border/50">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
                                <div>
                                    <p class="text-xs font-semibold text-brand-text">${escapeHtml(w.nama)}</p>
                                    <p class="text-[10px] text-brand-muted">Lapor ${w.waktu || '-'} · akurasi ±${w.akurasi_meter || '?'} m</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="text-[10px] font-semibold text-brand-info hover:underline">Buka di peta</button>
                                <button class="px-3 py-1 rounded-lg border border-brand-border text-[10px] font-semibold text-brand-text hover:bg-gray-100 transition">Hubungi</button>
                            </div>
                        </div>`;
                    });
                    listEl.innerHTML = html;
                } else {
                    listEl.innerHTML = '<p class="text-center py-4 text-xs text-brand-muted">Tidak ada warga yang membutuhkan bantuan</p>';
                }
            }
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    // ═══ Table helpers ═══
    function formatWaktu(isoStr) {
        if (!isoStr) return '-';
        const d = new Date(isoStr);
        if (isNaN(d.getTime())) return '-';
        const now = new Date();
        const isToday = d.toDateString() === now.toDateString();
        const yesterday = new Date(now); yesterday.setDate(yesterday.getDate() - 1);
        const isYesterday = d.toDateString() === yesterday.toDateString();
        const prefix = isToday ? 'Hari ini' : (isYesterday ? 'Kemarin' : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
        const time = d.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        return `${prefix} ${time}`;
    }

    const makeRow = () => {
        const row = document.createElement('tr');
        row.className = 'cursor-pointer hover:bg-gray-50 transition';
        for (let c = 0; c < 7; c++) {
            const td = document.createElement('td');
            if (c === 0) td.className = 'py-3 font-mono text-[11px]';
            if (c === 1) td.className = 'font-medium';
            if (c === 3) td.className = 'font-mono';
            if (c === 4 || c === 5) {
                const badge = document.createElement('span');
                badge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                td.appendChild(badge);
            }
            if (c === 6) td.className = 'text-[10px] text-brand-muted';
            row.appendChild(td);
        }
        return row;
    };

    const updateRow = (row, item) => {
        row.dataset.deteksiId = item.id;

        const cells = row.cells;
        live.setText(cells[0], formatWaktu(item.detected_at));
        live.setText(cells[1], item.lokasi);

        const nodeDisplay = (item.node_label || '').replace(/ND-001/g, 'Node A').replace(/ND-002/g, 'Node B');
        cells[2].innerHTML = `<span class="font-medium">${escapeHtml(nodeDisplay)}</span>`;

        live.setText(cells[3], item.jeda_formatted || '–');

        // Konsensus
        live.setBadge(cells[4].firstElementChild, item.konsensus_label,
            item.konsensus_key === 'terpenuhi' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700');

        // Keparahan
        const kepColors = { kuat: 'bg-red-100 text-red-800', sedang: 'bg-amber-100 text-amber-800', ringan: 'bg-gray-100 text-gray-700' };
        const kepCls = kepColors[item.keparahan_key] || (item.jenis === 'gangguan_lokal' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
        live.setBadge(cells[5].firstElementChild, item.keparahan_label || (item.jenis === 'gangguan_lokal' ? 'Gangguan lokal' : '-'), kepCls);

        // Respons warga
        const rw = item.respons_warga;
        live.setText(cells[6], rw ? rw.text : (item.jenis === 'gangguan_lokal' ? 'Tidak ada notifikasi' : '-'));

        // Highlight selected
        if (parseInt(item.id) === selectedDeteksiId) {
            row.classList.add('bg-blue-50/40');
        } else {
            row.classList.remove('bg-blue-50/40');
        }
    };

    // ═══ Live Polling ═══
    const dataUrl = (() => {
        const url = new URL(baseDataUrl, window.location.origin);
        if (activeFilter) url.searchParams.set('filter', activeFilter);
        if (selectedDeteksiId) url.searchParams.set('detail_id', selectedDeteksiId);
        return url.toString();
    })();

    live.poll(dataUrl, result => {
        live.syncRows(body, result.items || [], makeRow, updateRow, 'Belum ada data deteksi', 7);
        if (result.detail) updateDetailPanel(result.detail);
    }, currentPage);

})();
});
</script>
@endsection
