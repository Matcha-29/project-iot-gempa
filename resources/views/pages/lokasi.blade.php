@extends('layouts.app', ['title' => 'Lokasi - Sistem Gempa IoT'])
@section('main-content')
@include('components.navbar', ['title' => 'Lokasi', 'subtitle' => 'Daftar lokasi pemantauan dan perangkat'])
<main class="p-8"><div class="bg-white p-6 rounded-2xl border shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr class="border-b text-brand-muted"><th class="py-3">Nama</th><th>Alamat</th><th>Koordinat</th><th>Nomor Petugas</th><th>Perangkat</th><th>Pengguna</th></tr></thead><tbody class="divide-y">
@forelse($lokasi as $item)
<tr><td class="py-3 font-bold">{{ $item->nama_lokasi }}</td><td>{{ $item->alamat }}</td><td>{{ $item->latitude }}, {{ $item->longitude }}</td><td>{{ $item->nomor_petugas ?? '-' }}</td><td>{{ $item->perangkat_count }}</td><td>{{ $item->pengguna_count }}</td></tr>
@empty
<tr><td colspan="6" class="py-8 text-center text-brand-muted">Belum ada data lokasi</td></tr>
@endforelse
</tbody></table></div><div class="mt-4">{{ $lokasi->links() }}</div></div></main>
@endsection