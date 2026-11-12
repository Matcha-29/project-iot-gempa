<?php

namespace App\Http\Controllers;

use App\Models\LevelGetaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetriController extends Controller
{
    public function index()
    {
        $telemetri = LevelGetaran::with('perangkat')->latest('dibuat_pada')->latest('id_level')->paginate(50);
        $terakhir = LevelGetaran::with('perangkat')->latest('dibuat_pada')->latest('id_level')->first();

        return view('pages.telemetri', compact('telemetri', 'terakhir'));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $telemetri = LevelGetaran::with('perangkat')
            ->latest('dibuat_pada')
            ->latest('id_level')
            ->paginate(50);
        $terakhir = LevelGetaran::with('perangkat')
            ->latest('dibuat_pada')
            ->latest('id_level')
            ->first();

        $serialize = static fn (?LevelGetaran $item): ?array => $item ? [
            'id' => $item->getKey(),
            'created_at' => $item->dibuat_pada?->toIso8601String(),
            'device_code' => $item->perangkat?->kode_perangkat,
            'acceleration_x' => $item->akselerasi_x,
            'acceleration_y' => $item->akselerasi_y,
            'acceleration_z' => $item->akselerasi_z,
            'vibration_g' => $item->nilai_getaran_g === null ? null : (float) $item->nilai_getaran_g,
            'magnitude' => $item->nilai_magnitudo === null ? null : (float) $item->nilai_magnitudo,
            'sw420_active' => (bool) $item->status_sw420,
            'category' => $item->kategori_level,
        ] : null;

        return response()->json([
            'items' => $telemetri->getCollection()->map($serialize)->values(),
            'latest' => $serialize($terakhir),
            'pagination' => [
                'current_page' => $telemetri->currentPage(),
                'last_page' => $telemetri->lastPage(),
                'total' => $telemetri->total(),
            ],
        ]);
    }
}
