@extends('layouts.app', ['title' => 'Pengguna - Sistem Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Pengguna', 'subtitle' => 'Daftar warga dan petugas'])
<main class="p-8"><div class="bg-white p-6 rounded-2xl border shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr class="border-b text-brand-muted"><th class="py-3">Nama</th><th>Email</th><th>No. Telepon</th><th>Peran</th><th>Status</th><th>Lokasi</th><th>Rumah</th></tr></thead><tbody class="divide-y">
@forelse($pengguna as $item)
<tr><td class="py-3 font-bold">{{ $item->nama }}</td><td>{{ $item->email ?? '-' }}</td><td>{{ $item->no_telepon }}</td><td>{{ ucfirst($item->peran) }}</td><td><span class="status-pill px-2 py-1 text-[10px] font-bold {{ $item->status_akun === 'aktif' ? 'bg-emerald-100 text-emerald-800' : ($item->status_akun === 'nonaktif' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst(str_replace('_', ' ', $item->status_akun)) }}</span></td><td>{{ optional($item->lokasi)->nama_lokasi ?? '-' }}</td><td>{{ $item->nomor_rumah ?? '-' }}</td></tr>
@empty
<tr><td colspan="7" class="py-8 text-center text-brand-muted">Belum ada data pengguna</td></tr>
@endforelse
</tbody></table></div><div class="mt-4">{{ $pengguna->links() }}</div></div></main>
@endsection