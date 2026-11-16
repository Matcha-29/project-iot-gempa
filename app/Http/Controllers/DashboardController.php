<?php

namespace App\Http\Controllers;

use App\Models\Deteksi;
use App\Models\LevelGetaran;
use App\Models\LogAlarm;
use App\Models\Perangkat;
use App\Services\DeviceHeartbeat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        DeviceHeartbeat::expireStaleDevices();
        $totalPerangkatAktif = Perangkat::where('status', 'online')->count();
        $deteksiTerbaru = Deteksi::with(['perangkatA', 'perangkatB'])
            ->whereIn('jenis', ['gempa', 'gangguan_lokal'])
            ->latest('waktu_node_a')
            ->paginate(10);
        $jumlahDeteksiTerbaru = Deteksi::where('jenis', 'gempa')->count();
        $logAlarmAktif = LogAlarm::where('status_alarm', 'menyala')->count();
        $telemetriTerakhir = LevelGetaran::with('perangkat')->latest('dibuat_pada')->latest('id_level')->first();

        return view('pages.dashboard', compact(
            'totalPerangkatAktif',
            'jumlahDeteksiTerbaru',
            'logAlarmAktif',
            'telemetriTerakhir',
            'deteksiTerbaru'
        ));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        DeviceHeartbeat::expireStaleDevices();

        $detections = Deteksi::with(['perangkatA', 'perangkatB'])
            ->whereIn('jenis', ['gempa', 'gangguan_lokal'])
            ->latest('waktu_node_a')
            ->paginate(10);
        $latestTelemetry = LevelGetaran::query()
            ->latest('dibuat_pada')
            ->latest('id_level')
            ->first();

        return response()->json([
            'summary' => [
                'active_devices' => Perangkat::where('status', 'online')->count(),
                'earthquake_detections' => Deteksi::where('jenis', 'gempa')->count(),
                'active_alarms' => LogAlarm::where('status_alarm', 'menyala')->count(),
                'latest_vibration_g' => $latestTelemetry?->nilai_getaran_g === null
                    ? null
                    : (float) $latestTelemetry->nilai_getaran_g,
            ],
            'items' => $detections->getCollection()->map(static fn (Deteksi $item): array => [
                'id' => $item->getKey(),
                'detected_at' => $item->waktu_node_a?->toIso8601String(),
                'kind' => $item->jenis === 'gangguan_lokal' ? 'Gangguan' : ucfirst($item->jenis),
                'consensus' => (bool) $item->status_konsensus,
                'status' => $item->jenis === 'gangguan_lokal'
                    ? 'Gangguan'
                    : ($item->status_konsensus ? 'Terpenuhi' : 'Tidak terpenuhi'),
                'severity' => $item->tingkat_keparahan,
                'node_a' => $item->perangkatA?->kode_perangkat,
                'node_b' => $item->perangkatB?->kode_perangkat,
            ])->values(),
            'pagination' => [
                'current_page' => $detections->currentPage(),
                'last_page' => $detections->lastPage(),
                'total' => $detections->total(),
            ],
        ]);
    }
}
