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
            'identitas' => ['required_without_all:email,no_telepon', 'nullable', 'string', 'max:150'],
            'email' => ['required_without_all:identitas,no_telepon', 'nullable', 'email', 'max:150'],
            'no_telepon' => ['required_without_all:identitas,email', 'nullable', 'string', 'max:20'],
            'password' => ['required_without:kata_sandi', 'nullable', 'string', 'max:255'],
            'kata_sandi' => ['required_without:password', 'nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'fcm_token' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first(), ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $identifier = $input['identitas'] ?? $input['no_telepon'] ?? $input['email'] ?? null;
        $password = $input['password'] ?? $input['kata_sandi'] ?? null;

        if (! $identifier || ! $password) {
            return $this->failure('Identitas dan kata sandi wajib diisi.', [], 422);
        }

        $pengguna = Pengguna::query()
            ->where('email', $identifier)
            ->orWhere('no_telepon', $identifier)
            ->first();

        if (! $pengguna || $pengguna->status_akun !== 'aktif' || ! $pengguna->kata_sandi) {
            return $this->failure('Email/nomor telepon atau kata sandi tidak sesuai.', [], 401);
        }

        if (! Hash::check($password, $pengguna->kata_sandi)) {
            return $this->failure('Email/nomor telepon atau kata sandi tidak sesuai.', [], 401);
        }

        $token = $pengguna->createToken($input['device_name'] ?? 'flutter-app')->plainTextToken;
        $pengguna->forceFill([
            'terakhir_masuk_pada' => now(),
            'fcm_token' => $input['fcm_token'] ?? $pengguna->fcm_token,
        ])->save();

        $userData = $pengguna->fresh()->load('lokasi');

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $userData,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $userData,
            ],
        ], 200);
    }

    public function aktivasi(Request $request): JsonResponse
    {
        $rules = [
            'kode_aktivasi' => ['required', 'string', 'max:255'],
            'password' => ['required_without:kata_sandi', 'nullable', 'string', 'min:6', 'max:255'],
            'kata_sandi' => ['required_without:password', 'nullable', 'string', 'min:6', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'fcm_token' => ['nullable', 'string', 'max:255'],
        ];

        // password_confirmation opsional: jika dikirim, validasi same:password
        if ($request->has('password_confirmation')) {
            $rules['password_confirmation'] = ['same:password'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first(), ['errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        $kodeInput = strtoupper(trim((string) $input['kode_aktivasi']));
        $password = $input['password'] ?? $input['kata_sandi'];

        $pengguna = null;

        $candidates = Pengguna::query()
            ->where('status_akun', 'belum_aktivasi')
            ->whereNotNull('kode_aktivasi')
            ->when(! empty($input['no_telepon'] ?? null), fn ($q) => $q->where('no_telepon', $input['no_telepon']))
            ->get();

        foreach ($candidates as $candidate) {
            $stored = (string) $candidate->kode_aktivasi;

            if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2b$')) {
                if (Hash::check($kodeInput, $stored)) {
                    $pengguna = $candidate;
                    break;
                }
            } else {
                if (hash_equals(strtoupper(trim($stored)), $kodeInput)) {
                    $pengguna = $candidate;
                    break;
                }
            }
        }

        if (! $pengguna) {
            return $this->failure('Kode aktivasi tidak valid atau akun sudah aktif.', [], 422);
        }

        if ($pengguna->kode_kedaluwarsa_pada && $pengguna->kode_kedaluwarsa_pada->isPast()) {
            return $this->failure('Kode aktivasi sudah kedaluwarsa. Silakan minta kode baru ke petugas.', [], 422);
        }

        $pengguna->forceFill([
            'kata_sandi' => Hash::make($password),
            'status_akun' => 'aktif',
            'kode_aktivasi' => null,
            'kode_kedaluwarsa_pada' => null,
            'terakhir_masuk_pada' => now(),
            'fcm_token' => $input['fcm_token'] ?? $pengguna->fcm_token,
        ])->save();

        $token = $pengguna->createToken($input['device_name'] ?? 'flutter-app')->plainTextToken;
        $userData = $pengguna->fresh()->load('lokasi');

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diaktivasi.',
            'token' => $token,
            'user' => $userData,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $userData,
            ],
        ], 200);
    }
}
