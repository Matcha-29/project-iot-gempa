<?php

namespace App\Http\Controllers;

use App\Models\Deteksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeteksiController extends Controller
{
    public function index()
    {
        $deteksi = Deteksi::with(['perangkatA', 'perangkatB', 'responsWarga'])->latest('waktu_node_a')->paginate(15);
        return view('pages.deteksi', compact('deteksi'));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $deteksi = Deteksi::with(['perangkatA', 'perangkatB'])
            ->latest('waktu_node_a')
            ->paginate(15);

        return response()->json([
            'items' => $deteksi->getCollection()->map(static fn (Deteksi $item): array => [
                'id' => $item->getKey(),
                'detected_at' => $item->waktu_node_a?->toIso8601String(),
                'kind' => $item->jenis,
                'node_a' => $item->perangkatA?->kode_perangkat,
                'node_b' => $item->perangkatB?->kode_perangkat,
                'delay_ms' => $item->jeda_waktu_node,
                'consensus' => (bool) $item->status_konsensus,
                'severity' => $item->tingkat_keparahan,
                'description' => $item->deskripsi,
            ])->values(),
            'pagination' => [
                'current_page' => $deteksi->currentPage(),
                'last_page' => $deteksi->lastPage(),
                'total' => $deteksi->total(),
            ],
        ]);
    }
}
