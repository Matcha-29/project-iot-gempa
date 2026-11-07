@extends('layouts.app', ['title' => 'Kelola Pengguna Akun Warga - Deteksi Gempa IoT'])

@section('main-content')
@include('components.navbar', [
    'title' => 'Pengguna',
    'subtitle' => 'Akun warga dibuat dan dikelola petugas RT 01'
])

<main class="p-8 space-y-6">
    <!-- 4 Kotak Ringkasan Data Akun -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">TOTAL WARGA</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">24</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">AKUN AKTIF</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-aman">21</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">BELUM AKTIVASI</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-waspada">3</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-brand-border shadow-sm">
            <span class="text-[11px] font-bold text-brand-muted uppercase">PETUGAS</span>
            <p class="text-3xl font-mono font-bold mt-1 text-brand-text">2</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Tabel Warga -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
            <div class="flex flex-col md:flex-row items-center justify-between pb-4 border-b border-brand-border gap-4">
                <div class="flex items-center gap-2">
                    <button class="px-3.5 py-1.5 bg-brand-text text-white rounded-xl text-xs font-semibold">Warga</button>
                    <button class="px-3.5 py-1.5 text-brand-muted hover:text-brand-text rounded-xl text-xs font-semibold">Petugas</button>
                </div>
                <input type="text" placeholder="Cari nama atau nomor..." class="px-4 py-2 border border-brand-border rounded-xl text-xs w-full md:w-60 focus:outline-none">
            </div>

            <div class="overflow-x-auto mt-2">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-brand-muted uppercase text-[10px] tracking-wider border-b border-brand-border">
                            <th class="py-3 px-2">Nama</th>
                            <th class="py-3 px-2">Telepon</th>
                            <th class="py-3 px-2">Rumah</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border">
                        <tr class="bg-amber-50/20">
                            <td class="py-3.5 px-2">
                                <span class="font-bold text-brand-text block">Ucup Santoso</span>
                                <span class="text-[10px] text-brand-muted">Akun baru dibuat</span>
                            </td>
                            <td class="py-3.5 px-2 font-mono text-brand-muted">0812-••••-5588</td>
                            <td class="py-3.5 px-2 font-mono">12</td>
                            <td class="py-3.5 px-2"><span class="px-2 py-0.5 rounded bg-amber-100 text-brand-waspada text-[10px] font-bold">Belum aktivasi</span></td>
                            <td class="py-3.5 px-2 text-right">
                                <button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Buat ulang kode</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-2">
                                <span class="font-bold text-brand-text block">Rosa Mania</span>
                                <span class="text-[10px] text-amber-700">Ada lansia di rumah</span>
                            </td>
                            <td class="py-3.5 px-2 font-mono text-brand-muted">0812-••••-3341</td>
                            <td class="py-3.5 px-2 font-mono">03</td>
                            <td class="py-3.5 px-2"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Aktif</span></td>
                            <td class="py-3.5 px-2 text-right">
                                <button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Reset kata sandi</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-2">
                                <span class="font-bold text-brand-text block">Aulia Wijaya</span>
                            </td>
                            <td class="py-3.5 px-2 font-mono text-brand-muted">0813-••••-7720</td>
                            <td class="py-3.5 px-2 font-mono">07</td>
                            <td class="py-3.5 px-2"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Aktif</span></td>
                            <td class="py-3.5 px-2 text-right">
                                <button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Reset kata sandi</button>
                            </td>
                        </tr>
                        <tr class="bg-amber-50/20">
                            <td class="py-3.5 px-2">
                                <span class="font-bold text-brand-text block">Dandi Kusuma</span>
                            </td>
                            <td class="py-3.5 px-2 font-mono text-brand-muted">0857-••••-1189</td>
                            <td class="py-3.5 px-2 font-mono">15</td>
                            <td class="py-3.5 px-2"><span class="px-2 py-0.5 rounded bg-amber-100 text-brand-waspada text-[10px] font-bold">Belum aktivasi</span></td>
                            <td class="py-3.5 px-2 text-right">
                                <button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Buat ulang kode</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-2">
                                <span class="font-bold text-brand-text block">Alfi Pratama</span>
                            </td>
                            <td class="py-3.5 px-2 font-mono text-brand-muted">0821-••••-5406</td>
                            <td class="py-3.5 px-2 font-mono">09</td>
                            <td class="py-3.5 px-2"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Aktif</span></td>
                            <td class="py-3.5 px-2 text-right">
                                <button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Reset kata sandi</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-2">
                                <span class="font-bold text-brand-text block">Ghabriella Lestari</span>
                                <span class="text-[10px] text-brand-muted">Pindah rumah</span>
                            </td>
                            <td class="py-3.5 px-2 font-mono text-brand-muted">0878-••••-2260</td>
                            <td class="py-3.5 px-2 font-mono">21</td>
                            <td class="py-3.5 px-2"><span class="px-2 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px] font-bold">Nonaktif</span></td>
                            <td class="py-3.5 px-2 text-right">
                                <button class="px-3 py-1 rounded border border-brand-border hover:bg-gray-50 text-xs font-semibold">Aktifkan</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Tambah Warga & Voucher Kode Aktivasi -->
        <div class="space-y-6">
            <!-- Form Tambah Warga -->
            <div class="bg-white p-6 rounded-2xl border border-brand-border shadow-sm">
                <h3 class="font-bold text-sm text-brand-text mb-4">Tambah Warga</h3>
                <form class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Nama Lengkap</label>
                        <input type="text" placeholder="Nama warga" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none">
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Nomor Telepon</label>
                            <input type="text" placeholder="08xx-xxxx-xxxx" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none font-mono">
                        </div>
                        <div>
                            <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">No. Rumah</label>
                            <input type="text" placeholder="12" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Email (opsional)</label>
                        <input type="email" placeholder="nama@contoh.id" class="w-full px-3 py-2.5 border border-brand-border rounded-xl focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-brand-muted uppercase text-[10px] mb-1">Catatan Khusus (opsional)</label>
                        <textarea rows="2" placeholder="Contoh: ada lansia atau penyandang disabilitas di rumah" class="w-full px-3 py-2 border border-brand-border rounded-xl focus:outline-none"></textarea>
                        <span class="text-[10px] text-brand-muted block mt-1">Sukarela. Dipakai petugas hanya untuk membantu saat evakuasi.</span>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-brand-aman text-white font-semibold text-xs hover:bg-[#14662F]">
                            Simpan dan Buat Kode Aktivasi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Banner Kode Aktivasi Baru -->
            <div class="bg-emerald-50 border border-emerald-200 p-5 rounded-2xl">
                <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">AKUN DIBUAT · BERIKAN KODE INI KE WARGA</span>
                <div class="mt-3 flex items-center justify-between bg-white p-3.5 rounded-xl border border-emerald-200">
                    <span class="font-mono text-2xl font-bold tracking-wider text-brand-text">RT01-7K4M</span>
                    <button onclick="navigator.clipboard.writeText('RT01-7K4M'); alert('Kode disalin!');" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold">
                        Salin Kode
                    </button>
                </div>
                <p class="text-[11px] text-emerald-900 mt-2 leading-relaxed">
                    Untuk <b>Budi Santoso</b>. Berlaku 24 jam dan hanya bisa dipakai sekali. Kode tidak akan ditampilkan lagi setelah halaman ini ditutup.
                </p>
            </div>
        </div>
    </div>
</main>
@endsection