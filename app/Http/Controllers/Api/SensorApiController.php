<?php

namespace App\Http\Controllers\Api;

use App\Models\Deteksi;
use App\Models\LevelGetaran;
use App\Models\LogAlarm;
use App\Models\Perangkat;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SensorApiController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'node1_vib' => ['required', 'boolean'],
            'node2_vib' => ['required', 'boolean'],
            'consensus' => ['required', 'boolean'],
            'node1_pure_g' => ['required', 'numeric', 'min:0', 'max:1000'],
            'node1_mag' => ['required', 'numeric', 'min:0', 'max:1000'],
            'node2_pure_g' => ['required', 'numeric', 'min:0', 'max:1000'],
            'node2_mag' => ['required', 'numeric', 'min:0', 'max:1000'],
            'node2_online' => ['required', 'boolean'],
            'timestamp' => ['sometimes', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->failure('Data telemetri tidak valid.', ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $token = $request->bearerToken() ?: $request->header('X-Device-Token');
        $node1 = Perangkat::query()
            ->where('posisi_node', 'Node A Diagonal 1')
            ->where('api_token', $token)
            ->first();

        if (! $token || ! $node1) {
            return $this->failure('Token ESP32 Master tidak valid.', [], 401);
        }

        $node2 = Perangkat::query()
            ->where('id_lokasi', $node1->id_lokasi)
            ->where('posisi_node', 'Node B Diagonal 2')
            ->first();

        if (! $node2) {
            return $this->failure('Perangkat Node 2 belum terdaftar di lokasi Node 1.', [], 409);
        }

        if ($input['consensus'] && ! $input['node2_online']) {
            return $this->failure('Konsensus tidak valid karena Node 2 dilaporkan offline.', [], 422);
        }

        $threshold = config('iot.earthquake_threshold_g');
        $maxG = $input['node2_online']
            ? max((float) $input['node1_pure_g'], (float) $input['node2_pure_g'])
            : (float) $input['node1_pure_g'];
        $consensusDetected = (bool) $input['consensus'];
        $node1Triggered = (bool) $input['node1_vib'];
        $node2Triggered = (bool) $input['node2_online'] && (bool) $input['node2_vib'];
        $triggerDetected = $consensusDetected || $node1Triggered || $node2Triggered;
        $capturedAt = isset($input['timestamp']) ? Carbon::parse($input['timestamp']) : now();

        $result = DB::transaction(function () use ($input, $node1, $node2, $threshold, $maxG, $consensusDetected, $node1Triggered, $node2Triggered, $triggerDetected, $capturedAt) {
            $node1Online = true;
            $node2Online = (bool) $input['node2_online'];

            foreach ([[$node1, $node1Online], [$node2, $node2Online]] as [$device, $online]) {
                if (! $online) {
                    continue;
                }

                $device->forceFill([
                    'status' => 'online',
                    'heartbeat_terakhir' => now(),
                ])->save();

                foreach (['sw420', 'akselerometer'] as $sensorType) {
                    Sensor::updateOrCreate(
                        [
                            'id_perangkat' => $device->getKey(),
                            'jenis_sensor' => $sensorType,
                        ],
                        [
                            'status' => 'normal',
                            'status_diperbarui_pada' => now(),
                            'keterangan_error' => null,
                        ],
                    );
                }
            }

            $readings = [];
            foreach ([
                [$node1, 'node1_pure_g', 'node1_mag', 'node1_vib'],
                [$node2, 'node2_pure_g', 'node2_mag', 'node2_vib'],
            ] as [$device, $gField, $magnitudeField, $vibrationField]) {
                $g = (float) $input[$gField];
                $category = $g >= $threshold
                    ? 'bahaya'
                    : ($g >= $threshold * config('iot.warning_threshold_ratio') ? 'waspada' : 'normal');

                $readings[] = LevelGetaran::create([
                    'id_perangkat' => $device->getKey(),
                    'akselerasi_x' => null,
                    'akselerasi_y' => null,
                    'akselerasi_z' => null,
                    'nilai_getaran_g' => $g,
                    'nilai_magnitudo' => $input[$magnitudeField],
                    'status_sw420' => $input[$vibrationField],
                    'kategori_level' => $category,
                    'dibuat_pada' => $capturedAt,
                ]);
            }

            $activeDetection = Deteksi::query()
                ->where(function ($query) use ($node1, $node2): void {
                    $query->whereIn('id_perangkat_a', [$node1->getKey(), $node2->getKey()])
                        ->orWhereIn('id_perangkat_b', [$node1->getKey(), $node2->getKey()]);
                })
                ->whereIn('jenis', ['gempa', 'gangguan_lokal'])
                ->whereNull('berakhir_pada')
                ->latest('id_deteksi')
                ->first();

            $detection = null;
            if ($triggerDetected) {
                $triggeredNode = $node1Triggered ? $node1 : $node2;

                if ($activeDetection) {
                    $peakG = max((float) ($activeDetection->puncak_getaran_g ?? 0), $maxG);
                    $promotedToEarthquake = $consensusDetected && $activeDetection->jenis === 'gangguan_lokal';
                    $activeDetection->forceFill([
                        'id_perangkat_a' => $consensusDetected ? $node1->getKey() : $activeDetection->id_perangkat_a,
                        'id_perangkat_b' => $consensusDetected ? $node2->getKey() : $activeDetection->id_perangkat_b,
                        'waktu_node_b' => $consensusDetected ? $capturedAt : $activeDetection->waktu_node_b,
                        'status_konsensus' => $activeDetection->status_konsensus || $consensusDetected,
                        'jenis' => $consensusDetected || $activeDetection->jenis === 'gempa' ? 'gempa' : 'gangguan_lokal',
                        'puncak_getaran_g' => $peakG,
                        'tingkat_keparahan' => $peakG >= $threshold * 2 ? 'kuat' : ($peakG >= $threshold ? 'sedang' : 'ringan'),
                        'deskripsi' => $promotedToEarthquake
                            ? 'Konsensus getaran terdeteksi oleh ESP32 Master.'
                            : $activeDetection->deskripsi,
                    ])->save();
                    $detection = $activeDetection;

                    if ($promotedToEarthquake) {
                        LogAlarm::create([
                            'id_deteksi' => $detection->getKey(),
                            'waktu_alarm' => now(),
                            'jenis_alarm' => 'sirine',
                            'status_alarm' => 'menyala',
                            'catatan' => 'Peringatan gempa dibuat otomatis dari telemetri ESP32.',
                        ]);
                    }
                } else {
                    $jenisDeteksi = $consensusDetected ? 'gempa' : 'gangguan_lokal';
                    $detection = Deteksi::create([
                        'id_perangkat_a' => $consensusDetected ? $node1->getKey() : $triggeredNode->getKey(),
                        'id_perangkat_b' => $consensusDetected ? $node2->getKey() : null,
                        'waktu_node_a' => $capturedAt,
                        'waktu_node_b' => $consensusDetected ? $capturedAt : null,
                        'jeda_waktu_node' => null,
                        'status_konsensus' => $consensusDetected,
                        'jenis' => $jenisDeteksi,
                        'tingkat_keparahan' => $maxG >= $threshold * 2 ? 'kuat' : ($maxG >= $threshold ? 'sedang' : 'ringan'),
                        'puncak_getaran_g' => $maxG,
                        'deskripsi' => $consensusDetected
                            ? 'Konsensus getaran terdeteksi oleh ESP32 Master.'
                            : 'Gangguan lokal terdeteksi di '.$triggeredNode->posisi_node.'. Belum ada konsensus antarperangkat.',
                    ]);

                    if ($consensusDetected) {
                        LogAlarm::create([
                            'id_deteksi' => $detection->getKey(),
                            'waktu_alarm' => now(),
                            'jenis_alarm' => 'sirine',
                            'status_alarm' => 'menyala',
                            'catatan' => 'Peringatan gempa dibuat otomatis dari telemetri ESP32.',
                        ]);
                    }
                }
            } elseif ($activeDetection) {
                $activeDetection->forceFill(['berakhir_pada' => now()])->save();
                LogAlarm::query()
                    ->where('id_deteksi', $activeDetection->getKey())
                    ->where('status_alarm', 'menyala')
                    ->update([
                        'status_alarm' => 'selesai',
                        'durasi_detik' => (int) abs(now()->diffInSeconds($activeDetection->created_at)),
                        'updated_at' => now(),
                    ]);
            }

            return compact('readings', 'detection', 'triggerDetected', 'node1Online', 'node2Online');
        });

        return $this->success('Telemetri ESP32 berhasil diterima.', [
            'node1_status' => $result['node1Online'] ? 'online' : 'offline',
            'node2_status' => $result['node2Online'] ? 'online' : 'offline',
            'trigger_detected' => $result['triggerDetected'],
            'readings' => $result['readings'],
            'deteksi' => $result['detection'],
        ], 201);
    }
}
