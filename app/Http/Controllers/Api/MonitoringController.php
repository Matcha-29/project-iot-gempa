<?php

namespace App\Http\Controllers\Api;

use App\Models\Deteksi;
use App\Models\LevelGetaran;
use App\Models\LogAlarm;
use App\Models\Lokasi;
use App\Models\Perangkat;
use App\Services\DeviceHeartbeat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringController extends ApiController
{
    public function statusWilayah(): JsonResponse
    {
        DeviceHeartbeat::expireStaleDevices();

        $node1 = Perangkat::query()
            ->where('kode_perangkat', 'ND-001')
            ->orWhere('posisi_node', 'like', '%Node A%')
            ->with('lokasi')
            ->first();

        $node2 = Perangkat::query()
            ->where('kode_perangkat', 'ND-002')
            ->orWhere('posisi_node', 'like', '%Node B%')
            ->with('lokasi')
            ->first();

        $latestLog1 = $node1
            ? LevelGetaran::query()->where('id_perangkat', $node1->getKey())->orderByDesc('id_level')->first()
            : null;

        $latestLog2 = $node2
            ? LevelGetaran::query()->where('id_perangkat', $node2->getKey())->orderByDesc('id_level')->first()
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
            : Deteksi::query()->latest('id_deteksi')->first();

        $node1Online = $node1 && strtolower((string) $node1->status) === 'online';
        $node2Online = $node2 && strtolower((string) $node2->status) === 'online';

        $node1Status = $node1Online ? 'online' : 'offline';
        $node2Status = $node2Online ? 'online' : 'offline';

        $maxMagnitude = collect([$latestLog1?->nilai_magnitudo, $latestLog2?->nilai_magnitudo, $latestDetection?->magnitudo])
            ->filter(fn ($v) => $v !== null && is_numeric($v))
            ->max();

        $maxG = collect([$latestLog1?->nilai_getaran_g, $latestLog2?->nilai_getaran_g, $latestDetection?->puncak_getaran_g])
            ->filter(fn ($v) => $v !== null && is_numeric($v))
            ->max();

        $recentVibA = (bool) ($latestLog1?->status_sw420 ?? false);
        $recentVibB = (bool) ($latestLog2?->status_sw420 ?? false);

        $latestAlarm = LogAlarm::query()
            ->whereIn('status_alarm', ['aktif', 'menyala'])
            ->latest('id_log')
            ->first();

        $alarmAktif = ($latestAlarm !== null) || (bool) ($latestDetection?->status_alarm);

        // Uji coba sirine
        $ujiCobaAktif = (bool) cache()->get('uji_sirine_aktif', false)
            || LogAlarm::query()->where('status_alarm', 'uji_coba')->whereNull('waktu_selesai')->where('created_at', '>=', now()->subSeconds(60))->exists();
        $waktuMulaiUji = cache()->get('uji_sirine_mulai') ?? now()->toIso8601String();

        // Situasi terkendali (pasca-gempa)
        $situasiTerkendali = cache()->get('situasi_terkendali');

        // Heartbeat terakhir dari kedua node
        $terakhirDiperbarui = collect([$node1?->heartbeat_terakhir, $node2?->heartbeat_terakhir])
            ->filter()
            ->max()?->toIso8601String() ?? now()->toIso8601String();

        // Konsensus 2 node terpenuhi atau alarm aktif -> "bahaya"
        // 1 node saja mendeteksi getaran lokal -> "gangguan"
        // Normal -> "aman"
        $isConsensusActive = ($latestDetection && ($latestDetection->status_konsensus || $latestDetection->jenis === 'gempa') && $latestDetection->berakhir_pada === null)
            || ($recentVibA && $recentVibB);

        $isSingleNodeActive = ($latestDetection && ($latestDetection->jenis === 'gangguan_lokal' || $latestDetection->jenis === 'gangguan') && $latestDetection->berakhir_pada === null)
            || ($recentVibA !== $recentVibB);

        if ($alarmAktif || $isConsensusActive) {
            $statusWilayah = 'bahaya';
        } elseif ($ujiCobaAktif) {
            $statusWilayah = 'uji_coba';
        } elseif ($situasiTerkendali !== null) {
            $statusWilayah = 'terkendali';
        } elseif (!$node1Online && !$node2Online) {
            $statusWilayah = 'offline';
        } elseif ($isSingleNodeActive) {
            $statusWilayah = 'gangguan';
        } else {
            $statusWilayah = 'aman';
        }

        $lokasi = $node1?->lokasi ?? $node2?->lokasi ?? Lokasi::first();
        $namaLokasi = $lokasi?->nama_lokasi ?? 'Pos Monitoring Polije';
        $noTeleponPetugas = $lokasi?->nomor_petugas ?? '08123456789';

        $responseData = [
            'status_wilayah' => $statusWilayah,
            'status' => strtoupper(str_replace('_', ' ', $statusWilayah)),
            'nama_lokasi' => $namaLokasi,
            'no_telepon_petugas' => $noTeleponPetugas,
            'node1_status' => $node1Status,
            'node2_status' => $node2Status,
            'node_a' => $node1Status,
            'node_b' => $node2Status,
            'getaran' => (float) ($maxG ?? 0.0),
            'nilai_getaran_g' => (float) ($maxG ?? 0.0),
            'max_g' => (float) ($maxG ?? 0.0),
            'magnitudo' => (float) ($maxMagnitude ?? 0.0),
            'max_magnitude' => (float) ($maxMagnitude ?? 0.0),
            'alarm_aktif' => (bool) $alarmAktif,
            'uji_coba_aktif' => (bool) $ujiCobaAktif,
            'waktu_mulai_uji' => $ujiCobaAktif ? $waktuMulaiUji : null,
            'terakhir_diperbarui' => $terakhirDiperbarui,
            'ringkasan_kejadian' => $situasiTerkendali,
            'log_alarm' => $latestAlarm,
            'deteksi_terakhir' => $latestDetection,
            'lokasi' => $lokasi,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Status wilayah berhasil diambil.',
            ...$responseData,
            'data' => $responseData,
        ], 200);
    }

    public function riwayatGempa(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 20;
        }

        $riwayat = Deteksi::query()
            ->with(['perangkatA:id_perangkat,kode_perangkat,posisi_node', 'perangkatB:id_perangkat,kode_perangkat,posisi_node'])
            ->orderByDesc('id_deteksi')
            ->paginate($perPage);

        $items = collect($riwayat->items())->map(function (Deteksi $deteksi) {
            $hasA = (bool) $deteksi->id_perangkat_a;
            $hasB = (bool) $deteksi->id_perangkat_b;
            $isConsensus = (bool) $deteksi->status_konsensus;

            $nodeTerpicu = 'Node A & B';
            if ($isConsensus || ($hasA && $hasB)) {
                $nodeTerpicu = 'Node A & B';
            } elseif ($hasA && ! $hasB) {
                $posisi = $deteksi->perangkatA?->posisi_node;
                $nodeTerpicu = ($posisi && str_contains($posisi, 'Node B')) ? 'Node B' : 'Node A';
            } elseif (! $hasA && $hasB) {
                $posisi = $deteksi->perangkatB?->posisi_node;
                $nodeTerpicu = ($posisi && str_contains($posisi, 'Node A')) ? 'Node A' : 'Node B';
            }

            $jenisLower = strtolower((string) $deteksi->jenis);
            $kategori = match ($jenisLower) {
                'gempa' => 'Gempa',
                'uji_coba' => 'Uji coba',
                'gangguan_lokal', 'gangguan' => 'Gangguan',
                'terkendali', 'pulih' => 'Pulih',
                'selesai' => 'Selesai',
                default => 'Info',
            };

            $sumberKejadian = ($isConsensus || ($hasA && $hasB))
                ? 'Konsensus 2 Node'
                : '1 Node (Gangguan Lokal)';

            $data = $deteksi->toArray();
            $data['node_terpicu'] = $nodeTerpicu;
            $data['kategori'] = $kategori;
            $data['jenis'] = $jenisLower;
            $data['sumber_kejadian'] = $sumberKejadian;
            $data['sumber'] = $sumberKejadian;

            return $data;
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat gempa berhasil diambil.',
            'items' => $items,
            'data' => $items,
            'pagination' => [
                'current_page' => $riwayat->currentPage(),
                'per_page' => $riwayat->perPage(),
                'last_page' => $riwayat->lastPage(),
                'total' => $riwayat->total(),
            ],
        ], 200);
    }
}
