<?php

namespace App\Http\Controllers;

use App\Models\Deteksi;
use App\Models\LevelGetaran;
use App\Models\LogAlarm;
use App\Models\Notifikasi;
use App\Models\Perangkat;
use App\Models\ResponsWarga;
use App\Services\DeviceHeartbeat;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Build per-node status summaries for the dashboard.
     */
    private function buildNodeStatus(): array
    {
        $now = Carbon::now();
        $onlineThreshold = $now->copy()->subSeconds(15);

        $perangkat = Perangkat::with(['lokasi', 'sensor'])->orderBy('kode_perangkat')->get();
        $nodes = [];

        foreach ($perangkat as $p) {
            $latest = LevelGetaran::where('id_perangkat', $p->id_perangkat)
                ->latest('dibuat_pada')->latest('id_level')->first();

            $isOnline = $latest && $latest->dibuat_pada && $latest->dibuat_pada->gte($onlineThreshold);

            $heartbeatAge = $p->heartbeat_terakhir
                ? $now->diffInSeconds($p->heartbeat_terakhir)
                : null;

            $sensors = $p->sensor->map(fn ($s) => [
                'jenis' => $s->jenis_sensor,
                'tipe' => $s->tipe_model,
                'status' => $s->status,
            ])->values()->toArray();

            $nodes[] = [
                'code' => $p->kode_perangkat,
                'posisi' => $p->posisi_node,
                'mikrokontroler' => $p->mikrokontroler,
                'online' => $isOnline,
                'heartbeat_age_seconds' => $heartbeatAge !== null ? (int) $heartbeatAge : null,
                'vibration_g' => $latest?->nilai_getaran_g === null ? null : (float) $latest->nilai_getaran_g,
                'magnitude' => $latest?->nilai_magnitudo === null ? null : (float) $latest->nilai_magnitudo,
                'sensors' => $sensors,
            ];
        }

        return $nodes;
    }

    /**
     * Derive system status from recent detections.
     */
    private function deriveSystemStatus(): array
    {
        $now = Carbon::now();
        $recentThreshold = $now->copy()->subSeconds(10);

        // Check if any node is triggered in last 10s
        $recentTriggered = LevelGetaran::where('dibuat_pada', '>=', $recentThreshold)
            ->where(fn ($q) => $q
                ->where('kategori_level', '!=', 'normal')
                ->orWhere('status_sw420', true)
            )->exists();

        // Check latest deteksi for active earthquake
        $latestDeteksi = Deteksi::whereIn('jenis', ['gempa', 'gangguan_lokal'])
            ->latest('waktu_node_a')->first();

        $activeAlarms = LogAlarm::whereIn('status_alarm', ['menyala', 'aktif'])->count();
        $isUjiCoba = (bool) cache()->get('uji_sirine_aktif');
        $isTerkendali = cache()->has('situasi_terkendali');

        if ($activeAlarms > 0 || ($latestDeteksi && $latestDeteksi->jenis === 'gempa' && $latestDeteksi->tingkat_keparahan === 'kuat' && $latestDeteksi->waktu_node_a?->gte($now->copy()->subMinutes(5)))) {
            return ['label' => 'BAHAYA', 'key' => 'danger', 'class' => 'bg-red-600 text-white'];
        }

        if ($isUjiCoba) {
            return ['label' => 'UJI COBA', 'key' => 'test', 'class' => 'bg-blue-600 text-white'];
        }

        if ($isTerkendali) {
            return ['label' => 'TERKENDALI', 'key' => 'controlled', 'class' => 'bg-emerald-700 text-white'];
        }

        if ($recentTriggered || ($latestDeteksi && $latestDeteksi->waktu_node_a?->gte($now->copy()->subMinutes(5)))) {
            return ['label' => 'WASPADA', 'key' => 'warning', 'class' => 'bg-amber-500 text-white'];
        }

        return ['label' => 'AMAN', 'key' => 'safe', 'class' => 'bg-brand-aman text-white'];
    }

    /**
     * Build chart data for the realtime vibration chart (last 60 data points per node).
     */
    private function buildChartData(): array
    {
        $perangkatList = Perangkat::orderBy('kode_perangkat')->pluck('kode_perangkat', 'id_perangkat');
        $chartData = [];

        foreach ($perangkatList as $id => $kode) {
            $rows = LevelGetaran::where('id_perangkat', $id)
                ->latest('dibuat_pada')->latest('id_level')
                ->limit(60)->get()->reverse()->values();

            $chartData[$kode] = $rows->map(fn ($r) => [
                'time' => $r->dibuat_pada?->timezone(config('app.display_timezone'))->format('H:i:s'),
                'vibration_g' => $r->nilai_getaran_g === null ? null : (float) $r->nilai_getaran_g,
            ]);
        }

        return $chartData;
    }

    /**
     * Build unified log from deteksi, log_alarm, and telemetri.
     */
    private function buildRealtimeLog(int $limit = 50, ?string $filter = null): Collection
    {
        $logs = collect();

        // Telemetri entries (non-normal or recent)
        if (!$filter || $filter === 'telemetri') {
            $telemetriLogs = LevelGetaran::with('perangkat')
                ->latest('dibuat_pada')->latest('id_level')
                ->limit($limit)->get();

            foreach ($telemetriLogs as $t) {
                $logs->push([
                    'id' => 'tel-' . $t->id_level,
                    'waktu' => $t->dibuat_pada?->toIso8601String(),
                    'waktu_sort' => $t->dibuat_pada?->timestamp ?? 0,
                    'jenis' => 'Telemetri',
                    'sumber' => optional($t->perangkat)->kode_perangkat ?? '-',
                    'detail' => sprintf(
                        'Getaran %s g · SW420 %s',
                        $t->nilai_getaran_g !== null ? number_format($t->nilai_getaran_g, 2) : '-',
                        $t->status_sw420 ? 'aktif' : 'tidak aktif'
                    ),
                    'status' => $t->kategori_level ? ucfirst($t->kategori_level) : 'Normal',
                    'status_key' => strtolower($t->kategori_level ?? 'normal'),
                ]);
            }
        }

        // Deteksi entries
        if (!$filter || $filter === 'deteksi') {
            $deteksiLogs = Deteksi::with(['perangkatA', 'perangkatB'])
                ->whereIn('jenis', ['gempa', 'gangguan_lokal'])
                ->latest('waktu_node_a')
                ->limit($limit)->get();

            foreach ($deteksiLogs as $d) {
                $nodeLabel = optional($d->perangkatA)->kode_perangkat;
                if ($d->perangkatB) {
                    $nodeLabel .= ' + ' . $d->perangkatB->kode_perangkat;
                }

                $detailParts = [];
                if ($d->jenis === 'gangguan_lokal') {
                    $triggeredNode = optional($d->perangkatA)->kode_perangkat ?? '?';
                    $detailParts[] = "$triggeredNode terpicu · konsensus tidak terpenuhi";
                } else {
                    if ($d->jeda_waktu_node !== null) {
                        $detailParts[] = sprintf('Δt %s dtk', number_format($d->jeda_waktu_node, 1, ',', '.'));
                    }
                    $detailParts[] = $d->status_konsensus ? 'konsensus terpenuhi' : 'konsensus tidak terpenuhi';
                    if ($d->puncak_getaran_g !== null) {
                        $detailParts[] = sprintf('puncak %s g', number_format((float) $d->puncak_getaran_g, 2, ',', '.'));
                    }
                }
                if ($d->deskripsi) {
                    $detailParts[] = $d->deskripsi;
                }

                $statusLabel = $d->jenis === 'gangguan_lokal' ? 'Gangguan lokal' : ($d->status_konsensus ? 'Uji coba' : 'Pulih');
                $statusKey = $d->jenis === 'gangguan_lokal' ? 'gangguan' : ($d->tingkat_keparahan === 'kuat' ? 'bahaya' : ($d->tingkat_keparahan === 'sedang' ? 'waspada' : 'normal'));

                $logs->push([
                    'id' => 'det-' . $d->id_deteksi,
                    'waktu' => $d->waktu_node_a?->toIso8601String(),
                    'waktu_sort' => $d->waktu_node_a?->timestamp ?? 0,
                    'jenis' => 'Deteksi',
                    'sumber' => $nodeLabel ?? '-',
                    'detail' => implode(' · ', $detailParts),
                    'status' => $statusLabel,
                    'status_key' => $statusKey,
                ]);
            }
        }

        // Log Alarm entries
        if (!$filter || $filter === 'log-alarm') {
            $alarmLogs = LogAlarm::with('deteksi')
                ->latest('waktu_alarm')
                ->limit($limit)->get();

            foreach ($alarmLogs as $a) {
                $detail = ucfirst($a->jenis_alarm ?? 'Sirene');
                if ($a->catatan) {
                    $detail .= ' · ' . $a->catatan;
                }
                if ($a->status_alarm === 'tidak_aktif' && $a->catatan) {
                    $detail = $a->catatan;
                }

                $statusLabel = match ($a->status_alarm) {
                    'menyala' => 'Aktif',
                    'dimatikan' => 'Selesai',
                    default => ucfirst(str_replace('_', ' ', $a->status_alarm ?? '')),
                };
                $statusKey = $a->status_alarm === 'menyala' ? 'bahaya' : ($a->status_alarm === 'dimatikan' ? 'selesai' : 'normal');

                $logs->push([
                    'id' => 'alm-' . $a->id_log,
                    'waktu' => $a->waktu_alarm?->toIso8601String(),
                    'waktu_sort' => $a->waktu_alarm?->timestamp ?? 0,
                    'jenis' => 'Log Alarm',
                    'sumber' => 'Sirine',
                    'detail' => $detail,
                    'status' => $statusLabel,
                    'status_key' => $statusKey,
                ]);
            }
        }

        return $logs->sortByDesc('waktu_sort')->take($limit)->values();
    }

    /**
     * Build warga response summary for latest gempa detection or today's reports.
     */
    private function buildResponsWarga(): array
    {
        $latestDeteksi = Deteksi::where('jenis', 'gempa')
            ->latest('id_deteksi')->first()
            ?? Deteksi::latest('id_deteksi')->first();

        $query = ResponsWarga::with('pengguna');
        if ($latestDeteksi) {
            $query->where('id_deteksi', $latestDeteksi->getKey());
        }
        $responses = $query->latest('updated_at')->get();

        if ($responses->isEmpty()) {
            $responses = ResponsWarga::with('pengguna')->whereDate('created_at', today())->latest('updated_at')->limit(50)->get();
        }

        $aman = $responses->where('status', 'aman')->count();
        $butuhBantuan = $responses->where('status', 'butuh_bantuan')->count();
        $totalPenggunaAktif = \App\Models\Pengguna::where('status_akun', 'aktif')->count();
        $belumRespons = max(0, $totalPenggunaAktif - $responses->count());

        $daftarRespons = $responses->map(fn ($r) => [
            'id' => $r->id_respons,
            'nama' => optional($r->pengguna)->nama ?? '-',
            'no_telepon' => optional($r->pengguna)->no_telepon ?? '-',
            'lokasi' => optional($r->pengguna)->nomor_rumah ?? '-',
            'nomor_rumah' => optional($r->pengguna)->nomor_rumah ?? '-',
            'status' => $r->status,
            'status_label' => $r->status === 'butuh_bantuan' ? 'BUTUH BANTUAN' : 'AMAN',
            'waktu' => $r->updated_at
                ? $r->updated_at->timezone(config('app.display_timezone'))->format('H:i:s')
                : ($r->created_at ? $r->created_at->timezone(config('app.display_timezone'))->format('H:i:s') : '-'),
            'ditangani' => $r->ditangani_pada !== null,
        ])->values()->toArray();

        $wargaBantuanList = collect($daftarRespons)->where('status', 'butuh_bantuan')->values()->toArray();

        return [
            'deteksi_id' => $latestDeteksi?->id_deteksi,
            'waktu' => $latestDeteksi?->waktu_node_a?->toIso8601String(),
            'aman' => $aman,
            'butuh_bantuan' => $butuhBantuan,
            'belum_merespons' => $belumRespons,
            'total_warga' => $responses->count(),
            'warga_butuh_bantuan' => $wargaBantuanList,
            'daftar_respons' => $daftarRespons,
        ];
    }

    public function triggerUjiSirine(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $nodeA = Perangkat::where('kode_perangkat', 'ND-001')->orWhere('posisi_node', 'like', '%Node A%')->first();
        $nodeB = Perangkat::where('kode_perangkat', 'ND-002')->orWhere('posisi_node', 'like', '%Node B%')->first();

        $deteksi = Deteksi::create([
            'id_perangkat_a' => $nodeA?->getKey(),
            'id_perangkat_b' => $nodeB?->getKey(),
            'waktu_node_a' => now(),
            'waktu_node_b' => now(),
            'jeda_waktu_node' => 0.0,
            'status_konsensus' => true,
            'jenis' => 'uji_coba',
            'tingkat_keparahan' => 'ringan',
            'puncak_getaran_g' => 0.05,
            'magnitudo' => 0.0,
            'deskripsi' => 'Uji coba sirine dan sistem peringatan oleh petugas.',
        ]);

        $logAlarm = LogAlarm::create([
            'id_deteksi' => $deteksi->getKey(),
            'waktu_alarm' => now(),
            'jenis_alarm' => 'sirine',
            'status_alarm' => 'uji_coba',
            'pemicu' => 'Petugas RT (Manual)',
            'catatan' => 'Uji coba sirine peringatan berkala.',
        ]);

        cache()->put('uji_sirine_aktif', true, 60);
        cache()->put('uji_sirine_mulai', now()->toIso8601String(), 60);
        cache()->put('perintah_buzzer', true, 60);
        cache()->put('uji_sirine_deteksi_id', $deteksi->getKey(), 60);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Mode uji sirine diaktifkan selama 60 detik.',
                'deteksi' => $deteksi,
                'log_alarm' => $logAlarm,
            ]);
        }

        return back()->with('success', 'Mode uji sirine diaktifkan selama 60 detik.');
    }

    public function stopUjiSirine(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $deteksiId = cache()->get('uji_sirine_deteksi_id');
        cache()->forget('uji_sirine_aktif');
        cache()->forget('uji_sirine_mulai');
        cache()->forget('perintah_buzzer');
        cache()->forget('uji_sirine_deteksi_id');

        if ($deteksiId) {
            Deteksi::whereKey($deteksiId)->update(['berakhir_pada' => now()]);
            LogAlarm::where('id_deteksi', $deteksiId)->update([
                'status_alarm' => 'selesai',
                'waktu_selesai' => now(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Uji sirine berhasil dihentikan.',
            ]);
        }

        return back()->with('success', 'Uji sirine berhasil dihentikan.');
    }

    public function nyatakanSituasiAman(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $activeDeteksi = Deteksi::where('jenis', 'gempa')
            ->whereNull('berakhir_pada')
            ->latest('id_deteksi')
            ->first()
            ?? Deteksi::where('jenis', 'gempa')->latest('id_deteksi')->first();

        $waktuMulai = $activeDeteksi?->waktu_node_a ?? now()->subMinutes(2);
        $waktuSelesai = now();
        $durasiDetik = (int) abs($waktuSelesai->diffInSeconds($waktuMulai));

        if ($activeDeteksi) {
            $activeDeteksi->forceFill(['berakhir_pada' => $waktuSelesai])->save();
        }

        LogAlarm::whereIn('status_alarm', ['aktif', 'menyala', 'uji_coba'])
            ->update([
                'status_alarm' => 'dimatikan',
                'waktu_selesai' => $waktuSelesai,
                'durasi_detik' => $durasiDetik,
            ]);

        $ringkasan = [
            'waktu_mulai' => $waktuMulai->toIso8601String(),
            'waktu_selesai' => $waktuSelesai->toIso8601String(),
            'waktu_label' => $waktuMulai->timezone(config('app.display_timezone'))->format('H:i') . ' - ' . $waktuSelesai->timezone(config('app.display_timezone'))->format('H:i'),
            'durasi_detik' => $durasiDetik,
            'puncak_getaran_g' => (float) ($activeDeteksi?->puncak_getaran_g ?? 0.0),
            'magnitudo' => (float) ($activeDeteksi?->magnitudo ?? 0.0),
            'tingkat_keparahan' => ucfirst($activeDeteksi?->tingkat_keparahan ?? 'Sedang'),
            'status' => 'terkendali',
        ];

        cache()->put('situasi_terkendali', $ringkasan, 120);
        cache()->forget('uji_sirine_aktif');
        cache()->forget('perintah_buzzer');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Situasi dinyatakan aman dan terkendali.',
                'ringkasan' => $ringkasan,
            ]);
        }

        return back()->with('success', 'Situasi dinyatakan aman dan terkendali.');
    }

    public function index(Request $request): View
    {
        DeviceHeartbeat::expireStaleDevices();

        $nodeStatus = $this->buildNodeStatus();
        $systemStatus = $this->deriveSystemStatus();
        $chartData = $this->buildChartData();

        // Paginate 10 data log per halaman
        $allLogs = $this->buildRealtimeLog(100);
        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $items = $allLogs->slice(($page - 1) * $perPage, $perPage)->values();
        $realtimeLog = new LengthAwarePaginator($items, $allLogs->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $responsWarga = $this->buildResponsWarga();

        $onlineCount = collect($nodeStatus)->where('online', true)->count();
        $totalNodes = count($nodeStatus);

        $peakToday = LevelGetaran::whereDate('dibuat_pada', today())->max('nilai_getaran_g');
        $peakTodayTime = null;
        if ($peakToday !== null) {
            $peakRow = LevelGetaran::whereDate('dibuat_pada', today())
                ->where('nilai_getaran_g', $peakToday)
                ->latest('dibuat_pada')->first();
            $peakTodayTime = $peakRow?->dibuat_pada?->timezone(config('app.display_timezone'))->format('H:i');
        }

        $totalNotifikasi = Notifikasi::count();
        $notifikasiHariIni = Notifikasi::whereDate('dikirim_pada', today())->count();
        $totalDeteksiGempa = Deteksi::where('jenis', 'gempa')->count();

        $lokasi = \App\Models\Lokasi::first();
        $ujiSirineAktif = (bool) cache()->get('uji_sirine_aktif', false);
        $situasiTerkendali = cache()->get('situasi_terkendali');

        return view('pages.dashboard', compact(
            'nodeStatus', 'systemStatus', 'chartData', 'realtimeLog',
            'responsWarga', 'onlineCount', 'totalNodes',
            'peakToday', 'peakTodayTime', 'totalNotifikasi',
            'notifikasiHariIni', 'totalDeteksiGempa', 'lokasi',
            'ujiSirineAktif', 'situasiTerkendali'
        ));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'filter' => ['sometimes', 'string', 'in:telemetri,deteksi,log-alarm'],
        ]);

        DeviceHeartbeat::expireStaleDevices();

        $filter = $request->query('filter');
        $nodeStatus = $this->buildNodeStatus();
        $systemStatus = $this->deriveSystemStatus();
        $chartData = $this->buildChartData();

        // Mengembalikan 10 data terbaru untuk polling live halaman 1
        $realtimeLog = $this->buildRealtimeLog(10, $filter)->toArray();
        $responsWarga = $this->buildResponsWarga();

        $onlineCount = collect($nodeStatus)->where('online', true)->count();
        $totalNodes = count($nodeStatus);

        $peakToday = LevelGetaran::whereDate('dibuat_pada', today())->max('nilai_getaran_g');
        $peakTodayTime = null;
        if ($peakToday !== null) {
            $peakRow = LevelGetaran::whereDate('dibuat_pada', today())
                ->where('nilai_getaran_g', $peakToday)
                ->latest('dibuat_pada')->first();
            $peakTodayTime = $peakRow?->dibuat_pada?->timezone(config('app.display_timezone'))->format('H:i');
        }

        $totalNotifikasi = Notifikasi::count();

        return response()->json([
            'node_status' => $nodeStatus,
            'system_status' => $systemStatus,
            'uji_sirine_aktif' => (bool) cache()->get('uji_sirine_aktif', false),
            'situasi_terkendali' => cache()->get('situasi_terkendali'),
            'summary' => [
                'online_count' => $onlineCount,
                'total_nodes' => $totalNodes,
                'peak_today' => $peakToday !== null ? (float) $peakToday : null,
                'peak_today_time' => $peakTodayTime,
                'total_notifikasi' => $totalNotifikasi,
                'total_deteksi_gempa' => Deteksi::where('jenis', 'gempa')->count(),
            ],
            'chart_data' => $chartData,
            'realtime_log' => $realtimeLog,
            'respons_warga' => $responsWarga,
        ]);
    }
}
