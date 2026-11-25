@extends('layouts.app', ['title' => 'Telemetri - Sistem Deteksi Gempa IoT'])

@section('main-content')
    @include('components.navbar', ['title' => 'Telemetri', 'subtitle' => 'Data level getaran berdampingan per slot 5 detik'])

    <main class="p-8 space-y-6">
        {{-- ═══ 3 Summary Cards ═══ --}}
        <div class="grid grid-cols-3 gap-5">
            @foreach($nodeSummaries as $i => $ns)
                <div class="bg-white p-5 rounded-2xl border">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">NODE {{ $i + 1 }} · {{ $ns['code'] }}</span>
                        <span id="nodeStatus{{ $i }}" class="inline-flex items-center gap-1.5 text-[10px] font-bold {{ $ns['active'] ? 'text-emerald-700' : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $ns['active'] ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300' }}"></span>
                            {{ $ns['active'] ? 'Aktif' : 'Tidak aktif' }}
                        </span>
                    </div>
                    <p id="nodeVibration{{ $i }}" class="text-3xl font-bold mt-2">
                        {{ $ns['vibration_g'] !== null ? number_format($ns['vibration_g'], 3, ',', '.') . ' g' : '-' }}
                    </p>
                    <p id="nodeMagnitude{{ $i }}" class="text-xs text-brand-muted mt-0.5">
                        {{ $ns['magnitude'] !== null ? number_format($ns['magnitude'], 2, ',', '.') . ' M' : '- M' }}
                    </p>
                </div>
            @endforeach

            {{-- Kategori Card --}}
            <div class="bg-white p-5 rounded-2xl border">
                <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wide">KATEGORI TERAKHIR</span>
                <div class="mt-2">
                    <span id="latestCategory" data-category="{{ $consensusStatus['badge_key'] }}" class="status-pill px-2 py-1 text-[10px] font-bold {{ $consensusStatus['badge_class'] }}">
                        {{ $consensusStatus['badge_label'] }}
                    </span>
                </div>
                <p id="activeNodeCount" class="text-xs text-brand-muted mt-2">{{ $consensusStatus['subtitle'] }}</p>
            </div>
        </div>

        {{-- ═══ Chart ═══ --}}
        <div class="bg-white p-5 rounded-2xl border shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h3 class="text-xs font-bold text-brand-muted uppercase tracking-wide">Getaran Real-Time (25 Data Terakhir)</h3>
                <div class="flex items-center gap-6 text-xs text-brand-text font-medium">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full border-2 border-blue-600 bg-blue-100"></span>
                        <span>Node 1 · ND-001</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full border-2 border-amber-700 bg-amber-100"></span>
                        <span>Node 2 · ND-002</span>
                    </div>
                </div>
            </div>
            <div style="height: 180px;">
                <canvas id="vibrationChart"></canvas>
            </div>
        </div>

        {{-- ═══ Side-by-Side Table ═══ --}}
        <div class="bg-white p-6 rounded-2xl border shadow-sm">
            <h3 class="text-xs font-bold text-brand-muted uppercase tracking-wide mb-4">Log Getaran Berdampingan · Slot 5 Detik</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b text-brand-muted">
                            <th class="py-3" rowspan="2">Waktu</th>
                            <th class="text-center border-l border-blue-100 bg-blue-50/30 px-2" colspan="3">Node 1 (ND-001)</th>
                            <th class="text-center border-l border-amber-100 bg-amber-50/30 px-2" colspan="3">Node 2 (ND-002)</th>
                            <th class="text-center border-l px-2" rowspan="2">Kategori</th>
                        </tr>
                        <tr class="border-b text-brand-muted text-[10px]">
                            <th class="py-2 border-l border-blue-100 bg-blue-50/30 px-2">Getaran</th>
                            <th class="bg-blue-50/30 px-2">Magnitudo</th>
                            <th class="bg-blue-50/30 px-2">SW420</th>
                            <th class="border-l border-amber-100 bg-amber-50/30 px-2">Getaran</th>
                            <th class="bg-amber-50/30 px-2">Magnitudo</th>
                            <th class="bg-amber-50/30 px-2">SW420</th>
                        </tr>
                    </thead>
                    <tbody id="telemetryRows" class="divide-y">
                        @forelse($telemetri as $idx => $row)
                            @php
                                $cat = $row->kategori_gabungan ?? 'Normal';
                                $catKey = strtolower($cat);
                                $catClass = match($catKey) {
                                    'bahaya' => 'bg-red-100 text-red-800',
                                    'waspada' => 'bg-amber-100 text-amber-800',
                                    'gangguan' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-emerald-100 text-emerald-800',
                                };
                            @endphp
                            <tr data-row-idx="{{ $idx }}">
                                <td class="py-2.5">{{ $row->waktu_display }}</td>
                                {{-- Node 1 --}}
                                <td class="border-l border-blue-100 bg-blue-50/20 px-2">{{ $row->getaran_n1 !== null ? number_format($row->getaran_n1, 3, ',', '.') . ' g' : '-' }}</td>
                                <td class="bg-blue-50/20 px-2">{{ $row->magnitudo_n1 !== null ? number_format($row->magnitudo_n1, 2, ',', '.') . ' M' : '-' }}</td>
                                <td class="bg-blue-50/20 px-2">
                                    <span class="status-pill px-2 py-0.5 text-[9px] font-bold {{ $row->sw420_n1 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $row->sw420_n1 ? 'Aktif' : 'Mati' }}
                                    </span>
                                </td>
                                {{-- Node 2 --}}
                                <td class="border-l border-amber-100 bg-amber-50/20 px-2">{{ $row->getaran_n2 !== null ? number_format($row->getaran_n2, 3, ',', '.') . ' g' : '-' }}</td>
                                <td class="bg-amber-50/20 px-2">{{ $row->magnitudo_n2 !== null ? number_format($row->magnitudo_n2, 2, ',', '.') . ' M' : '-' }}</td>
                                <td class="bg-amber-50/20 px-2">
                                    <span class="status-pill px-2 py-0.5 text-[9px] font-bold {{ $row->sw420_n2 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $row->sw420_n2 ? 'Aktif' : 'Mati' }}
                                    </span>
                                </td>
                                {{-- Kategori --}}
                                <td class="border-l px-2 text-center">
                                    <span class="status-pill px-2 py-0.5 text-[9px] font-bold {{ $catClass }}">{{ $cat }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="8" class="py-8 text-center text-brand-muted">Belum ada data telemetri</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi Pagination --}}
            <div class="mt-4">
                {{ $telemetri->links() }}
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            (() => {
            const dataUrl = @json(route('telemetri.data'));
            const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit',
                timeZone: @json(config('app.display_timezone', 'Asia/Jakarta'))
            });

            // Cek parameter page di URL
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = parseInt(urlParams.get('page') || '1', 10);

            // ═══ Chart Setup ═══
            const chartCtx = document.getElementById('vibrationChart').getContext('2d');
            const initialChartData = @json($chartData);
            const nodeColors = ['#2F5BEA', '#C25E0B'];
            const nodeBgColors = ['rgba(47,91,234,0.08)', 'rgba(194,94,11,0.08)'];
            const nodeCodes = ['ND-001', 'ND-002'];

            const chartDatasets = nodeCodes.map((code, i) => ({
                label: `Node ${i + 1} · ${code}`,
                data: (initialChartData[code] || []).map(d => d.vibration_g),
                borderColor: nodeColors[i],
                backgroundColor: nodeBgColors[i],
                borderWidth: 2, pointRadius: 2, pointHoverRadius: 4, tension: 0.3, fill: true,
            }));

            const firstData = initialChartData[nodeCodes[0]] || [];
            const vibrationChart = new Chart(chartCtx, {
                type: 'line',
                data: { labels: firstData.map(d => d.time), datasets: chartDatasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y !== null ? ctx.parsed.y.toFixed(3) + ' g' : '-'}` } }
                    },
                    scales: {
                        x: { display: true, grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0 } },
                        y: { display: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 9 }, callback: v => v.toFixed(2) + ' g' }, beginAtZero: true }
                    }
                }
            });

            // ═══ Helpers ═══
            function fmt(v, d = 3) {
                if (v === null || v === undefined) return '-';
                return Number(v).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
            }
            function setText(el, val) { if (el && el.textContent !== val) el.textContent = val; }
            function updateBadge(el, label, classes, key) {
                if (!el) return;
                setText(el, label);
                const cls = `status-pill px-2 py-1 text-[10px] font-bold ${classes}`;
                if (el.className !== cls) el.className = cls;
                if (key !== undefined) el.dataset.category = key;
            }

            function catStyle(cat) {
                const k = (cat || 'normal').toLowerCase();
                if (k === 'bahaya') return { cls: 'bg-red-100 text-red-800' };
                if (k === 'waspada' || k === 'gangguan') return { cls: 'bg-amber-100 text-amber-800' };
                return { cls: 'bg-emerald-100 text-emerald-800' };
            }

            function sw420Badge(active) {
                return active
                    ? '<span class="status-pill px-2 py-0.5 text-[9px] font-bold bg-red-100 text-red-800">Aktif</span>'
                    : '<span class="status-pill px-2 py-0.5 text-[9px] font-bold bg-gray-100 text-gray-600">Mati</span>';
            }

            // ═══ Update Cards ═══
            function updateNodeCards(summaries) {
                if (!summaries) return;
                summaries.forEach((ns, i) => {
                    const vibEl = document.getElementById(`nodeVibration${i}`);
                    const magEl = document.getElementById(`nodeMagnitude${i}`);
                    const statusEl = document.getElementById(`nodeStatus${i}`);
                    if (vibEl) setText(vibEl, ns.vibration_g !== null ? `${fmt(ns.vibration_g)} g` : '-');
                    if (magEl) setText(magEl, ns.magnitude !== null ? `${fmt(ns.magnitude, 2)} M` : '- M');
                    if (statusEl) {
                        const dot = statusEl.querySelector('span');
                        const dotCls = `w-1.5 h-1.5 rounded-full ${ns.active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'}`;
                        if (dot && dot.className !== dotCls) dot.className = dotCls;
                        statusEl.className = `inline-flex items-center gap-1.5 text-[10px] font-bold ${ns.active ? 'text-emerald-700' : 'text-gray-400'}`;
                        const textNode = statusEl.lastChild;
                        if (textNode && textNode.nodeType === 3) {
                            const desired = ns.active ? ' Aktif' : ' Tidak aktif';
                            if (textNode.textContent.trim() !== desired.trim()) textNode.textContent = desired;
                        }
                    }
                });
            }

            function updateCategoryCard(consensus) {
                const el = document.getElementById('latestCategory');
                const subtitleEl = document.getElementById('activeNodeCount');
                if (!el || !consensus) return;
                updateBadge(el, consensus.badge_label, consensus.badge_class, consensus.badge_key);
                if (subtitleEl) setText(subtitleEl, consensus.subtitle);
            }

            // ═══ Update Chart ═══
            function updateChart(chartDataNew) {
                if (!chartDataNew) return;
                nodeCodes.forEach((code, i) => {
                    const nd = chartDataNew[code] || [];
                    vibrationChart.data.datasets[i].data = nd.map(d => d.vibration_g);
                    if (i === 0) vibrationChart.data.labels = nd.map(d => d.time);
                });
                vibrationChart.update('none');
            }

            // ═══ Update Table ═══
            function renderRows(items) {
                const tbody = document.getElementById('telemetryRows');
                if (!tbody) return;

                if (!items || !items.length) {
                    if (!tbody.querySelector('[data-empty-row]')) {
                        tbody.innerHTML = '<tr data-empty-row><td colspan="8" class="py-8 text-center text-brand-muted">Belum ada data telemetri</td></tr>';
                    }
                    return;
                }

                let html = '';
                items.forEach((row, idx) => {
                    const waktu = row.waktu_slot ? new Date(row.waktu_slot) : null;
                    const waktuStr = waktu && !isNaN(waktu.getTime()) ? dateFormatter.format(waktu) : '-';
                    const cs = catStyle(row.kategori_gabungan);

                    html += `<tr data-row-idx="${idx}">
                        <td class="py-2.5">${waktuStr}</td>
                        <td class="border-l border-blue-100 bg-blue-50/20 px-2">${row.getaran_n1 !== null ? fmt(row.getaran_n1) + ' g' : '-'}</td>
                        <td class="bg-blue-50/20 px-2">${row.magnitudo_n1 !== null ? fmt(row.magnitudo_n1, 2) + ' M' : '-'}</td>
                        <td class="bg-blue-50/20 px-2">${sw420Badge(row.sw420_n1)}</td>
                        <td class="border-l border-amber-100 bg-amber-50/20 px-2">${row.getaran_n2 !== null ? fmt(row.getaran_n2) + ' g' : '-'}</td>
                        <td class="bg-amber-50/20 px-2">${row.magnitudo_n2 !== null ? fmt(row.magnitudo_n2, 2) + ' M' : '-'}</td>
                        <td class="bg-amber-50/20 px-2">${sw420Badge(row.sw420_n2)}</td>
                        <td class="border-l px-2 text-center"><span class="status-pill px-2 py-0.5 text-[9px] font-bold ${cs.cls}">${row.kategori_gabungan}</span></td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            }

            // ═══ Live Poll ═══
            window.liveTable.poll(dataUrl, result => {
                // Selalu perbarui ringkasan kartu dan grafik secara real-time
                updateNodeCards(result.node_summaries);
                updateCategoryCard(result.consensus_status);
                updateChart(result.chart_data);

                // Perbarui baris tabel HANYA jika berada di halaman 1
                if (currentPage === 1) {
                    renderRows(result.items);
                }
            }, 1);
        })();
        });
    </script>
@endsection
