<?php

namespace App\Http\Controllers\Api;

use App\Models\Perangkat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodeHeartbeatController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Token');
        if (! $token) {
            return $this->failure('Token Node 2 wajib dikirim.', [], 401);
        }

        $device = Perangkat::query()
            ->where('posisi_node', 'Node B Diagonal 2')
            ->where('api_token', $token)
            ->first();

        if (! $device) {
            return $this->failure('Token Node 2 tidak valid.', [], 401);
        }

        $heartbeatAt = now();
        $device->forceFill([
            'status' => 'online',
            'heartbeat_terakhir' => $heartbeatAt,
        ])->save();

        $buzzer = (bool) cache()->get('perintah_buzzer', false) || (bool) cache()->get('uji_sirine_aktif', false);

        return $this->success('Heartbeat Node 2 berhasil diterima.', [
            'kode_perangkat' => $device->kode_perangkat,
            'status' => 'online',
            'heartbeat_terakhir' => $heartbeatAt->toIso8601String(),
            'perintah_buzzer' => $buzzer,
        ]);
    }
}
