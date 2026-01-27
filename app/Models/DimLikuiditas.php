<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimLikuiditas extends Model
{
    protected $table = 'dim_likuiditas';
    protected $primaryKey = 'id_likuiditas';
    public $timestamps = false;
    protected $guarded = [];

    public function rasio()
    {
        return $this->belongsTo(DimRasio::class, 'id_rasio', 'id_rasio');
    }
}
