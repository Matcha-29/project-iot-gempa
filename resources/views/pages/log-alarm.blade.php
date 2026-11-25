@extends('layouts.app', ['title' => 'Log Alarm - Sistem Deteksi Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Log Alarm', 'subtitle' => 'Catatan setiap kali sirine dibunyikan, beserta status dan catatan petugas'])

<main class="p-6 space-y-5">
    {{-- ═══ 3 Summary Cards ═══ --}}
    <div class="grid grid-cols-3 gap-4">
        {{-- Card 1: Alarm Bulan Ini --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">ALARM BULAN INI</span>
            <p id="alarmBulanIni" class="text-3xl font-bold mt-2">{{ $summary['alarm_bulan_ini'] }}</p>
            <p id="alarmBreakdown" class="text-[11px] text-brand-muted mt-1">{{ $summary['gempa_count'] }} gempa, {{ $summary['non_gempa_count'] }} uji coba</p>
        </div>

        {{-- Card 2: Sirine Terakhir --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">SIRINE TERAKHIR</span>
            <p id="sirineTerakhir" class="text-3xl font-bold mt-2 font-mono">{{ $summary['sirine_terakhir_time'] }}</p>
            <p id="sirineSubtext" class="text-[11px] text-brand-muted mt-1">{{ $summary['sirine_terakhir_subtext'] }}</p>
        </div>

        {{-- Card 3: Status Sirine --}}
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">STATUS SIRINE</span>
            @php
                $statusClass = match($summary['status_sirine_key']) {
                    'aktif' => 'text-red-600',
                    'gagal' => 'text-amber-600',
                    default => 'text-brand-aman',
                };
            @endphp
            <p id="statusSirine" class="text-3xl font-bold mt-2 {{ $statusClass }}">{{ $summary['status_sirine'] }}</p>
            <p id="statusSirineSubtext" class="text-[11px] text-brand-muted mt-1">{{ $summary['status_sirine_subtext'] }}</p>
        </div>
    </div>

    {{-- ═══ Riwayat Alarm Table ═══ --}}
    <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-brand-text">Riwayat Alarm</h3>
            <select id="statusFilterSelect" class="text-xs border border-brand-border rounded-lg px-3 py-1.5 bg-white text-brand-text font-medium focus:outline-none focus:ring-1 focus:ring-brand-aman">
                <option value="" {{ $statusFilter === '' ? 'selected' : '' }}>Semua status</option>
                <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="menyala" {{ $statusFilter === 'menyala' ? 'selected' : '' }}>Aktif</option>
                <option value="gagal" {{ $statusFilter === 'gagal' ? 'selected' : '' }}>Gagal</option>
                <option value="dibatalkan" {{ $statusFilter === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b text-brand-muted uppercase text-[10px] font-bold tracking-wide">
                        <th class="py-3">WAKTU ALARM</th>
                        <th>DETEKSI TERKAIT</th>
                        <th>STATUS</th>
                        <th>DURASI</th>
                        <th>CATATAN PETUGAS</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody id="alarmRows" class="divide-y">
                    @forelse($logAlarm as $item)
                        @php
                            $deteksi = $item->deteksi;
                            $detJenis = optional($deteksi)->jenis;
                            $detKep = optional($deteksi)->tingkat_keparahan;
                            $lokasiName = optional(optional(optional($deteksi)->perangkatA)->lokasi)->nama_lokasi ?? optional($lokasi)->nama_lokasi ?? '-';

                            $detLabel = '-';
                            if ($deteksi) {
                                $jenisLabel = match($detJenis) {
                                    'gempa' => ($detKep ? ucfirst($detKep) : 'Gempa'),
                                    'gangguan_lokal' => 'Gangguan lokal',
                                    'uji_coba' => 'Uji coba',
                                    default => ucfirst($detJenis ?? ''),
                                };
                                $detLabel = "{$jenisLabel} · {$lokasiName}";
                            }

                            $statusClass = match($item->status_alarm) {
                                'menyala' => 'bg-red-100 text-red-800',
                                'selesai' => 'bg-emerald-100 text-emerald-800',
                                'gagal' => 'bg-red-100 text-red-700',
                                'dibatalkan' => 'bg-gray-100 text-gray-700',
                                default => 'bg-amber-100 text-amber-800',
                            };
                            $statusLabel = match($item->status_alarm) {
                                'menyala' => 'Aktif',
                                'selesai' => 'Selesai',
                                'gagal' => 'Gagal',
                                'dibatalkan' => 'Dibatalkan',
                                default => ucfirst(str_replace('_',' ',$item->status_alarm ?? '')),
                            };

                            $waktuCarbon = $item->waktu_alarm?->timezone(config('app.display_timezone'));
                            $isToday = $waktuCarbon && $waktuCarbon->isToday();
                            $isYesterday = $waktuCarbon && $waktuCarbon->isYesterday();
                            $waktuPrefix = $isToday ? 'Hari ini' : ($isYesterday ? 'Kemarin' : ($waktuCarbon ? $waktuCarbon->format('d M') : ''));
                            $waktuTime = $waktuCarbon ? $waktuCarbon->format('H:i:s') : '-';
                        @endphp
                        <tr data-live-id="{{ $item->getKey() }}" data-alarm-id="{{ $item->getKey() }}" class="cursor-pointer hover:bg-gray-50 transition {{ $loop->first ? 'bg-blue-50/40' : '' }}">
                            <td class="py-3 font-mono text-[11px]">
                                <span class="font-medium text-brand-text">{{ $waktuPrefix }}</span>
                                {{ $waktuTime }}
                            </td>
                            <td class="font-medium text-xs">{{ $detLabel }}</td>
                            <td>
                                <span class="status-pill px-2 py-1 text-[10px] font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="font-mono">{{ $item->durasi_detik !== null ? $item->durasi_detik . ' dtk' : '-' }}</td>
                            <td class="text-[11px] text-brand-muted max-w-[200px] truncate">{{ $item->catatan ?? '—' }}</td>
                            <td class="text-right">
                                <button class="text-xs font-semibold text-brand-info hover:underline btn-ubah" data-alarm-id="{{ $item->getKey() }}" data-alarm-waktu="{{ $waktuPrefix }} {{ $waktuTime }}" data-alarm-catatan="{{ $item->catatan ?? '' }}">Ubah</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-live-empty>
                            <td colspan="6" class="py-8 text-center text-brand-muted">Belum ada log alarm</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logAlarm->links() }}</div>
    </div>

    {{-- ═══ Catatan Panel ═══ --}}
    <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm" id="catatanPanel" style="display: none;">
        <h3 class="text-sm font-bold text-brand-text mb-3" id="catatanTitle">Catatan untuk alarm</h3>
        <input type="hidden" id="catatanAlarmId" value="">
        <textarea id="catatanTextarea" rows="3" class="w-full border border-brand-border rounded-xl px-4 py-3 text-xs text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-aman/30 resize-none" placeholder="Tulis catatan petugas..."></textarea>
        <div class="flex justify-end mt-3">
            <button id="simpanCatatanBtn" class="px-5 py-2 rounded-xl bg-brand-aman text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm">
                Simpan Catatan
            </button>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
(() => {
    const live = window.liveTable;
    const baseDataUrl = @json(route('log-alarm.data'));
    const currentPage = @json($logAlarm->currentPage());
    let activeStatus = @json($statusFilter);
    let selectedAlarmId = null;

    const body = document.getElementById('alarmRows');

    // ═══ Status filter dropdown ═══
    document.getElementById('statusFilterSelect').addEventListener('change', function() {
        activeStatus = this.value;
        window.location.href = activeStatus ? `/log-alarm?status=${activeStatus}` : '/log-alarm';
    });

    // ═══ Ubah button click → show catatan panel ═══
    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-ubah');
        if (!btn) return;
        e.stopPropagation();

        const alarmId = btn.dataset.alarmId;
        const waktu = btn.dataset.alarmWaktu;
        const catatan = btn.dataset.alarmCatatan;

        selectedAlarmId = alarmId;
        document.getElementById('catatanAlarmId').value = alarmId;
        document.getElementById('catatanTitle').textContent = `Catatan untuk alarm ${waktu}`;
        document.getElementById('catatanTextarea').value = catatan;
        document.getElementById('catatanPanel').style.display = 'block';

        // Highlight selected row
        body.querySelectorAll('tr').forEach(r => r.classList.remove('bg-blue-50/40'));
        btn.closest('tr')?.classList.add('bg-blue-50/40');

        // Scroll to catatan
        document.getElementById('catatanPanel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    // ═══ Simpan Catatan ═══
    document.getElementById('simpanCatatanBtn').addEventListener('click', async () => {
        const alarmId = document.getElementById('catatanAlarmId').value;
        const catatan = document.getElementById('catatanTextarea').value;
        if (!alarmId) return;

        const btn = document.getElementById('simpanCatatanBtn');
        const origText = btn.textContent;
        btn.textContent = 'Menyimpan...';
        btn.disabled = true;

        try {
            const resp = await fetch(`/log-alarm/${alarmId}/catatan`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ catatan }),
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            btn.textContent = '✓ Tersimpan';
            setTimeout(() => { btn.textContent = origText; btn.disabled = false; }, 2000);

            // Update the row's catatan cell & data attribute
            const row = body.querySelector(`tr[data-alarm-id="${alarmId}"]`);
            if (row) {
                live.setText(row.cells[4], catatan || '—');
                const ubahBtn = row.querySelector('.btn-ubah');
                if (ubahBtn) ubahBtn.dataset.alarmCatatan = catatan;
            }
        } catch (err) {
            console.error('Simpan catatan error:', err);
            btn.textContent = 'Gagal menyimpan';
            setTimeout(() => { btn.textContent = origText; btn.disabled = false; }, 2000);
        }
    });

    // ═══ Helpers ═══
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

    function statusStyle(status) {
        const s = (status || '').toLowerCase();
        const map = {
            menyala: { label: 'Aktif', cls: 'bg-red-100 text-red-800' },
            selesai: { label: 'Selesai', cls: 'bg-emerald-100 text-emerald-800' },
            gagal: { label: 'Gagal', cls: 'bg-red-100 text-red-700' },
            dibatalkan: { label: 'Dibatalkan', cls: 'bg-gray-100 text-gray-700' },
        };
        return map[s] || { label: s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) || '-', cls: 'bg-amber-100 text-amber-800' };
    }

    // ═══ Update summary cards ═══
    function updateSummary(summary) {
        if (!summary) return;
        live.setText(document.getElementById('alarmBulanIni'), summary.alarm_bulan_ini);
        live.setText(document.getElementById('alarmBreakdown'), `${summary.gempa_count} gempa, ${summary.non_gempa_count} uji coba`);
        live.setText(document.getElementById('sirineTerakhir'), summary.sirine_terakhir_time);
        live.setText(document.getElementById('sirineSubtext'), summary.sirine_terakhir_subtext);
        live.setText(document.getElementById('statusSirine'), summary.status_sirine);
        live.setText(document.getElementById('statusSirineSubtext'), summary.status_sirine_subtext);

        const statusEl = document.getElementById('statusSirine');
        if (statusEl) {
            const colorMap = { aktif: 'text-red-600', gagal: 'text-amber-600', siap: 'text-brand-aman' };
            statusEl.className = `text-3xl font-bold mt-2 ${colorMap[summary.status_sirine_key] || 'text-brand-aman'}`;
        }
    }

    // ═══ Table row creation ═══
    const makeRow = () => {
        const row = document.createElement('tr');
        row.className = 'cursor-pointer hover:bg-gray-50 transition';
        for (let c = 0; c < 6; c++) {
            const td = document.createElement('td');
            if (c === 0) td.className = 'py-3 font-mono text-[11px]';
            if (c === 1) td.className = 'font-medium text-xs';
            if (c === 2) {
                const badge = document.createElement('span');
                badge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                td.appendChild(badge);
            }
            if (c === 3) td.className = 'font-mono';
            if (c === 4) td.className = 'text-[11px] text-brand-muted max-w-[200px] truncate';
            if (c === 5) {
                td.className = 'text-right';
                const btn = document.createElement('button');
                btn.className = 'text-xs font-semibold text-brand-info hover:underline btn-ubah';
                btn.textContent = 'Ubah';
                td.appendChild(btn);
            }
            row.appendChild(td);
        }
        return row;
    };

    const updateRow = (row, item) => {
        row.dataset.alarmId = item.id;
        const cells = row.cells;

        live.setText(cells[0], formatWaktu(item.alarm_at));
        live.setText(cells[1], item.detection_label || '-');

        const st = statusStyle(item.status);
        live.setBadge(cells[2].firstElementChild, st.label, st.cls);

        live.setText(cells[3], item.duration_seconds !== null ? `${item.duration_seconds} dtk` : '-');
        live.setText(cells[4], item.notes || '—');

        // Update ubah button data
        const ubahBtn = cells[5].querySelector('.btn-ubah');
        if (ubahBtn) {
            ubahBtn.dataset.alarmId = item.id;
            ubahBtn.dataset.alarmWaktu = formatWaktu(item.alarm_at);
            ubahBtn.dataset.alarmCatatan = item.notes || '';
        }
    };

    // ═══ Live Polling ═══
    const dataUrl = (() => {
        const url = new URL(baseDataUrl, window.location.origin);
        if (activeStatus) url.searchParams.set('status', activeStatus);
        return url.toString();
    })();

    live.poll(dataUrl, result => {
        updateSummary(result.summary);
        live.syncRows(body, result.items || [], makeRow, updateRow, 'Belum ada log alarm', 6);
    }, currentPage);

})();
});
</script>
@endsection
