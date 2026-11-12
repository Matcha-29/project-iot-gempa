<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sistem Deteksi Gempa IoT' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .status-pill {
            width: 8rem;
            min-width: 8rem;
            height: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border-radius: 9999px;
            text-align: center;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"IBM Plex Sans"', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            sidebar: '#14201A',
                            bg: '#F3F5F1',
                            text: '#16211B',
                            muted: '#55615A',
                            border: '#E1E6DF',
                            aman: '#17703A',
                            amanBg: '#E3F4EB',
                            waspada: '#B45F06',
                            waspadaBg: '#FFF1D6',
                            bahaya: '#B3261E',
                            bahayaBg: '#FDE7E4',
                            nodeA: '#2F5BEA',
                            nodeB: '#C25E0B',
                            info: '#1F4FB0'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#F3F5F1] text-brand-text font-sans antialiased flex h-screen overflow-hidden">
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Konten Utama -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        @yield('main-content')
    </div>
    <script>
        window.liveTable = (() => {
            const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: @json(config('app.display_timezone', 'Asia/Jakarta'))
            });
            let lastError = null;

            function setText(element, value) {
                const text = value === null || value === undefined || value === '' ? '-' : String(value);
                if (element && element.textContent !== text) {
                    element.textContent = text;
                }
            }

            function formatDate(value) {
                if (!value) {
                    return '-';
                }
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? '-' : dateFormatter.format(date);
            }

            function formatNumber(value, digits = 3) {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }
                return Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits
                });
            }

            function setBadge(element, label, colors) {
                if (!element) {
                    return;
                }
                setText(element, label);
                const baseClass = 'status-pill px-2 py-1 text-[10px] font-bold';
                const className = `${baseClass} ${colors}`;
                if (element.className !== className) {
                    element.className = className;
                }
            }

            function syncRows(tbody, items, createRow, updateRow, emptyMessage, columns) {
                if (!tbody) {
                    return;
                }
                if (!items.length) {
                    if (!tbody.querySelector('[data-live-empty]')) {
                        const row = document.createElement('tr');
                        row.dataset.liveEmpty = '';
                        const cell = document.createElement('td');
                        cell.colSpan = columns;
                        cell.className = 'py-8 text-center text-brand-muted';
                        cell.textContent = emptyMessage;
                        row.appendChild(cell);
                        tbody.replaceChildren(row);
                    }
                    return;
                }

                const currentRows = new Map(
                    Array.from(tbody.querySelectorAll('tr[data-live-id]'))
                        .map(row => [row.dataset.liveId, row])
                );
                tbody.querySelector('[data-live-empty]')?.remove();

                items.forEach((item, index) => {
                    const id = String(item.id);
                    const row = currentRows.get(id) || createRow(item);
                    currentRows.delete(id);
                    row.dataset.liveId = id;
                    updateRow(row, item);
                    const current = tbody.children[index];
                    if (current !== row) {
                        tbody.insertBefore(row, current || null);
                    }
                });
                currentRows.forEach(row => row.remove());
            }

            function setConnection(isLive) {
                const label = document.getElementById('liveConnectionLabel');
                const dot = document.getElementById('liveConnectionDot');
                setText(label, isLive ? 'LIVE' : 'TERPUTUS');
                const className = `w-2 h-2 rounded-full ${isLive ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'}`;
                if (dot && dot.className !== className) {
                    dot.className = className;
                }
            }

            function poll(url, onData, page = 1, interval = 2000) {
                let active = true;
                let pending = false;

                const refresh = async () => {
                    if (!active || pending || document.hidden) {
                        if (active) {
                            window.setTimeout(refresh, interval);
                        }
                        return;
                    }

                    pending = true;
                    try {
                        const target = new URL(url, window.location.origin);
                        target.searchParams.set('page', page);
                        const response = await fetch(target, {
                            headers: { Accept: 'application/json' },
                            cache: 'no-store'
                        });
                        if (!response.ok) {
                            throw new Error(`Pembaruan data gagal (HTTP ${response.status}).`);
                        }
                        const result = await response.json();
                        onData(result);
                        setConnection(true);
                        lastError = null;
                    } catch (error) {
                        setConnection(false);
                        const message = error instanceof Error ? error.message : String(error);
                        if (message !== lastError) {
                            console.error('Polling data real-time gagal:', error);
                            lastError = message;
                        }
                    } finally {
                        pending = false;
                        if (active) {
                            window.setTimeout(refresh, interval);
                        }
                    }
                };

                refresh();
                return () => {
                    active = false;
                };
            }

            return { setText, formatDate, formatNumber, setBadge, syncRows, poll };
        })();
    </script>
</body>
</html>