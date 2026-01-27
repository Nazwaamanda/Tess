<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimWaktu extends Model
{
    protected $table = 'dim_waktu';
    protected $primaryKey = 'id_waktu';
    public $timestamps = false;
    protected $guarded = [];

    // Relasi ke Induk
    public function tahun()
    {
        return $this->belongsTo(DimTahun::class, 'id_tahun', 'id_tahun');
    }

    public function kuartal()
    {
        return $this->belongsTo(DimKuartal::class, 'id_kuartal', 'id_kuartal');
    }

    // Relasi ke Fakta
    public function faktaKinerja()
    {
        return $this->hasMany(FactKinerjaKeuangan::class, 'id_waktu', 'id_waktu');
    }
}
