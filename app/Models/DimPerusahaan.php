<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimPerusahaan extends Model
{
    protected $table = 'dim_perusahaan';
    protected $primaryKey = 'id_perusahaan';
    public $timestamps = false;
    protected $guarded = [];

    // Relasi: Satu Perusahaan punya banyak data Fakta
    public function faktaKinerja()
    {
        return $this->hasMany(FactKinerjaKeuangan::class, 'id_perusahaan', 'id_perusahaan');
    }
}
