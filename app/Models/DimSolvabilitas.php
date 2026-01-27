<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimSolvabilitas extends Model
{
    protected $table = 'dim_solvabilitas';
    protected $primaryKey = 'id_solvabilitas';
    public $timestamps = false;
    protected $guarded = [];

    public function rasio()
    {
        return $this->belongsTo(DimRasio::class, 'id_rasio', 'id_rasio');
    }
}
