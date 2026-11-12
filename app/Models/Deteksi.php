<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deteksi extends Model
{
    protected $table = 'deteksi';
    protected $primaryKey = 'id_deteksi';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['id_perangkat_a','id_perangkat_b','waktu_node_a','waktu_node_b','jeda_waktu_node','status_konsensus','jenis','tingkat_keparahan','puncak_getaran_g','deskripsi','berakhir_pada'];

    protected function casts(): array
    {
        return ['waktu_node_a'=>'datetime','waktu_node_b'=>'datetime','berakhir_pada'=>'datetime','status_konsensus'=>'boolean','puncak_getaran_g'=>'decimal:3'];
    }

    public function perangkatA(): BelongsTo { return $this->belongsTo(Perangkat::class, 'id_perangkat_a', 'id_perangkat'); }
    public function perangkatB(): BelongsTo { return $this->belongsTo(Perangkat::class, 'id_perangkat_b', 'id_perangkat'); }
    public function logAlarm(): HasMany { return $this->hasMany(LogAlarm::class, 'id_deteksi', 'id_deteksi'); }
    public function notifikasi(): HasMany { return $this->hasMany(Notifikasi::class, 'id_deteksi', 'id_deteksi'); }
    public function responsWarga(): HasMany { return $this->hasMany(ResponsWarga::class, 'id_deteksi', 'id_deteksi'); }
}
