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
</body>
</html>