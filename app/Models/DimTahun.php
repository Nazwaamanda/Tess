<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimTahun extends Model
{
    protected $table = 'dim_tahun';
    protected $primaryKey = 'id_tahun';
    public $timestamps = false; // Tabel dimensi biasanya jarang butuh timestamps
    protected $guarded = [];

    // Relasi: Satu Tahun punya banyak Waktu (Kuartal)
    public function waktu()
    {
        return $this->hasMany(DimWaktu::class, 'id_tahun', 'id_tahun');
    }
}
