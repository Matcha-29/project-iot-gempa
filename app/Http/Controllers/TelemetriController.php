<?php

namespace App\Http\Controllers;

use App\Models\LevelGetaran;
use App\Models\Perangkat;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelemetriController extends Controller
{
    /**
     * Build per-node summaries: online (15s) + triggered (10s).
     */
    private function buildNodeSummaries(): array
    {
        $now = Carbon::now();
        $onlineThreshold = $now->copy()->subSeconds(15);
        $triggerThreshold = $now->copy()->subSeconds(10);

        $nodes = [
            ['code' => 'ND-001', 'index' => 0],
            ['code' => 'ND-002', 'index' => 1],
        ];

        $summaries = [];
        foreach ($nodes as $node) {
            $latest = LevelGetaran::whereHas('perangkat', fn ($q) => $q->where('kode_perangkat', $node['code']))
                ->latest('dibuat_pada')->latest('id_level')->first();

            $isOnline = $latest && $latest->dibuat_pada && $latest->dibuat_pada->gte($onlineThreshold);

            $isTriggered = LevelGetaran::whereHas('perangkat', fn ($q) => $q->where('kode_perangkat', $node['code']))
                ->where('dibuat_pada', '>=', $triggerThreshold)
                ->where(fn ($q) => $q->where('kategori_level', '!=', 'normal')->orWhere('status_sw420', true))
                ->exists();

            $highestCategory = 'normal';
            if ($isTriggered) {
                $cats = LevelGetaran::whereHas('perangkat', fn ($q) => $q->where('kode_perangkat', $node['code']))
                    ->where('dibuat_pada', '>=', $triggerThreshold)
                    ->pluck('kategori_level')->map(fn ($c) => strtolower($c ?? 'normal'));
                if ($cats->contains('bahaya')) $highestCategory = 'bahaya';
                elseif ($cats->contains('waspada')) $highestCategory = 'waspada';
            }

            $summaries[] = [
                'code' => $node['code'],
                'index' => $node['index'],
                'vibration_g' => $latest?->nilai_getaran_g === null ? null : (float) $latest->nilai_getaran_g,
                'magnitude' => $latest?->nilai_magnitudo === null ? null : (float) $latest->nilai_magnitudo,
                'active' => $isOnline,
                'triggered' => $isTriggered,
                'highest_category' => $highestCategory,
            ];
        }

        return $summaries;
    }

    /**
     * Derive consensus status.
     */
    private function deriveConsensusStatus(array $summaries): array
    {
        $triggered = collect($summaries)->where('triggered', true);
        $triggeredCount = $triggered->count();
        $onlineCount = collect($summaries)->where('active', true)->count();
        $total = count($summaries);

        if ($triggeredCount === 0) {
            return [
                'badge_label' => 'Normal',
                'badge_class' => 'bg-emerald-100 text-emerald-800',
                'badge_key' => 'normal',
                'subtitle' => "{$onlineCount} dari {$total} node online · Semua aman",
            ];
        }

        if ($triggeredCount === 1) {
            $n = $triggered->first();
            $label = 'Node ' . ($n['index'] + 1) . ' · ' . $n['code'];
            return [
                'badge_label' => 'Gangguan',
                'badge_class' => 'bg-amber-100 text-amber-800',
                'badge_key' => 'gangguan',
                'subtitle' => "1 dari {$total} node terpicu ({$label}) · Belum konsensus",
            ];
        }

        $cats = $triggered->pluck('highest_category');
        if ($cats->contains('bahaya')) {
            return ['badge_label' => 'Bahaya', 'badge_class' => 'bg-red-100 text-red-800', 'badge_key' => 'bahaya', 'subtitle' => "{$triggeredCount} dari {$total} node terpicu · Konsensus tercapai"];
        }
        if ($cats->contains('waspada')) {
            return ['badge_label' => 'Waspada', 'badge_class' => 'bg-amber-100 text-amber-800', 'badge_key' => 'waspada', 'subtitle' => "{$triggeredCount} dari {$total} node terpicu · Konsensus tercapai"];
        }
        return ['badge_label' => 'Gangguan', 'badge_class' => 'bg-amber-100 text-amber-800', 'badge_key' => 'gangguan', 'subtitle' => "{$triggeredCount} dari {$total} node terpicu · Konsensus tercapai"];
    }

    /**
     * Chart data: last 25 per node from level_getaran directly.
     */
    private function buildChartData(): array
    {
        $tz = config('app.display_timezone', 'Asia/Jakarta');
        $chartData = [];
        foreach (['ND-001', 'ND-002'] as $kode) {
            $rows = LevelGetaran::whereHas('perangkat', fn ($q) => $q->where('kode_perangkat', $kode))
                ->latest('dibuat_pada')->latest('id_level')
                ->limit(25)->get()->reverse()->values();
            $chartData[$kode] = $rows->map(fn ($r) => [
                'time' => $r->dibuat_pada?->timezone($tz)->format('H:i:s'),
                'vibration_g' => $r->nilai_getaran_g === null ? null : (float) $r->nilai_getaran_g,
            ]);
        }
        return $chartData;
    }

    public function index(Request $request)
    {
        $tz = config('app.display_timezone', 'Asia/Jakarta');
        $nodeSummaries = $this->buildNodeSummaries();
        $consensusStatus = $this->deriveConsensusStatus($nodeSummaries);
        $chartData = $this->buildChartData();

        // 10 baris per halaman diurutkan dari waktu_slot terbaru
        $telemetri = DB::table('view_telemetri_gabungan')
            ->orderByDesc('waktu_slot')
            ->paginate(10);

        $telemetri->getCollection()->transform(function ($row) use ($tz) {
            $waktu = Carbon::parse($row->waktu_slot, 'UTC')->timezone($tz);
            $row->waktu_display = $waktu->format('d M Y H:i:s');
            $row->getaran_n1 = $row->getaran_n1 !== null ? (float) $row->getaran_n1 : null;
            $row->magnitudo_n1 = $row->magnitudo_n1 !== null ? (float) $row->magnitudo_n1 : null;
            $row->sw420_n1 = (bool) $row->sw420_n1;
            $row->getaran_n2 = $row->getaran_n2 !== null ? (float) $row->getaran_n2 : null;
            $row->magnitudo_n2 = $row->magnitudo_n2 !== null ? (float) $row->magnitudo_n2 : null;
            $row->sw420_n2 = (bool) $row->sw420_n2;
            return $row;
        });

        return view('pages.telemetri', compact(
            'nodeSummaries', 'consensusStatus', 'chartData', 'telemetri'
        ));
    }

    public function latestData(Request $request): JsonResponse
    {
        $tz = config('app.display_timezone', 'Asia/Jakarta');
        $nodeSummaries = $this->buildNodeSummaries();
        $consensusStatus = $this->deriveConsensusStatus($nodeSummaries);
        $chartData = $this->buildChartData();

        // Mengembalikan 10 data terbaru untuk polling
        $firstPageRows = DB::table('view_telemetri_gabungan')
            ->orderByDesc('waktu_slot')
            ->limit(10)
            ->get()
            ->map(function ($row) use ($tz) {
                $waktu = Carbon::parse($row->waktu_slot, 'UTC')->timezone($tz);
                return [
                    'waktu_slot' => $waktu->toIso8601String(),
                    'waktu_display' => $waktu->format('d M Y H:i:s'),
                    'getaran_n1' => $row->getaran_n1 !== null ? (float) $row->getaran_n1 : null,
                    'magnitudo_n1' => $row->magnitudo_n1 !== null ? (float) $row->magnitudo_n1 : null,
                    'sw420_n1' => (bool) $row->sw420_n1,
                    'kategori_n1' => $row->kategori_n1,
                    'getaran_n2' => $row->getaran_n2 !== null ? (float) $row->getaran_n2 : null,
                    'magnitudo_n2' => $row->magnitudo_n2 !== null ? (float) $row->magnitudo_n2 : null,
                    'sw420_n2' => (bool) $row->sw420_n2,
                    'kategori_n2' => $row->kategori_n2,
                    'kategori_gabungan' => $row->kategori_gabungan,
                ];
            })->values()->toArray();

        return response()->json([
            'items' => $firstPageRows,
            'node_summaries' => $nodeSummaries,
            'consensus_status' => $consensusStatus,
            'chart_data' => $chartData,
        ]);
    }
}
