<?php

namespace App\Http\Controllers;

use App\Models\LogAlarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogAlarmController extends Controller
{
    public function index()
    {
        $logAlarm = LogAlarm::with(['deteksi', 'petugas'])->latest('waktu_alarm')->paginate(15);
        return view('pages.log-alarm', compact('logAlarm'));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $logAlarm = LogAlarm::with(['deteksi', 'petugas'])
            ->latest('waktu_alarm')
            ->paginate(15);

        return response()->json([
            'items' => $logAlarm->getCollection()->map(static fn (LogAlarm $item): array => [
                'id' => $item->getKey(),
                'alarm_at' => $item->waktu_alarm?->toIso8601String(),
                'type' => $item->jenis_alarm,
                'status' => $item->status_alarm,
                'duration_seconds' => $item->durasi_detik,
                'detection_id' => $item->deteksi?->getKey(),
                'officer' => $item->petugas?->nama,
                'notes' => $item->catatan,
            ])->values(),
            'pagination' => [
                'current_page' => $logAlarm->currentPage(),
                'last_page' => $logAlarm->lastPage(),
                'total' => $logAlarm->total(),
            ],
        ]);
    }
}
