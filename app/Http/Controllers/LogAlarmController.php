<?php

namespace App\Http\Controllers;

use App\Models\LogAlarm;
use App\Models\Lokasi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogAlarmController extends Controller
{
    /**
     * Build summary data for the 3 top cards.
     */
    private function buildSummary(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        // Alarm bulan ini
        $alarmBulanIni = LogAlarm::where('waktu_alarm', '>=', $startOfMonth)->count();

        // Breakdown: gempa vs uji coba
        $bulanIniAll = LogAlarm::with('deteksi')
            ->where('waktu_alarm', '>=', $startOfMonth)->get();
        $gempaCount = $bulanIniAll->filter(fn ($a) => optional($a->deteksi)->jenis === 'gempa')->count();
        $nonGempaCount = $alarmBulanIni - $gempaCount;

        // Sirine terakhir
        $latest = LogAlarm::with(['deteksi'])->latest('waktu_alarm')->first();
        $sirineTerakhirTime = $latest?->waktu_alarm
            ?->timezone(config('app.display_timezone'))
            ->format('H:i');

        $sirineTerakhirSubtext = '';
        if ($latest) {
            $waktuCarbon = $latest->waktu_alarm?->timezone(config('app.display_timezone'));
            $prefix = $waktuCarbon?->isToday() ? 'Hari ini' : ($waktuCarbon?->isYesterday() ? 'Kemarin' : ($waktuCarbon?->format('d M') ?? ''));
            $detJenis = optional($latest->deteksi)->jenis ?? 'sirine';
            $jenisLabel = $detJenis === 'gempa' ? 'gempa' : 'uji coba';
            $durasi = $latest->durasi_detik ? $latest->durasi_detik . ' detik' : '';
            $sirineTerakhirSubtext = trim("{$prefix} · {$jenisLabel}" . ($durasi ? " · {$durasi}" : ''));
        }

        // Status sirine - based on latest alarm
        $statusSirine = 'Siap';
        $statusSirineSubtext = 'Uji terakhir berhasil';
        $statusSirineKey = 'siap';
        if ($latest) {
            if ($latest->status_alarm === 'menyala') {
                $statusSirine = 'Aktif';
                $statusSirineSubtext = 'Sirine sedang berbunyi';
                $statusSirineKey = 'aktif';
            } elseif ($latest->status_alarm === 'gagal') {
                $statusSirine = 'Gagal';
                $statusSirineSubtext = 'Periksa relay sirine';
                $statusSirineKey = 'gagal';
            } elseif ($latest->status_alarm === 'dibatalkan') {
                $statusSirine = 'Siap';
                $statusSirineSubtext = 'Alarm terakhir dibatalkan';
                $statusSirineKey = 'siap';
            } else {
                // selesai
                $statusSirine = 'Siap';
                $statusSirineSubtext = 'Uji terakhir berhasil';
                $statusSirineKey = 'siap';
            }
        }

        return [
            'alarm_bulan_ini' => $alarmBulanIni,
            'gempa_count' => $gempaCount,
            'non_gempa_count' => $nonGempaCount,
            'sirine_terakhir_time' => $sirineTerakhirTime ?? '--:--',
            'sirine_terakhir_subtext' => $sirineTerakhirSubtext,
            'status_sirine' => $statusSirine,
            'status_sirine_subtext' => $statusSirineSubtext,
            'status_sirine_key' => $statusSirineKey,
        ];
    }

    /**
     * Serialize a log alarm row.
     */
    private function serializeAlarm(LogAlarm $item): array
    {
        $deteksi = $item->deteksi;
        $detJenis = optional($deteksi)->jenis;
        $detKep = optional($deteksi)->tingkat_keparahan;
        $lokasi = optional(optional(optional($deteksi)->perangkatA)->lokasi)->nama_lokasi
            ?? optional(Lokasi::first())->nama_lokasi ?? '-';

        // Deteksi terkait label
        $detLabel = '-';
        if ($deteksi) {
            $jenisLabel = match ($detJenis) {
                'gempa' => 'Gempa',
                'gangguan_lokal' => 'Gangguan lokal',
                'uji_coba' => 'Uji coba',
                default => ucfirst($detJenis ?? ''),
            };
            $kepLabel = $detKep ? ucfirst($detKep) : '';
            $detLabel = $kepLabel ? "{$kepLabel} · {$lokasi}" : "{$jenisLabel} · {$lokasi}";
        }

        return [
            'id' => $item->getKey(),
            'alarm_at' => $item->waktu_alarm?->toIso8601String(),
            'type' => $item->jenis_alarm,
            'status' => $item->status_alarm,
            'duration_seconds' => $item->durasi_detik,
            'detection_id' => $deteksi?->getKey(),
            'detection_label' => $detLabel,
            'detection_jenis' => $detJenis,
            'detection_keparahan' => $detKep,
            'officer' => $item->petugas?->nama,
            'notes' => $item->catatan,
        ];
    }

    public function index(Request $request)
    {
        $statusFilter = $request->query('status', '');

        $query = LogAlarm::with(['deteksi.perangkatA.lokasi', 'petugas'])
            ->latest('waktu_alarm');

        if ($statusFilter !== '') {
            $query->where('status_alarm', $statusFilter);
        }

        $logAlarm = $query->paginate(10)->appends(['status' => $statusFilter]);
        $summary = $this->buildSummary();
        $lokasi = Lokasi::first();

        return view('pages.log-alarm', compact('logAlarm', 'summary', 'statusFilter', 'lokasi'));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:menyala,selesai,dibatalkan,gagal,'],
        ]);

        $statusFilter = $request->query('status', '');

        $query = LogAlarm::with(['deteksi.perangkatA.lokasi', 'petugas'])
            ->latest('waktu_alarm');

        if ($statusFilter !== '') {
            $query->where('status_alarm', $statusFilter);
        }

        $logAlarm = $query->paginate(10);
        $summary = $this->buildSummary();

        return response()->json([
            'summary' => $summary,
            'items' => $logAlarm->getCollection()->map(fn (LogAlarm $item) => $this->serializeAlarm($item))->values(),
            'pagination' => [
                'current_page' => $logAlarm->currentPage(),
                'last_page' => $logAlarm->lastPage(),
                'total' => $logAlarm->total(),
            ],
        ]);
    }

    /**
     * Update catatan for a log alarm.
     */
    public function updateCatatan(Request $request, int $logAlarm): JsonResponse
    {
        $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $alarm = LogAlarm::findOrFail($logAlarm);
        $alarm->update(['catatan' => $request->input('catatan')]);

        return response()->json(['success' => true, 'catatan' => $alarm->catatan]);
    }
}
