<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $primaryKey = 'id_lokasi';
    protected $fillable = ['nama_lokasi','alamat','latitude','longitude','nomor_petugas'];

    protected function casts(): array
    {
        return ['latitude'=>'decimal:7','longitude'=>'decimal:7'];
    }

    public function perangkat(): HasMany { return $this->hasMany(Perangkat::class, 'id_lokasi', 'id_lokasi'); }
    public function pengguna(): HasMany { return $this->hasMany(Pengguna::class, 'id_lokasi', 'id_lokasi'); }
}
