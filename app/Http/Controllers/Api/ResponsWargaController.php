<?php

namespace App\Http\Controllers\Api;

use App\Models\Deteksi;
use App\Models\Pengguna;
use App\Models\ResponsWarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponsWargaController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_pengguna' => ['nullable', 'integer', 'exists:pengguna,id_pengguna'],
            'status_respons' => ['required_without:status', 'nullable', 'string'],
            'status' => ['required_without:status_respons', 'nullable', 'string'],
            'id_deteksi' => ['sometimes', 'nullable', 'integer', 'exists:deteksi,id_deteksi'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'akurasi_meter' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first(), ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $statusRaw = strtoupper(trim((string) ($input['status_respons'] ?? $input['status'] ?? 'AMAN')));
        $statusDb = str_contains($statusRaw, 'BANTUAN') ? 'butuh_bantuan' : 'aman';

        $pengguna = $request->user('sanctum') ?? auth('sanctum')->user() ?? $request->user();
        if (! $pengguna && ! empty($input['id_pengguna'])) {
            $pengguna = Pengguna::find($input['id_pengguna']);
        }

        if (! $pengguna || $pengguna->status_akun !== 'aktif') {
            return $this->failure('Respons hanya dapat dikirim oleh akun warga aktif.', [], 403);
        }

        $deteksi = isset($input['id_deteksi'])
            ? Deteksi::query()->whereKey($input['id_deteksi'])->first()
            : Deteksi::query()
                ->whereHas('perangkatA', fn ($query) => $query->where('id_lokasi', $pengguna->id_lokasi))
                ->latest('id_deteksi')
                ->first()
                ?? Deteksi::query()->latest('id_deteksi')->first();

        if (! $deteksi) {
            $deteksi = Deteksi::create([
                'waktu_node_a' => now(),
                'jenis' => 'gempa',
                'status_konsensus' => true,
                'tingkat_keparahan' => 'sedang',
                'puncak_getaran_g' => 0.0,
                'magnitudo' => 0.0,
                'deskripsi' => 'Insiden pemantauan gempa',
            ]);
        }

        $respons = ResponsWarga::updateOrCreate(
            [
                'id_pengguna' => $pengguna->getKey(),
                'id_deteksi' => $deteksi->getKey(),
            ],
            [
                'status' => $statusDb,
                'latitude' => $input['latitude'] ?? null,
                'longitude' => $input['longitude'] ?? null,
                'akurasi_meter' => $input['akurasi_meter'] ?? null,
            ],
        );

        $waktuLapor = $respons->updated_at ?? $respons->created_at ?? now();

        return response()->json([
            'success' => true,
            'message' => 'Respons warga berhasil disimpan: ' . $statusRaw,
            'data' => [
                'respons' => $respons->fresh()->load('pengguna'),
                'status' => $statusRaw,
                'status_respons' => $statusRaw,
                'nama' => $pengguna->nama,
                'nama_warga' => $pengguna->nama,
                'nomor_rumah' => $pengguna->nomor_rumah,
                'no_telepon' => $pengguna->no_telepon,
                'waktu_lapor' => $waktuLapor->toIso8601String(),
            ],
        ], 201);
    }
}
