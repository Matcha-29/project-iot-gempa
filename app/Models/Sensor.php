<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sensor extends Model
{
    protected $table = 'sensor';
    protected $primaryKey = 'id_sensor';
    protected $fillable = ['id_perangkat','jenis_sensor','tipe_model','sensitivitas_lsb_per_g','status','status_diperbarui_pada','keterangan_error'];

    protected function casts(): array
    {
        return ['status_diperbarui_pada'=>'datetime'];
    }

    public function perangkat(): BelongsTo { return $this->belongsTo(Perangkat::class, 'id_perangkat', 'id_perangkat'); }
}
