<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $timestamps = false;
    protected $fillable = ['id_pengguna','id_deteksi','pesan','jenis_notifikasi','status','dikirim_pada','dibaca_pada'];

    protected function casts(): array
    {
        return ['dikirim_pada'=>'datetime','dibaca_pada'=>'datetime','created_at'=>'datetime'];
    }

    public function pengguna(): BelongsTo { return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna'); }
    public function deteksi(): BelongsTo { return $this->belongsTo(Deteksi::class, 'id_deteksi', 'id_deteksi'); }
}
