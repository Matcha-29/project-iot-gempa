@extends('layouts.app', ['title' => 'Pengguna - Sistem Gempa IoT'])

@section('main-content')
    <header class="flex flex-col gap-4 border-b border-brand-border bg-[#F3F5F1] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <div>
            <h1 class="text-2xl font-bold leading-tight text-brand-text">Pengguna</h1>
            <p class="mt-1 text-xs text-brand-muted">Akun warga dibuat dan dikelola petugas RT 01</p>
        </div>
        <button id="tambahWargaButton" type="button" class="inline-flex items-center justify-center rounded-xl bg-brand-text px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-black">
            + Tambah Warga
        </button>
    </header>

    <main class="flex-1 space-y-5 p-5 sm:p-8">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan pengguna">
            <div class="rounded-2xl border border-brand-border bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold tracking-wider text-brand-muted">TOTAL WARGA</p>
                <p class="mt-1 text-2xl font-bold text-brand-text">{{ $statistik['totalWarga'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold tracking-wider text-emerald-800">AKUN AKTIF</p>
                <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $statistik['akunAktif'] }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold tracking-wider text-amber-800">BELUM AKTIVASI</p>
                <p class="mt-1 text-2xl font-bold text-amber-900">{{ $statistik['belumAktivasi'] }}</p>
            </div>
            <div class="rounded-2xl border border-brand-border bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold tracking-wider text-brand-muted">PETUGAS</p>
                <p class="mt-1 text-2xl font-bold text-brand-text">{{ $statistik['petugas'] }}</p>
            </div>
        </section>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,1fr)]">
            <section class="min-w-0 overflow-hidden rounded-2xl border border-brand-border bg-white p-4 shadow-sm sm:p-5" aria-label="Daftar pengguna">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <nav class="flex w-fit items-center gap-1 rounded-full bg-[#F3F5F1] p-1" aria-label="Filter peran pengguna">
                        <a href="{{ route('pengguna.index', ['peran' => 'warga', 'q' => request('q')]) }}"
                            @class([
                                'rounded-full px-3 py-1.5 text-[11px] font-semibold transition',
                                'bg-brand-text text-white shadow-sm' => $peranAktif === 'warga',
                                'text-brand-muted hover:text-brand-text' => $peranAktif !== 'warga',
                            ])>
                            Warga
                        </a>
                        <a href="{{ route('pengguna.index', ['peran' => 'petugas', 'q' => request('q')]) }}"
                            @class([
                                'rounded-full px-3 py-1.5 text-[11px] font-semibold transition',
                                'bg-brand-text text-white shadow-sm' => $peranAktif === 'petugas',
                                'text-brand-muted hover:text-brand-text' => $peranAktif !== 'petugas',
                            ])>
                            Petugas
                        </a>
                    </nav>

                    <form method="GET" action="{{ route('pengguna.index') }}" class="flex w-full gap-2 sm:w-auto">
                        <input type="hidden" name="peran" value="{{ $peranAktif }}">
                        <label class="sr-only" for="cariPengguna">Cari nama atau nomor</label>
                        <input id="cariPengguna" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor"
                            class="min-w-0 flex-1 rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-brand-text outline-none transition placeholder:text-brand-muted/70 focus:border-brand-aman focus:ring-2 focus:ring-brand-aman/15 sm:w-48">
                        <button type="submit" class="rounded-lg border border-brand-border px-3 py-2 text-xs font-semibold text-brand-text hover:bg-[#F3F5F1]">Cari</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left text-xs">
                        <thead>
                            <tr class="border-y border-brand-border bg-[#F8F9F7] text-[10px] font-bold tracking-wide text-brand-muted">
                                <th scope="col" class="px-2 py-3">NAMA</th>
                                <th scope="col" class="px-2 py-3">TELEPON</th>
                                <th scope="col" class="px-2 py-3">RUMAH</th>
                                <th scope="col" class="px-2 py-3">STATUS</th>
                                <th scope="col" class="px-2 py-3">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            @forelse ($pengguna as $item)
                                <tr>
                                    <td class="px-2 py-3">
                                        <div class="font-semibold text-brand-text">{{ $item->nama }}</div>
                                        @if ($item->catatan_khusus || $item->status_akun === 'belum_aktivasi')
                                            <div class="mt-0.5 max-w-48 truncate text-[10px] text-brand-muted">
                                                {{ $item->catatan_khusus ?: 'Akun baru dibuat' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-2 py-3 text-brand-muted">{{ $item->no_telepon ?: '-' }}</td>
                                    <td class="px-2 py-3 text-brand-muted">{{ $item->nomor_rumah ?: '-' }}</td>
                                    <td class="px-2 py-3">
                                        @php
                                            $statusClass = match ($item->status_akun) {
                                                'aktif' => 'bg-emerald-100 text-emerald-800',
                                                'nonaktif' => 'bg-gray-100 text-gray-700',
                                                default => 'bg-amber-100 text-amber-800',
                                            };
                                            $statusLabel = match ($item->status_akun) {
                                                'aktif' => 'Aktif',
                                                'nonaktif' => 'Nonaktif',
                                                default => 'Belum aktivasi',
                                            };
                                        @endphp
                                        <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-2 py-3">
                                        @if ($item->peran === 'warga' && $item->status_akun === 'belum_aktivasi')
                                            <form method="POST" action="{{ route('pengguna.kode-aktivasi', $item) }}">
                                                @csrf
                                                <button type="submit" class="whitespace-nowrap text-[10px] font-bold text-brand-aman hover:underline">Buat ulang kode</button>
                                            </form>
                                        @elseif ($item->peran === 'warga' && $item->status_akun === 'aktif')
                                            <form method="POST" action="{{ route('pengguna.kode-aktivasi', $item) }}">
                                                @csrf
                                                <button type="submit" class="whitespace-nowrap text-[10px] font-bold text-brand-aman hover:underline">Reset kata sandi</button>
                                            </form>
                                        @elseif ($item->peran === 'warga' && $item->status_akun === 'nonaktif')
                                            <form method="POST" action="{{ route('pengguna.aktifkan', $item) }}">
                                                @csrf
                                                <button type="submit" class="whitespace-nowrap text-[10px] font-bold text-brand-aman hover:underline">Aktifkan</button>
                                            </form>
                                        @else
                                            <span class="text-brand-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-2 py-10 text-center text-brand-muted">
                                        {{ $peranAktif === 'warga' ? 'Belum ada data warga' : 'Belum ada data petugas' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pengguna->hasPages())
                    <div class="mt-4">{{ $pengguna->links() }}</div>
                @endif
            </section>

            <aside class="space-y-4">
                <section id="form-tambah-warga" class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold text-brand-text">Tambah Warga</h2>
                    <form method="POST" action="{{ route('pengguna.store') }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="nama" class="mb-1 block text-[11px] font-semibold text-brand-text">Nama lengkap</label>
                            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required maxlength="100" autocomplete="name" placeholder="Nama warga"
                                class="w-full rounded-lg border border-brand-border px-3 py-2.5 text-xs outline-none transition placeholder:text-brand-muted/70 focus:border-brand-aman focus:ring-2 focus:ring-brand-aman/15">
                            @error('nama') <p class="mt-1 text-[10px] text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="no_telepon" class="mb-1 block text-[11px] font-semibold text-brand-text">Nomor telepon</label>
                                <input id="no_telepon" name="no_telepon" type="tel" value="{{ old('no_telepon') }}" required maxlength="20" autocomplete="tel" placeholder="08xx-xxxx-xxxx"
                                    class="w-full rounded-lg border border-brand-border px-3 py-2.5 text-xs outline-none transition placeholder:text-brand-muted/70 focus:border-brand-aman focus:ring-2 focus:ring-brand-aman/15">
                                @error('no_telepon') <p class="mt-1 text-[10px] text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="nomor_rumah" class="mb-1 block text-[11px] font-semibold text-brand-text">No. rumah</label>
                                <input id="nomor_rumah" name="nomor_rumah" type="text" value="{{ old('nomor_rumah') }}" required maxlength="20" placeholder="12"
                                    class="w-full rounded-lg border border-brand-border px-3 py-2.5 text-xs outline-none transition placeholder:text-brand-muted/70 focus:border-brand-aman focus:ring-2 focus:ring-brand-aman/15">
                                @error('nomor_rumah') <p class="mt-1 text-[10px] text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="email" class="mb-1 block text-[11px] font-semibold text-brand-text">Email <span class="font-normal text-brand-muted">(opsional)</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" placeholder="nama@contoh.id"
                                class="w-full rounded-lg border border-brand-border px-3 py-2.5 text-xs outline-none transition placeholder:text-brand-muted/70 focus:border-brand-aman focus:ring-2 focus:ring-brand-aman/15">
                            @error('email') <p class="mt-1 text-[10px] text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="catatan_khusus" class="mb-1 block text-[11px] font-semibold text-brand-text">Catatan khusus <span class="font-normal text-brand-muted">(opsional)</span></label>
                            <textarea id="catatan_khusus" name="catatan_khusus" rows="2" maxlength="255" placeholder="Contoh: ada lansia atau penyandang disabilitas di rumah"
                                class="w-full resize-y rounded-lg border border-brand-border px-3 py-2.5 text-xs outline-none transition placeholder:text-brand-muted/70 focus:border-brand-aman focus:ring-2 focus:ring-brand-aman/15">{{ old('catatan_khusus') }}</textarea>
                            <p class="mt-1 text-[10px] leading-relaxed text-brand-muted">Sukarela. Dipakai petugas hanya untuk membantu saat evakuasi.</p>
                            @error('catatan_khusus') <p class="mt-1 text-[10px] text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-brand-aman px-4 py-2.5 text-xs font-bold text-white transition hover:bg-green-800">
                            Simpan dan Buat Kode Aktivasi
                        </button>
                    </form>
                </section>

                @if (session('kode_aktivasi_baru'))
                    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4" aria-live="polite">
                        <h2 class="text-[10px] font-bold tracking-wide text-emerald-800">
                            {{ session('kode_aktivasi_baru.aksi') === 'dibuat' ? 'AKUN DIBUAT · BERIKAN KODE INI KE WARGA' : 'KODE AKTIVASI BARU · BERIKAN KODE INI KE WARGA' }}
                        </h2>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <code id="kodeAktivasi" class="font-mono text-lg font-semibold tracking-[0.16em] text-brand-text">{{ session('kode_aktivasi_baru.kode') }}</code>
                            <button id="salinKodeButton" type="button" class="shrink-0 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-[10px] font-semibold text-brand-text hover:bg-emerald-100">
                                Salin Kode
                            </button>
                        </div>
                        <p id="salinKodeStatus" class="mt-1 min-h-3 text-[10px] text-emerald-800" role="status"></p>
                        <p class="mt-2 text-[10px] leading-relaxed text-emerald-900">
                            Berlaku 24 jam dan hanya bisa dipakai sekali. Kode tidak akan ditampilkan lagi setelah halaman ini ditutup.
                        </p>
                    </section>
                @endif
            </aside>
        </div>
    </main>

    <script>
        document.getElementById('tambahWargaButton').addEventListener('click', () => {
            const formSection = document.getElementById('form-tambah-warga');
            const nameInput = document.getElementById('nama');

            formSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            nameInput.focus({ preventScroll: true });
        });

        const copyButton = document.getElementById('salinKodeButton');
        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                const code = document.getElementById('kodeAktivasi').textContent.trim();
                const status = document.getElementById('salinKodeStatus');

                try {
                    await navigator.clipboard.writeText(code);
                    copyButton.textContent = 'Tersalin';
                    status.textContent = 'Kode aktivasi berhasil disalin.';
                } catch {
                    status.textContent = 'Tidak dapat menyalin kode. Silakan salin secara manual.';
                }
            });
        }
    </script>
@endsection
