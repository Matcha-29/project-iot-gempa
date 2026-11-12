<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perangkat extends Model
{
    protected $table = 'perangkat';
    protected $primaryKey = 'id_perangkat';
    protected $fillable = ['id_lokasi','kode_perangkat','posisi_node','mikrokontroler','api_token','latitude','longitude','status','heartbeat_terakhir'];
    protected $hidden = ['api_token'];

    protected function casts(): array
    {
        return ['heartbeat_terakhir'=>'datetime','latitude'=>'decimal:7','longitude'=>'decimal:7'];
    }

    public function lokasi(): BelongsTo { return $this->belongsTo(Lokasi::class, 'id_lokasi', 'id_lokasi'); }
    public function sensor(): HasMany { return $this->hasMany(Sensor::class, 'id_perangkat', 'id_perangkat'); }
    public function levelGetaran(): HasMany { return $this->hasMany(LevelGetaran::class, 'id_perangkat', 'id_perangkat'); }
    public function deteksiA(): HasMany { return $this->hasMany(Deteksi::class, 'id_perangkat_a', 'id_perangkat'); }
    public function deteksiB(): HasMany { return $this->hasMany(Deteksi::class, 'id_perangkat_b', 'id_perangkat'); }
}
