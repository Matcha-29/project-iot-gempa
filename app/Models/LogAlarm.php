<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAlarm extends Model
{
    protected $table = 'log_alarm';
    protected $primaryKey = 'id_log';
    protected $fillable = ['id_deteksi','waktu_alarm','jenis_alarm','status_alarm','durasi_detik','catatan','id_petugas'];

    protected function casts(): array
    {
        return ['waktu_alarm'=>'datetime'];
    }

    public function deteksi(): BelongsTo { return $this->belongsTo(Deteksi::class, 'id_deteksi', 'id_deteksi'); }
    public function petugas(): BelongsTo { return $this->belongsTo(Pengguna::class, 'id_petugas', 'id_pengguna'); }
}
