<?php

namespace App\Services;

use App\Models\Perangkat;
use App\Models\Sensor;
use Illuminate\Support\Facades\DB;

class DeviceHeartbeat
{
    public static function expireStaleDevices(): void
    {
        $timeout = max(5, (int) config('iot.device_timeout_seconds', 15));
        $cutoff = now()->subSeconds($timeout);

        DB::transaction(function () use ($cutoff, $timeout): void {
            $staleDevices = Perangkat::query()
                ->where('status', 'online')
                ->where(function ($query) use ($cutoff): void {
                    $query->whereNull('heartbeat_terakhir')
                        ->orWhere('heartbeat_terakhir', '<=', $cutoff);
                })
                ->lockForUpdate()
                ->get(['id_perangkat']);

            if ($staleDevices->isEmpty()) {
                return;
            }

            $deviceIds = $staleDevices->modelKeys();
            Perangkat::query()
                ->whereIn('id_perangkat', $deviceIds)
                ->update(['status' => 'offline', 'updated_at' => now()]);

            Sensor::query()
                ->whereIn('id_perangkat', $deviceIds)
                ->update([
                    'status' => 'error',
                    'status_diperbarui_pada' => now(),
                    'keterangan_error' => "Heartbeat tidak diterima dalam {$timeout} detik.",
                    'updated_at' => now(),
                ]);
        });
    }
}
