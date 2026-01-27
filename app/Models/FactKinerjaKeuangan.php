<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactKinerjaKeuangan extends Model
{
    protected $table = 'fact_kinerja_keuangan';
    protected $primaryKey = 'id_fakta';
    public $timestamps = false; // Matikan timestamps jika tabel fakta tidak butuh created_at/updated_at

    // Guarded kosong artinya semua kolom bisa diisi (Mass Assignment)
    // Ini memudahkan saat input data kas, persediaan, dll sekaligus.
    protected $guarded = [];

    // ==========================
    // RELASI KE DIMENSI
    // ==========================

    // 1. Ke Dimensi Perusahaan
    public function perusahaan()
    {
        return $this->belongsTo(DimPerusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }

    // 2. Ke Dimensi Waktu
    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'id_waktu', 'id_waktu');
    }

    // 3. Ke Dimensi Rasio (Opsional/Nullable)
    public function rasio()
    {
        return $this->belongsTo(DimRasio::class, 'id_rasio', 'id_rasio');
    }

    // ==========================
    // SCOPE (OPSIONAL - UNTUK MEMUDAHKAN QUERY)
    // ==========================

    // Contoh cara pakai: FactKinerjaKeuangan::perusahaan('PT ABC')->get();
    public function scopeFilterPerusahaan($query, $namaPerusahaan)
    {
        return $query->whereHas('perusahaan', function($q) use ($namaPerusahaan) {
            $q->where('nama_perusahaan', 'like', "%{$namaPerusahaan}%");
        });
    }
}
