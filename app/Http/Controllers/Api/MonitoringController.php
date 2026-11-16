<?php

namespace App\Http\Controllers\Api;

use App\Models\Deteksi;
use App\Models\Perangkat;
use App\Services\DeviceHeartbeat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends ApiController
{
    public function statusWilayah(): JsonResponse
    {
        DeviceHeartbeat::expireStaleDevices();
        $node1 = Perangkat::query()
            ->where('posisi_node', 'Node A Diagonal 1')
            ->with(['levelGetaran' => fn ($query) => $query->latest('dibuat_pada')->latest('id_level')->limit(1)])
            ->latest('id_perangkat')
            ->first();

        $node2 = $node1
            ? Perangkat::query()
                ->where('id_lokasi', $node1->id_lokasi)
                ->where('posisi_node', 'Node B Diagonal 2')
                ->with(['levelGetaran' => fn ($query) => $query->latest('dibuat_pada')->latest('id_level')->limit(1)])
                ->first()
            : null;

        $deviceIds = collect([$node1?->getKey(), $node2?->getKey()])
            ->filter()
            ->all();

        $latestDetection = $deviceIds !== []
            ? Deteksi::query()
                ->where(function ($query) use ($deviceIds): void {
                    $query->whereIn('id_perangkat_a', $deviceIds)
                        ->orWhereIn('id_perangkat_b', $deviceIds);
                })
                ->latest('id_deteksi')
                ->first()
            : null;

        $maxMagnitude = collect([$node1, $node2])
            ->filter()
            ->where('status', 'online')
            ->map(fn (Perangkat $device) => $device->levelGetaran->first()?->nilai_magnitudo)
            ->filter(fn ($value) => $value !== null)
            ->max();

        $maxG = collect([$node1, $node2])
            ->filter()
            ->where('status', 'online')
            ->map(fn (Perangkat $device) => $device->levelGetaran->first()?->nilai_getaran_g)
            ->filter(fn ($value) => $value !== null)
            ->max();

        $statusWilayah = $latestDetection && $latestDetection->berakhir_pada === null
            ? match ($latestDetection->jenis) {
                'gempa' => 'BAHAYA',
                'gangguan_lokal' => 'GANGGUAN',
                default => 'AMAN',
            }
        : 'AMAN';

        return $this->success('Status wilayah berhasil diambil.', [
            'status_wilayah' => $statusWilayah,
            'node1_status' => $node1?->status ?? 'offline',
            'node2_status' => $node2?->status ?? 'offline',
            'max_magnitude' => $maxMagnitude,
            'max_g' => $maxG,
            'deteksi_terakhir' => $latestDetection,
        ]);
    }

    public function riwayatGempa(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->failure('Parameter pagination tidak valid.', ['errors' => $validator->errors()], 422);
        }

        $riwayat = Deteksi::query()
            ->with(['perangkatA:id_perangkat,kode_perangkat', 'perangkatB:id_perangkat,kode_perangkat'])
            ->where('jenis', 'gempa')
            ->latest('waktu_node_a')
            ->paginate($validator->validated()['per_page'] ?? 20);

        return $this->success('Riwayat gempa berhasil diambil.', [
            'items' => $riwayat->items(),
            'pagination' => [
                'current_page' => $riwayat->currentPage(),
                'per_page' => $riwayat->perPage(),
                'last_page' => $riwayat->lastPage(),
                'total' => $riwayat->total(),
            ],
        ]);
    }
}
