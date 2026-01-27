<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimProfitabilitas extends Model
{
    protected $table = 'dim_profitabilitas';
    protected $primaryKey = 'id_profitabilitas';
    public $timestamps = false;
    protected $guarded = [];

    public function rasio()
    {
        return $this->belongsTo(DimRasio::class, 'id_rasio', 'id_rasio');
    }
}
