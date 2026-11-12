<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelGetaran extends Model
{
    protected $table = 'level_getaran';

    protected $primaryKey = 'id_level';

    public $timestamps = false;

    protected $fillable = ['id_perangkat', 'akselerasi_x', 'akselerasi_y', 'akselerasi_z', 'nilai_getaran_g', 'nilai_magnitudo', 'status_sw420', 'kategori_level', 'dibuat_pada'];

    protected function casts(): array
    {
        return ['dibuat_pada' => 'datetime', 'nilai_getaran_g' => 'decimal:3', 'nilai_magnitudo' => 'decimal:3', 'status_sw420' => 'boolean'];
    }

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class, 'id_perangkat', 'id_perangkat');
    }
}
