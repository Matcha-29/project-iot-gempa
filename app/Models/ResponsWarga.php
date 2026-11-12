<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponsWarga extends Model
{
    protected $table = 'respons_warga';
    protected $primaryKey = 'id_respons';
    protected $fillable = ['id_pengguna','id_deteksi','status','latitude','longitude','akurasi_meter','ditangani_pada','id_petugas'];

    protected function casts(): array
    {
        return ['latitude'=>'decimal:7','longitude'=>'decimal:7','akurasi_meter'=>'decimal:1','ditangani_pada'=>'datetime'];
    }

    public function pengguna(): BelongsTo { return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna'); }
    public function deteksi(): BelongsTo { return $this->belongsTo(Deteksi::class, 'id_deteksi', 'id_deteksi'); }
    public function petugas(): BelongsTo { return $this->belongsTo(Pengguna::class, 'id_petugas', 'id_pengguna'); }
}
