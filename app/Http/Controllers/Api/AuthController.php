<?php

namespace App\Http\Controllers\Api;

use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required_without_all:email,no_telepon', 'nullable', 'string', 'max:150'],
            'email' => ['required_without_all:identifier,no_telepon', 'nullable', 'email', 'max:150'],
            'no_telepon' => ['required_without_all:identifier,email', 'nullable', 'string', 'max:20'],
            'kata_sandi' => ['required_without:password', 'nullable', 'string', 'max:255'],
            'password' => ['required_without:kata_sandi', 'nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->failure('Data login tidak valid.', ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $identifier = $input['identifier'] ?? $input['email'] ?? $input['no_telepon'];
        $pengguna = Pengguna::query()
            ->where('email', $identifier)
            ->orWhere('no_telepon', $identifier)
            ->first();

        if (
            ! $pengguna
            || $pengguna->status_akun !== 'aktif'
            || ! $pengguna->kata_sandi
            || ! Hash::check($input['kata_sandi'] ?? $input['password'], $pengguna->kata_sandi)
        ) {
            return $this->failure('Email/nomor telepon atau kata sandi tidak sesuai.', [], 401);
        }

        $token = $pengguna->createToken($input['device_name'] ?? 'flutter-app')->plainTextToken;
        $pengguna->forceFill(['terakhir_masuk_pada' => now()])->save();

        return $this->success('Login berhasil.', [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $pengguna->fresh(),
        ]);
    }

    public function aktivasi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'no_telepon' => ['required', 'string', 'max:20'],
            'kode_aktivasi' => ['required', 'string', 'max:255'],
            'kata_sandi' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->failure('Data aktivasi tidak valid.', ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $pengguna = Pengguna::where('no_telepon', $input['no_telepon'])->first();

        if (! $pengguna || $pengguna->status_akun !== 'belum_aktivasi' || ! $pengguna->kode_aktivasi) {
            return $this->failure('Akun tidak ditemukan atau sudah diaktivasi.', [], 422);
        }

        $kodeCocok = hash_equals($pengguna->kode_aktivasi, $input['kode_aktivasi'])
            || Hash::check($input['kode_aktivasi'], $pengguna->kode_aktivasi);

        if (! $kodeCocok) {
            return $this->failure('Kode aktivasi tidak valid.', [], 422);
        }

        if ($pengguna->kode_kedaluwarsa_pada && $pengguna->kode_kedaluwarsa_pada->isPast()) {
            return $this->failure('Kode aktivasi sudah kedaluwarsa.', [], 422);
        }

        $pengguna->forceFill([
            'kata_sandi' => $input['kata_sandi'],
            'status_akun' => 'aktif',
            'kode_aktivasi' => null,
            'kode_kedaluwarsa_pada' => null,
        ])->save();

        return $this->success('Akun berhasil diaktivasi. Silakan login.', [
            'user' => $pengguna->fresh(),
        ]);
    }
}
