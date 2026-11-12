<?php

namespace App\Http\Controllers\Api;

use App\Models\Deteksi;
use App\Models\ResponsWarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponsWargaController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_pengguna' => ['required', 'integer', 'exists:pengguna,id_pengguna'],
            'status_respons' => ['required', 'in:AMAN,BUTUH_BANTUAN'],
            'id_deteksi' => ['sometimes', 'integer', 'exists:deteksi,id_deteksi'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'akurasi_meter' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->failure('Data respons warga tidak valid.', ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $pengguna = $request->user();

        if (
            ! $pengguna
            || (int) $input['id_pengguna'] !== (int) $pengguna->getKey()
            || $pengguna->status_akun !== 'aktif'
            || $pengguna->peran !== 'warga'
        ) {
            return $this->failure('Respons hanya dapat dikirim oleh akun warga aktif milik pengguna tersebut.', [], 403);
        }

        $deteksi = isset($input['id_deteksi'])
            ? Deteksi::query()
                ->whereKey($input['id_deteksi'])
                ->whereHas('perangkatA', fn ($query) => $query->where('id_lokasi', $pengguna->id_lokasi))
                ->first()
            : Deteksi::query()
                ->whereHas('perangkatA', fn ($query) => $query->where('id_lokasi', $pengguna->id_lokasi))
                ->where('jenis', 'gempa')
                ->whereNull('berakhir_pada')
                ->latest('id_deteksi')
                ->first();

        if (! $deteksi || $deteksi->jenis !== 'gempa' || $deteksi->berakhir_pada !== null) {
            return $this->failure('Tidak ada deteksi gempa aktif untuk menerima respons.', [], 409);
        }

        $respons = ResponsWarga::updateOrCreate(
            [
                'id_pengguna' => $pengguna->getKey(),
                'id_deteksi' => $deteksi->getKey(),
            ],
            [
                'status' => strtolower($input['status_respons']) === 'aman' ? 'aman' : 'butuh_bantuan',
                'latitude' => $input['latitude'] ?? null,
                'longitude' => $input['longitude'] ?? null,
                'akurasi_meter' => $input['akurasi_meter'] ?? null,
            ],
        );

        return $this->success('Respons warga berhasil disimpan.', [
            'respons' => $respons->fresh(),
        ], 201);
    }
}
