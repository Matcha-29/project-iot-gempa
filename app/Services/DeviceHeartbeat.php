<?php

namespace App\Services;

use App\Models\Perangkat;
use App\Models\Sensor;

class DeviceHeartbeat
{
    public static function expireStaleDevices(): void
    {
        $timeout = max(5, (int) config('iot.device_timeout_seconds', 15));
        $cutoff = now()->subSeconds($timeout);

        $staleDeviceIds = Perangkat::query()
            ->where('status', 'online')
            ->where(function ($query) use ($cutoff): void {
                $query->whereNull('heartbeat_terakhir')
                    ->orWhere('heartbeat_terakhir', '<=', $cutoff);
            })
            ->pluck('id_perangkat')
            ->all();

        if (empty($staleDeviceIds)) {
            return;
        }

        Perangkat::query()
            ->whereIn('id_perangkat', $staleDeviceIds)
            ->update(['status' => 'offline', 'updated_at' => now()]);

        Sensor::query()
            ->whereIn('id_perangkat', $staleDeviceIds)
            ->update([
                'status' => 'error',
                'status_diperbarui_pada' => now(),
                'keterangan_error' => "Heartbeat tidak diterima dalam {$timeout} detik.",
                'updated_at' => now(),
            ]);
    }
}
