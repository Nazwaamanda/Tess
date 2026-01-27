<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimKuartal extends Model
{
    protected $table = 'dim_kuartal';
    protected $primaryKey = 'id_kuartal';
    public $timestamps = false;
    protected $guarded = [];

    public function waktu()
    {
        return $this->hasMany(DimWaktu::class, 'id_kuartal', 'id_kuartal');
    }
}
