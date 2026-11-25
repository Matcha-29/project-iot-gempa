<?php

namespace App\Http\Controllers;

use App\Models\Deteksi;
use App\Models\Lokasi;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeteksiController extends Controller
{
    /**
     * Serialize a Deteksi row for JSON / blade.
     */
    private function serializeDeteksi(Deteksi $item): array
    {
        $lokasiName = optional(optional($item->perangkatA)->lokasi)->nama_lokasi
            ?? optional(Lokasi::first())->nama_lokasi
            ?? '-';

        // Node label
        $nodeLabel = optional($item->perangkatA)->kode_perangkat ?? '-';
        if ($item->perangkatB) {
            $nodeLabel .= ' + ' . $item->perangkatB->kode_perangkat;
        }

        // Jeda formatted
        $jedaFormatted = null;
        if ($item->jeda_waktu_node !== null) {
            $jedaFormatted = number_format((float) $item->jeda_waktu_node, 1, ',', '.') . ' dtk';
        }

        // Konsensus label
        if ($item->jenis === 'gangguan_lokal') {
            $konsensusLabel = 'Tidak terpenuhi';
            $konsensusKey = 'tidak';
        } elseif ($item->jenis === 'uji_coba') {
            $konsensusLabel = 'Terpenuhi';
            $konsensusKey = 'terpenuhi';
        } else {
            $konsensusLabel = $item->status_konsensus ? 'Terpenuhi' : 'Tidak terpenuhi';
            $konsensusKey = $item->status_konsensus ? 'terpenuhi' : 'tidak';
        }

        // Keparahan label
        $keparahanMap = [
            'kuat' => ['label' => 'Kuat', 'key' => 'kuat'],
            'sedang' => ['label' => 'Sedang', 'key' => 'sedang'],
            'ringan' => ['label' => 'Ringan', 'key' => 'ringan'],
        ];
        $keparahan = $keparahanMap[strtolower($item->tingkat_keparahan ?? '')] ?? null;

        // Jenis label for display
        $jenisLabel = match ($item->jenis) {
            'gempa' => 'Gempa',
            'gangguan_lokal' => 'Gangguan lokal',
            'uji_coba' => 'Uji coba',
            default => ucfirst($item->jenis),
        };

        // Respons warga summary
        $responsWarga = null;
        if ($item->relationLoaded('responsWarga') && $item->responsWarga->count() > 0) {
            $aman = $item->responsWarga->where('status', 'aman')->count();
            $bantuan = $item->responsWarga->where('status', 'butuh_bantuan')->count();
            $belum = $item->responsWarga->whereNull('status')->count();
            $responsWarga = [
                'aman' => $aman,
                'butuh_bantuan' => $bantuan,
                'belum' => $belum,
                'total' => $item->responsWarga->count(),
                'text' => "{$aman} aman · {$bantuan} bantuan · {$belum} belum",
            ];
        }

        // Notifikasi count
        $notifCount = $item->relationLoaded('notifikasi') ? $item->notifikasi->count() : 0;

        return [
            'id' => $item->getKey(),
            'detected_at' => $item->waktu_node_a?->toIso8601String(),
            'waktu_node_a' => $item->waktu_node_a?->toIso8601String(),
            'waktu_node_b' => $item->waktu_node_b?->toIso8601String(),
            'jenis' => $item->jenis,
            'jenis_label' => $jenisLabel,
            'lokasi' => $lokasiName,
            'node_label' => $nodeLabel,
            'node_a' => optional($item->perangkatA)->kode_perangkat,
            'node_b' => optional($item->perangkatB)->kode_perangkat,
            'jeda_raw' => $item->jeda_waktu_node,
            'jeda_formatted' => $jedaFormatted,
            'konsensus_label' => $konsensusLabel,
            'konsensus_key' => $konsensusKey,
            'keparahan_label' => $keparahan['label'] ?? '-',
            'keparahan_key' => $keparahan['key'] ?? '',
            'puncak_getaran_g' => $item->puncak_getaran_g !== null ? (float) $item->puncak_getaran_g : null,
            'deskripsi' => $item->deskripsi,
            'respons_warga' => $responsWarga,
            'notifikasi_count' => $notifCount,
        ];
    }

    /**
     * Build detail data for a single deteksi event (for the detail panel).
     */
    private function buildDeteksiDetail(?Deteksi $deteksi): ?array
    {
        if (!$deteksi) {
            return null;
        }

        $deteksi->loadMissing(['perangkatA', 'perangkatB', 'logAlarm', 'notifikasi', 'responsWarga.pengguna']);

        $lokasi = optional(optional($deteksi->perangkatA)->lokasi)->nama_lokasi
            ?? optional(Lokasi::first())->nama_lokasi ?? '-';

        // Build timeline steps
        $timeline = [];

        // Step 1: Node A terpicu
        $nodeACode = optional($deteksi->perangkatA)->kode_perangkat ?? 'Node A';
        $timeline[] = [
            'color' => 'blue',
            'title' => "{$nodeACode} terpicu",
            'detail' => $deteksi->waktu_node_a
                ? $deteksi->waktu_node_a->timezone(config('app.display_timezone'))->format('H:i:s.v')
                  . ($deteksi->puncak_getaran_g !== null ? ' · ' . number_format((float) $deteksi->puncak_getaran_g, 2, ',', '.') . ' g' : '')
                  . ' · SW420 aktif'
                : '-',
        ];

        // Step 2: Node B terpicu (if exists)
        if ($deteksi->perangkatB) {
            $nodeBCode = $deteksi->perangkatB->kode_perangkat;
            $nodeBDetail = $deteksi->waktu_node_b
                ? $deteksi->waktu_node_b->timezone(config('app.display_timezone'))->format('H:i:s.v')
                  . ($deteksi->puncak_getaran_g !== null ? ' · ' . number_format((float) $deteksi->puncak_getaran_g, 2, ',', '.') . ' g' : '')
                  . ($deteksi->jeda_waktu_node !== null ? ' · selisih ' . number_format((float) $deteksi->jeda_waktu_node, 1, ',', '.') . ' dtk' : '')
                : '-';
            $timeline[] = [
                'color' => 'amber',
                'title' => "{$nodeBCode} terpicu",
                'detail' => $nodeBDetail,
            ];
        }

        // Step 3: Konsensus
        if ($deteksi->status_konsensus) {
            $jedaText = $deteksi->jeda_waktu_node !== null
                ? 'Δt ' . number_format((float) $deteksi->jeda_waktu_node, 1, ',', '.') . ' dtk < 1,5 dtk'
                : '';
            $timeline[] = [
                'color' => 'green',
                'title' => 'Konsensus terpenuhi',
                'detail' => $jedaText . ($jedaText ? ' · ' : '') . 'dikonfirmasi (mode uji)',
            ];
        } else {
            $timeline[] = [
                'color' => 'gray',
                'title' => 'Konsensus tidak terpenuhi',
                'detail' => $deteksi->jenis === 'gangguan_lokal' ? 'Hanya 1 node terpicu' : 'Selisih waktu melebihi batas',
            ];
        }

        // Step 4: Sirine dan notifikasi
        $alarmCount = $deteksi->logAlarm->count();
        $notifCount = $deteksi->notifikasi->count();
        if ($alarmCount > 0 || $notifCount > 0) {
            $alarm = $deteksi->logAlarm->first();
            $alarmTime = $alarm?->waktu_alarm?->timezone(config('app.display_timezone'))->format('H:i:s');
            $durasi = $alarm?->durasi_detik;
            $timeline[] = [
                'color' => 'green',
                'title' => 'Sirine dan notifikasi',
                'detail' => ($alarmTime ?? '-') . ($durasi ? " · sirine {$durasi} detik" : '') . " · {$notifCount} notifikasi terkirim",
            ];
        }

        // Respons warga with details
        $responsData = [
            'aman' => 0,
            'butuh_bantuan' => 0,
            'belum_merespons' => 0,
            'total' => 0,
            'list_bantuan' => [],
        ];

        if ($deteksi->responsWarga->count() > 0) {
            $responsData['aman'] = $deteksi->responsWarga->where('status', 'aman')->count();
            $responsData['butuh_bantuan'] = $deteksi->responsWarga->where('status', 'butuh_bantuan')->count();
            $responsData['belum_merespons'] = $deteksi->responsWarga->whereNull('status')->count();
            $responsData['total'] = $deteksi->responsWarga->count();

            $responsData['list_bantuan'] = $deteksi->responsWarga
                ->where('status', 'butuh_bantuan')
                ->map(fn ($r) => [
                    'id' => $r->id_respons,
                    'nama' => optional($r->pengguna)->nama ?? '-',
                    'no_telepon' => optional($r->pengguna)->no_telepon ?? '-',
                    'waktu' => $r->created_at?->timezone(config('app.display_timezone'))->format('H:i'),
                    'akurasi_meter' => $r->akurasi_meter,
                    'latitude' => $r->latitude,
                    'longitude' => $r->longitude,
                    'ditangani' => $r->ditangani_pada !== null,
                ])->values()->toArray();
        }

        // Jenis label
        $jenisLabel = match ($deteksi->jenis) {
            'gempa' => 'Gempa',
            'gangguan_lokal' => 'Gangguan lokal',
            'uji_coba' => 'Uji coba',
            default => ucfirst($deteksi->jenis),
        };

        return [
            'id' => $deteksi->id_deteksi,
            'jenis_label' => $jenisLabel,
            'keparahan_label' => $deteksi->tingkat_keparahan ? ucfirst($deteksi->tingkat_keparahan) : '-',
            'waktu' => $deteksi->waktu_node_a?->timezone(config('app.display_timezone'))->format('H:i'),
            'waktu_full' => $deteksi->waktu_node_a?->timezone(config('app.display_timezone'))->format('d M Y H:i:s'),
            'lokasi' => $lokasi,
            'timeline' => $timeline,
            'respons_warga' => $responsData,
        ];
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter', '');

        $query = Deteksi::with(['perangkatA', 'perangkatB', 'responsWarga', 'notifikasi'])
            ->latest('waktu_node_a');

        if ($filter === 'gempa') {
            $query->where('jenis', 'gempa')->where('status_konsensus', true);
        } elseif ($filter === 'gangguan') {
            $query->where('jenis', 'gangguan_lokal');
        } elseif ($filter === 'uji_coba') {
            $query->where('jenis', 'uji_coba');
        }

        $deteksi = $query->paginate(10)->appends(['filter' => $filter]);

        // Detail for the first (latest) detection
        $latestForDetail = $deteksi->first();
        $deteksiDetail = $this->buildDeteksiDetail($latestForDetail);

        $lokasi = Lokasi::first();

        return view('pages.deteksi', compact('deteksi', 'filter', 'deteksiDetail', 'lokasi'));
    }

    public function latestData(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'filter' => ['sometimes', 'string', 'in:gempa,gangguan,uji_coba,'],
            'detail_id' => ['sometimes', 'integer'],
        ]);

        $filter = $request->query('filter', '');

        $query = Deteksi::with(['perangkatA', 'perangkatB', 'responsWarga', 'notifikasi'])
            ->latest('waktu_node_a');

        if ($filter === 'gempa') {
            $query->where('jenis', 'gempa')->where('status_konsensus', true);
        } elseif ($filter === 'gangguan') {
            $query->where('jenis', 'gangguan_lokal');
        } elseif ($filter === 'uji_coba') {
            $query->where('jenis', 'uji_coba');
        }

        $deteksi = $query->paginate(10);

        // Detail for specific or first item
        $detailId = $request->query('detail_id');
        $detailItem = $detailId
            ? Deteksi::with(['perangkatA', 'perangkatB', 'logAlarm', 'notifikasi', 'responsWarga.pengguna'])->find($detailId)
            : $deteksi->first();
        $deteksiDetail = $this->buildDeteksiDetail($detailItem);

        return response()->json([
            'items' => $deteksi->getCollection()->map(fn (Deteksi $item) => $this->serializeDeteksi($item))->values(),
            'detail' => $deteksiDetail,
            'pagination' => [
                'current_page' => $deteksi->currentPage(),
                'last_page' => $deteksi->lastPage(),
                'total' => $deteksi->total(),
            ],
        ]);
    }
}
