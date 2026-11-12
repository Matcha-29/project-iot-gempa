<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'pengguna';

    protected $primaryKey = 'id_pengguna';

    protected $fillable = ['id_lokasi', 'nama', 'email', 'no_telepon', 'peran', 'kata_sandi', 'status_akun', 'kode_aktivasi', 'kode_kedaluwarsa_pada', 'nomor_rumah', 'latitude_rumah', 'longitude_rumah', 'catatan_khusus', 'fcm_token', 'terakhir_masuk_pada'];

    protected $hidden = ['kata_sandi', 'kode_aktivasi', 'fcm_token'];

    public function getAuthPasswordName()
    {
        return 'kata_sandi';
    }

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    protected function casts(): array
    {
        return ['kata_sandi' => 'hashed', 'kode_kedaluwarsa_pada' => 'datetime', 'terakhir_masuk_pada' => 'datetime', 'latitude_rumah' => 'decimal:7', 'longitude_rumah' => 'decimal:7'];
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi', 'id_lokasi');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_pengguna', 'id_pengguna');
    }

    public function responsWarga(): HasMany
    {
        return $this->hasMany(ResponsWarga::class, 'id_pengguna', 'id_pengguna');
    }

    public function responsDitangani(): HasMany
    {
        return $this->hasMany(ResponsWarga::class, 'id_petugas', 'id_pengguna');
    }
}
