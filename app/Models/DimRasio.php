<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimRasio extends Model
{
    protected $table = 'dim_rasio';
    protected $primaryKey = 'id_rasio';
    public $timestamps = false;
    protected $guarded = [];

    // Relasi ke tabel detail rasio
    public function likuiditas()
    {
        return $this->hasMany(DimLikuiditas::class, 'id_rasio', 'id_rasio');
    }

    public function profitabilitas()
    {
        return $this->hasMany(DimProfitabilitas::class, 'id_rasio', 'id_rasio');
    }

    public function solvabilitas()
    {
        return $this->hasMany(DimSolvabilitas::class, 'id_rasio', 'id_rasio');
    }

    // Relasi ke tabel Fakta
    public function faktaKinerja()
    {
        return $this->hasMany(FactKinerjaKeuangan::class, 'id_rasio', 'id_rasio');
    }
}
