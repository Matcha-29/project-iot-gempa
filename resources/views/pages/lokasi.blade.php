@extends('layouts.app', ['title' => 'Lokasi - Sistem Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Lokasi',
    'subtitle' => 'Data wilayah pantauan dan penempatan node sensor diagonal.'
])

<main class="p-6 md:p-8 space-y-6">
    {{-- Notifikasi Sukses / Error --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium shadow-xs">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium space-y-1 shadow-xs">
            @foreach($errors->all() as $err)
                <p>• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    {{-- Layout 1 Kolom Penuh --}}
    <div class="space-y-6">

        {{-- ═══ Kartu 1: Ringkasan Lokasi (Full Width) ═══ --}}
        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                <div>
                    <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider">LOKASI MONITORING</span>
                    <h2 class="text-xl font-bold text-brand-text mt-0.5">{{ $lokasiUtama->nama_lokasi ?? 'Lokasi Belum Diatur' }}</h2>
                    <p class="text-xs text-brand-muted mt-0.5">{{ $lokasiUtama->alamat ?? 'Alamat belum diisi' }}</p>
                </div>

                <div class="flex items-center gap-3">
                    @php
                        $badgeClass = ($onlineCount === 2)
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : ($onlineCount === 1 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-600 border-red-200');
                        $dotClass = ($onlineCount === 2)
                            ? 'bg-emerald-500 animate-pulse'
                            : ($onlineCount === 1 ? 'bg-amber-500' : 'bg-red-400');
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-[11px] font-bold tracking-wide {{ $badgeClass }}">
                        <span class="w-2 h-2 rounded-full {{ $dotClass }}"></span>
                        {{ $onlineCount }}/2 NODE ONLINE
                    </span>

                    {{-- Tombol Trigger Pop-Up Ubah Data Lokasi --}}
                    @if($lokasiUtama)
                        <button type="button" onclick="openLokasiModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#14201A] hover:bg-black text-white text-xs font-semibold tracking-wide transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <span>Ubah Data Lokasi</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- 3 Kotak Statistik Sejajar --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-[#F8F9F7] p-4 rounded-xl border border-brand-border/60">
                    <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider">Warga terdaftar</span>
                    <p class="text-2xl font-bold text-brand-text mt-1">{{ $wargaCount }}</p>
                    <p class="text-[10px] text-brand-muted mt-0.5">Penerima notifikasi darurat gempa</p>
                </div>
                <div class="bg-[#F8F9F7] p-4 rounded-xl border border-brand-border/60">
                    <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider">Node terpasang</span>
                    <p class="text-2xl font-bold text-brand-text mt-1">2</p>
                    <p class="text-[10px] text-brand-muted">ESP32 sensor getaran diagonal tetap</p>
                </div>
                <div class="bg-[#F8F9F7] p-4 rounded-xl border border-brand-border/60">
                    <span class="text-[10px] font-bold text-brand-muted uppercase tracking-wider">Koordinat Acuan Lokasi</span>
                    <p class="text-xs font-mono font-bold text-brand-text mt-2 truncate" title="{{ $lokasiUtama->latitude ?? '0' }}, {{ $lokasiUtama->longitude ?? '0' }}">
                        {{ $lokasiUtama->latitude ? number_format((float)$lokasiUtama->latitude, 6) : '-' }}, {{ $lokasiUtama->longitude ? number_format((float)$lokasiUtama->longitude, 6) : '-' }}
                    </p>
                    <p class="text-[10px] text-brand-muted mt-0.5">Titik acuan pusat pemantauan</p>
                </div>
            </div>
        </div>

        {{-- ═══ Kartu 2: Posisi Node di Lokasi (Peta Denah Statis Diagonal) ═══ --}}
        <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-brand-text">Posisi Node di {{ $lokasiUtama->nama_lokasi ?? 'Lokasi Pantauan' }}</h3>
                    <p class="text-xs text-brand-muted mt-0.5">Skema penempatan 2 sensor ESP32 secara diagonal</p>
                </div>
                <span class="text-[10px] font-semibold text-brand-muted bg-gray-100 px-2.5 py-1 rounded-md">Denah Wilayah Diagonal</span>
            </div>

            {{-- Area Denah Ilustrasi Kompak & Lebar --}}
            <div class="relative bg-[#EBF0EA] rounded-2xl border border-brand-border/80 overflow-hidden h-60 p-4">
                {{-- Grid dan Blok Bangunan Denah --}}
                <svg class="absolute inset-0 w-full h-full opacity-60 pointer-events-none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="streetGrid" width="48" height="48" patternUnits="userSpaceOnUse">
                            <path d="M 48 0 L 0 0 0 48" fill="none" stroke="#D3DDD1" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#streetGrid)"/>
                    <rect x="15%" y="15%" width="20%" height="30%" rx="6" fill="#DCE5D9"/>
                    <rect x="65%" y="18%" width="22%" height="28%" rx="6" fill="#DCE5D9"/>
                    <rect x="14%" y="58%" width="22%" height="28%" rx="6" fill="#DCE5D9"/>
                    <rect x="62%" y="56%" width="22%" height="30%" rx="6" fill="#DCE5D9"/>
                    {{-- Garis diagonal penghubung --}}
                    <line x1="10%" y1="20%" x2="90%" y2="80%" stroke="#2F5BEA" stroke-width="2" stroke-dasharray="6,6" opacity="0.6"/>
                </svg>

                {{-- Node A (Sudut Kiri Atas) --}}
                <div class="absolute top-4 left-4 z-10">
                    <div class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-1.5 rounded-full shadow-md text-xs font-bold">
                        <span class="w-2 h-2 rounded-full {{ ($node1 && $node1->is_online) ? 'bg-white animate-pulse' : 'bg-gray-300' }}"></span>
                        <span>Node 1 · {{ $node1->kode_perangkat ?? 'ND-001' }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono font-medium text-blue-900 bg-white/95 backdrop-blur-xs px-2.5 py-0.5 rounded shadow-xs mt-1.5 border border-blue-200 inline-block">
                            {{ ($node1 && $node1->latitude && $node1->longitude) ? number_format((float)$node1->latitude, 7) . ', ' . number_format((float)$node1->longitude, 7) : 'Koordinat belum diatur' }}
                        </p>
                    </div>
                </div>

                {{-- Pusat Pemantauan (Tengah) --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 flex flex-col items-center">
                    <div class="flex items-center gap-2 bg-emerald-700 text-white px-4 py-2 rounded-xl shadow-lg text-xs font-bold">
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>{{ $lokasiUtama->nama_lokasi ?? 'Pos Pantauan' }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-brand-muted bg-white/90 backdrop-blur-xs px-2.5 py-0.5 rounded mt-1 shadow-xs border border-brand-border">
                        Area Sensor Diagonal
                    </span>
                </div>

                {{-- Node B (Sudut Kanan Bawah) --}}
                <div class="absolute bottom-4 right-4 z-10 text-right">
                    <div class="inline-flex items-center gap-1.5 bg-amber-600 text-white px-3 py-1.5 rounded-full shadow-md text-xs font-bold">
                        <span class="w-2 h-2 rounded-full {{ ($node2 && $node2->is_online) ? 'bg-white animate-pulse' : 'bg-gray-300' }}"></span>
                        <span>Node 2 · {{ $node2->kode_perangkat ?? 'ND-002' }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono font-medium text-amber-900 bg-white/95 backdrop-blur-xs px-2.5 py-0.5 rounded shadow-xs mt-1.5 border border-amber-200 inline-block">
                            {{ ($node2 && $node2->latitude && $node2->longitude) ? number_format((float)$node2->latitude, 7) . ', ' . number_format((float)$node2->longitude, 7) : 'Koordinat belum diatur' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Titik Koordinat Berdampingan --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/40">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">Node 1 (ND-001)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($node1 && $node1->is_online) ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                            {{ ($node1 && $node1->is_online) ? 'Aktif' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-xs font-mono font-bold text-brand-text mt-1.5">
                        {{ ($node1 && $node1->latitude && $node1->longitude) ? number_format((float)$node1->latitude, 7) . ', ' . number_format((float)$node1->longitude, 7) : 'Koordinat belum diatur' }}
                    </p>
                    <p class="text-[10px] text-brand-muted mt-0.5">{{ $node1->posisi_node ?? 'Node A Diagonal 1' }}</p>
                </div>

                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">Node 2 (ND-002)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($node2 && $node2->is_online) ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                            {{ ($node2 && $node2->is_online) ? 'Aktif' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-xs font-mono font-bold text-brand-text mt-1.5">
                        {{ ($node2 && $node2->latitude && $node2->longitude) ? number_format((float)$node2->latitude, 7) . ', ' . number_format((float)$node2->longitude, 7) : 'Koordinat belum diatur' }}
                    </p>
                    <p class="text-[10px] text-brand-muted mt-0.5">{{ $node2->posisi_node ?? 'Node B Diagonal 2' }}</p>
                </div>
            </div>

            {{-- Catatan Penempatan --}}
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-brand-border/60">
                <svg class="w-4 h-4 text-brand-muted mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs text-brand-muted leading-relaxed">
                    <span class="font-semibold text-brand-text">Peta ilustrasi.</span> Pasang kedua node di sudut yang berseberangan (diagonal) agar getaran dari satu arah tidak hanya terbaca oleh satu node.
                </p>
            </div>
        </div>

    </div>
</main>

{{-- ═══ POP-UP MODAL: UBAH DATA LOKASI & KOORDINAT ═══ --}}
@if($lokasiUtama)
<div id="lokasiModal" onclick="closeModalOnBackdrop(event)" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-brand-border space-y-4 my-8" onclick="event.stopPropagation()">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between border-b border-brand-border pb-3.5">
            <div>
                <h3 class="text-base font-bold text-brand-text">Ubah Data Lokasi</h3>
                <p class="text-xs text-brand-muted mt-0.5">Dipakai untuk tampilan di dashboard dan aplikasi warga.</p>
            </div>
            <button type="button" onclick="closeLokasiModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-brand-text hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Edit Lengkap --}}
        <form action="{{ route('lokasi.update', $lokasiUtama->id_lokasi) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Baris 1: Nama Lokasi & Nomor Petugas (Grid 2 Kolom) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-brand-text mb-1">Nama lokasi</label>
                    <input type="text" name="nama_lokasi" value="{{ old('nama_lokasi', $lokasiUtama->nama_lokasi) }}" required
                        placeholder="Contoh: Pos Monitoring Polije"
                        class="w-full px-3 py-2 rounded-xl border border-brand-border text-xs text-brand-text focus:outline-none focus:ring-1 focus:ring-brand-aman">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-brand-text mb-1">Nomor petugas untuk warga</label>
                    <input type="text" name="nomor_petugas" value="{{ old('nomor_petugas', $lokasiUtama->nomor_petugas) }}"
                        placeholder="08xxxxxxxxxx"
                        class="w-full px-3 py-2 rounded-xl border border-brand-border text-xs font-mono text-brand-text focus:outline-none focus:ring-1 focus:ring-brand-aman">
                </div>
            </div>

            {{-- Baris 2: Alamat --}}
            <div>
                <label class="block text-xs font-semibold text-brand-text mb-1">Alamat lengkap</label>
                <textarea name="alamat" rows="2" required
                    placeholder="Alamat atau gedung lokasi pemantauan"
                    class="w-full px-3 py-2 rounded-xl border border-brand-border text-xs text-brand-text focus:outline-none focus:ring-1 focus:ring-brand-aman">{{ old('alamat', $lokasiUtama->alamat) }}</textarea>
            </div>

            {{-- Baris 3: Koordinat Lokasi Acuan Pusat --}}
            <div class="p-3.5 rounded-xl border border-brand-border bg-gray-50/70 space-y-2">
                <span class="text-xs font-bold text-brand-text">Koordinat Acuan Lokasi Utama</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-brand-muted mb-1">Latitude Lokasi</label>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude', $lokasiUtama->latitude) }}" required
                            placeholder="-8.1585000"
                            class="w-full px-3 py-1.5 rounded-lg border border-brand-border text-xs font-mono bg-white text-brand-text focus:ring-1 focus:ring-brand-aman">
                    </div>
                    <div>
                        <label class="block text-[11px] text-brand-muted mb-1">Longitude Lokasi</label>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude', $lokasiUtama->longitude) }}" required
                            placeholder="113.7233000"
                            class="w-full px-3 py-1.5 rounded-lg border border-brand-border text-xs font-mono bg-white text-brand-text focus:ring-1 focus:ring-brand-aman">
                    </div>
                </div>
            </div>

            {{-- Baris 4: Koordinat 2 Node Diagonal (Grid 2 Kolom) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                {{-- Node 1 --}}
                <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-800">Koordinat Node 1 (ND-001)</span>
                        <span class="text-[9px] font-mono bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded">Diagonal 1</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-brand-muted mb-0.5">Latitude</label>
                            <input type="number" step="any" name="lat_node1" value="{{ old('lat_node1', $node1->latitude ?? '') }}"
                                placeholder="-8.1584200"
                                class="w-full px-2.5 py-1.5 rounded-lg border border-blue-200 text-xs font-mono bg-white text-brand-text focus:ring-1 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-[10px] text-brand-muted mb-0.5">Longitude</label>
                            <input type="number" step="any" name="lng_node1" value="{{ old('lng_node1', $node1->longitude ?? '') }}"
                                placeholder="113.7232100"
                                class="w-full px-2.5 py-1.5 rounded-lg border border-blue-200 text-xs font-mono bg-white text-brand-text focus:ring-1 focus:ring-blue-600">
                        </div>
                    </div>
                </div>

                {{-- Node 2 --}}
                <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-800">Koordinat Node 2 (ND-002)</span>
                        <span class="text-[9px] font-mono bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">Diagonal 2</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-brand-muted mb-0.5">Latitude</label>
                            <input type="number" step="any" name="lat_node2" value="{{ old('lat_node2', $node2->latitude ?? '') }}"
                                placeholder="-8.1585800"
                                class="w-full px-2.5 py-1.5 rounded-lg border border-amber-200 text-xs font-mono bg-white text-brand-text focus:ring-1 focus:ring-amber-600">
                        </div>
                        <div>
                            <label class="block text-[10px] text-brand-muted mb-0.5">Longitude</label>
                            <input type="number" step="any" name="lng_node2" value="{{ old('lng_node2', $node2->longitude ?? '') }}"
                                placeholder="113.7233900"
                                class="w-full px-2.5 py-1.5 rounded-lg border border-amber-200 text-xs font-mono bg-white text-brand-text focus:ring-1 focus:ring-amber-600">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Tombol Modal --}}
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-border">
                <button type="button" onclick="closeLokasiModal()" class="px-4 py-2 rounded-xl border border-brand-border text-xs font-semibold text-brand-muted hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-aman hover:bg-emerald-800 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function openLokasiModal() {
        const modal = document.getElementById('lokasiModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeLokasiModal() {
        const modal = document.getElementById('lokasiModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function closeModalOnBackdrop(event) {
        if (event.target === document.getElementById('lokasiModal')) {
            closeLokasiModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLokasiModal();
        }
    });
</script>
@endsection
