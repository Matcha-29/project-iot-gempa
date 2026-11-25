<?php

namespace App\Http\Controllers;

use App\Models\LevelGetaran;
use App\Models\Lokasi;
use App\Models\Perangkat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->query('id');
        $lokasiUtama = $id ? Lokasi::find($id) : Lokasi::first();
        if (!$lokasiUtama) {
            $lokasiUtama = Lokasi::first();
        }

        $wargaCount = 0;
        if ($lokasiUtama) {
            $wargaCount = $lokasiUtama->pengguna()->count();
        }

        // Fixed 2 node system: ND-001 and ND-002
        $node1 = Perangkat::where('kode_perangkat', 'ND-001')->first();
        $node2 = Perangkat::where('kode_perangkat', 'ND-002')->first();

        $now = Carbon::now();
        $onlineThreshold = $now->copy()->subSeconds(15);

        $checkOnline = function ($p) use ($onlineThreshold) {
            if (!$p) return false;
            $latestLog = LevelGetaran::where('id_perangkat', $p->id_perangkat)
                ->latest('dibuat_pada')->first();
            return ($p->heartbeat_terakhir && $p->heartbeat_terakhir->gte($onlineThreshold)) ||
                   ($latestLog && $latestLog->dibuat_pada && $latestLog->dibuat_pada->gte($onlineThreshold));
        };

        if ($node1) $node1->is_online = (bool) $checkOnline($node1);
        if ($node2) $node2->is_online = (bool) $checkOnline($node2);

        $onlineCount = ($node1 && $node1->is_online ? 1 : 0) + ($node2 && $node2->is_online ? 1 : 0);
        $totalNode = 2;

        return view('pages.lokasi', compact(
            'lokasiUtama',
            'wargaCount',
            'totalNode',
            'onlineCount',
            'node1',
            'node2'
        ));
    }

    public function update(Request $request, Lokasi $lokasi)
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'nomor_petugas' => ['nullable', 'string', 'max:30'],

            'lat_node1' => ['nullable', 'numeric', 'between:-90,90'],
            'lng_node1' => ['nullable', 'numeric', 'between:-180,180'],

            'lat_node2' => ['nullable', 'numeric', 'between:-90,90'],
            'lng_node2' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $lokasi->update([
            'nama_lokasi' => $validated['nama_lokasi'],
            'alamat' => $validated['alamat'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'nomor_petugas' => $validated['nomor_petugas'] ?? null,
        ]);

        // Simpan koordinat Node 1 (ND-001)
        Perangkat::where('kode_perangkat', 'ND-001')->update([
            'latitude' => $validated['lat_node1'] ?? null,
            'longitude' => $validated['lng_node1'] ?? null,
        ]);

        // Simpan koordinat Node 2 (ND-002)
        Perangkat::where('kode_perangkat', 'ND-002')->update([
            'latitude' => $validated['lat_node2'] ?? null,
            'longitude' => $validated['lng_node2'] ?? null,
        ]);

        return redirect()->route('lokasi.index', ['id' => $lokasi->id_lokasi])
            ->with('success', 'Data lokasi dan koordinat node berhasil disimpan.');
    }
}
