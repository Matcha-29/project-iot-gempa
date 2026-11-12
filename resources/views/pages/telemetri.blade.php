@extends('layouts.app', ['title' => 'Telemetri - Sistem Deteksi Gempa IoT'])

@section('main-content')
    @include('components.navbar', ['title' => 'Telemetri', 'subtitle' => '50 data level getaran terbaru'])

    <main class="p-8 space-y-6">
        <div class="grid grid-cols-2 gap-5">
            <div class="bg-white p-5 rounded-2xl border">
                <span class="text-xs font-bold text-brand-muted">GETARAN TERAKHIR</span>
                <p id="latestVibration" class="text-3xl font-bold mt-1">
                    {{ $terakhir && $terakhir->nilai_getaran_g !== null ? number_format($terakhir->nilai_getaran_g, 3, ',', '.') . ' g' : '-' }}
                </p>
            </div>
            <div class="bg-white p-5 rounded-2xl border">
                <span class="text-xs font-bold text-brand-muted">KATEGORI TERAKHIR</span>
                <div class="mt-2">
                    @php
                        $latestCategory = strtolower($terakhir->kategori_level ?? '');
                        $latestCategoryClass = $latestCategory === 'normal'
                            ? 'bg-emerald-100 text-emerald-800'
                            : ($latestCategory === 'waspada'
                                ? 'bg-amber-100 text-amber-800'
                                : 'bg-red-100 text-red-800');
                    @endphp
                    <span id="latestCategory" data-category="{{ $latestCategory }}" class="status-pill px-2 py-1 text-[10px] font-bold {{ $latestCategoryClass }}">
                        {{ $terakhir ? ucfirst($terakhir->kategori_level) : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b text-brand-muted">
                            <th class="py-3">Waktu</th>
                            <th>Perangkat</th>
                            <th>X</th>
                            <th>Y</th>
                            <th>Z</th>
                            <th>Getaran</th>
                            <th>Magnitudo</th>
                            <th>SW420</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody id="telemetryRows" class="divide-y">
                        @forelse($telemetri as $item)
                            @php
                                $category = strtolower($item->kategori_level ?? '');
                                $categoryClass = $category === 'normal'
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : ($category === 'waspada'
                                        ? 'bg-amber-100 text-amber-800'
                                        : 'bg-red-100 text-red-800');
                            @endphp
                            <tr data-telemetry-id="{{ $item->getKey() }}">
                                <td class="py-3">{{ $item->dibuat_pada?->timezone(config('app.display_timezone'))->format('d M Y H:i:s.v') ?? '-' }}</td>
                                <td>{{ optional($item->perangkat)->kode_perangkat ?? '-' }}</td>
                                <td>{{ $item->akselerasi_x ?? '-' }}</td>
                                <td>{{ $item->akselerasi_y ?? '-' }}</td>
                                <td>{{ $item->akselerasi_z ?? '-' }}</td>
                                <td>{{ $item->nilai_getaran_g !== null ? number_format($item->nilai_getaran_g, 3, ',', '.') . ' g' : '-' }}</td>
                                <td>{{ $item->nilai_magnitudo !== null ? number_format($item->nilai_magnitudo, 3, ',', '.') : '-' }}</td>
                                <td>
                                    <span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->status_sw420 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $item->status_sw420 ? 'Aktif' : 'Tidak aktif' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill px-2 py-1 text-[10px] font-bold {{ $categoryClass }}">
                                        {{ $item->kategori_level ? ucfirst($item->kategori_level) : '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="9" class="py-8 text-center text-brand-muted">Belum ada data telemetri</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $telemetri->links() }}</div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            (() => {
            const dataUrl = @json(route('telemetri.data'));
            const currentPage = @json($telemetri->currentPage());
            const rowsElement = document.getElementById('telemetryRows');
            const latestVibrationElement = document.getElementById('latestVibration');
            const latestCategoryElement = document.getElementById('latestCategory');
            const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: @json(config('app.display_timezone', 'Asia/Jakarta'))
            });

            function formatNumber(value, digits = 3) {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }

                return Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits
                });
            }

            function formatTimestamp(value) {
                if (!value) {
                    return '-';
                }

                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? '-' : dateFormatter.format(date);
            }

            function categoryStyle(category) {
                const normalized = (category || '').toLowerCase();
                if (normalized === 'normal') {
                    return { label: 'Normal', classes: 'bg-emerald-100 text-emerald-800' };
                }
                if (normalized === 'waspada') {
                    return { label: 'Waspada', classes: 'bg-amber-100 text-amber-800' };
                }
                if (normalized) {
                    return {
                        label: normalized.charAt(0).toUpperCase() + normalized.slice(1),
                        classes: 'bg-red-100 text-red-800'
                    };
                }

                return { label: '-', classes: 'bg-red-100 text-red-800' };
            }

            function setText(element, value) {
                if (element && element.textContent !== value) {
                    element.textContent = value;
                }
            }

            function updateBadge(element, label, classes, category = null) {
                if (!element) {
                    return;
                }

                setText(element, label);
                if (element.className !== `status-pill px-2 py-1 text-[10px] font-bold ${classes}`) {
                    element.className = `status-pill px-2 py-1 text-[10px] font-bold ${classes}`;
                }
                if (category !== null) {
                    element.dataset.category = category;
                }
            }

            function createCell(row, className = '') {
                const cell = document.createElement('td');
                if (className) {
                    cell.className = className;
                }
                row.appendChild(cell);
                return cell;
            }

            function createTelemetryRow(id) {
                const row = document.createElement('tr');
                row.dataset.telemetryId = String(id);

                createCell(row, 'py-3');
                for (let index = 0; index < 6; index++) {
                    createCell(row);
                }

                const sw420Cell = createCell(row);
                const sw420Badge = document.createElement('span');
                sw420Badge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                sw420Cell.appendChild(sw420Badge);

                const categoryCell = createCell(row);
                const categoryBadge = document.createElement('span');
                categoryBadge.className = 'status-pill px-2 py-1 text-[10px] font-bold';
                categoryCell.appendChild(categoryBadge);

                return row;
            }

            function updateTelemetryRow(row, item) {
                const cells = row.cells;
                const values = [
                    formatTimestamp(item.created_at),
                    item.device_code || '-',
                    item.acceleration_x ?? '-',
                    item.acceleration_y ?? '-',
                    item.acceleration_z ?? '-',
                    item.vibration_g === null ? '-' : `${formatNumber(item.vibration_g)} g`,
                    formatNumber(item.magnitude)
                ];

                values.forEach((value, index) => setText(cells[index], String(value)));
                updateBadge(
                    cells[7].firstElementChild,
                    item.sw420_active ? 'Aktif' : 'Tidak aktif',
                    item.sw420_active ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700'
                );

                const category = categoryStyle(item.category);
                updateBadge(cells[8].firstElementChild, category.label, category.classes);
            }

            function renderRows(items) {
                if (!items.length) {
                    const emptyRow = rowsElement.querySelector('[data-empty-row]');
                    if (emptyRow && rowsElement.children.length === 1) {
                        return;
                    }

                    const row = document.createElement('tr');
                    row.dataset.emptyRow = '';
                    const cell = createCell(row, 'py-8 text-center text-brand-muted');
                    cell.colSpan = 9;
                    cell.textContent = 'Belum ada data telemetri';
                    rowsElement.replaceChildren(row);
                    return;
                }

                const existingRows = new Map(
                    Array.from(rowsElement.querySelectorAll('[data-telemetry-id]'))
                        .map(row => [row.dataset.telemetryId, row])
                );
                rowsElement.querySelector('[data-empty-row]')?.remove();

                items.forEach((item, index) => {
                    const id = String(item.id);
                    const row = existingRows.get(id) || createTelemetryRow(id);
                    existingRows.delete(id);
                    updateTelemetryRow(row, item);

                    const currentRow = rowsElement.children[index];
                    if (currentRow !== row) {
                        rowsElement.insertBefore(row, currentRow || null);
                    }
                });

                existingRows.forEach(row => row.remove());
            }

            function updateSummary(latest) {
                if (!latest) {
                    setText(latestVibrationElement, '-');
                    updateBadge(latestCategoryElement, '-', 'bg-red-100 text-red-800', '');
                    return;
                }

                setText(
                    latestVibrationElement,
                    latest.vibration_g === null ? '-' : `${formatNumber(latest.vibration_g)} g`
                );
                const category = categoryStyle(latest.category);
                updateBadge(latestCategoryElement, category.label, category.classes, (latest.category || '').toLowerCase());
            }

            window.liveTable.poll(dataUrl, result => {
                    updateSummary(result.latest);
                    renderRows(result.items || []);
                }, currentPage);
        })();
        });
    </script>
@endsection
