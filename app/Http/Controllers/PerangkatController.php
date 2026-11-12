<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use App\Services\DeviceHeartbeat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerangkatController extends Controller
{
    public function index()
    {
        DeviceHeartbeat::expireStaleDevices();
        $perangkat = Perangkat::with(['lokasi', 'sensor'])->latest('created_at')->paginate(15);
        $total = Perangkat::count();
        $online = Perangkat::where('status', 'online')->count();
        $offline = $total - $online;

        return view('pages.perangkat', compact('perangkat', 'total', 'online', 'offline'));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        DeviceHeartbeat::expireStaleDevices();

        $perangkat = Perangkat::with(['lokasi', 'sensor'])->latest('created_at')->paginate(15);
        $items = $perangkat->getCollection()->map(static fn (Perangkat $item): array => [
            'id' => $item->getKey(),
            'code' => $item->kode_perangkat,
            'position' => $item->posisi_node,
            'location' => $item->lokasi?->nama_lokasi,
            'status' => $item->status,
            'heartbeat_at' => $item->heartbeat_terakhir?->toIso8601String(),
            'sensors' => $item->sensor->pluck('jenis_sensor')->implode(', '),
        ])->values();
        $total = Perangkat::count();
        $online = Perangkat::where('status', 'online')->count();

        return response()->json([
            'items' => $items,
            'summary' => [
                'total' => $total,
                'online' => $online,
                'offline' => $total - $online,
            ],
            'pagination' => [
                'current_page' => $perangkat->currentPage(),
                'last_page' => $perangkat->lastPage(),
                'total' => $perangkat->total(),
            ],
        ]);
    }
}
