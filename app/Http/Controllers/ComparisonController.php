<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FactKinerjaKeuangan;
use App\Models\DimPerusahaan;

class ComparisonController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil daftar periode unik dari Data Warehouse (Double Join)
        $availablePeriods = FactKinerjaKeuangan::join('dim_waktu', 'fact_kinerja_keuangan.id_waktu', '=', 'dim_waktu.id_waktu')
            ->join('dim_tahun', 'dim_waktu.id_tahun', '=', 'dim_tahun.id_tahun')
            ->join('dim_kuartal', 'dim_waktu.id_kuartal', '=', 'dim_kuartal.id_kuartal')
            ->select(
                'dim_tahun.id_tahun',
                'dim_tahun.tahun',
                'dim_kuartal.id_kuartal',
                'dim_kuartal.nama_kuartal',
                'dim_kuartal.nomor_kuartal'
            )
            ->distinct()
            ->orderBy('dim_tahun.tahun', 'desc')
            ->orderBy('dim_kuartal.nomor_kuartal', 'desc')
            ->get();

        // 2. Tentukan periode terpilih
        $latest = $availablePeriods->first();
        $selectedIdTahun = $request->get('tahun', $latest->id_tahun ?? null);
        $selectedIdKuartal = $request->get('kuartal', $latest->id_kuartal ?? null);

        // 3. Ambil data ADMR sebagai benchmark utama
        $admr = FactKinerjaKeuangan::join('dim_waktu', 'fact_kinerja_keuangan.id_waktu', '=', 'dim_waktu.id_waktu')
            ->whereHas('perusahaan', function($q) {
                $q->where('kode_saham', 'ADMR');
            })
            ->where('dim_waktu.id_tahun', $selectedIdTahun)
            ->where('dim_waktu.id_kuartal', $selectedIdKuartal)
            ->select('fact_kinerja_keuangan.*')
            ->first();

        // 4. Mapping Label untuk Judul dan Referensi
        $infoTahun = $availablePeriods->where('id_tahun', $selectedIdTahun)->first();
        $infoKtr = $availablePeriods->where('id_kuartal', $selectedIdKuartal)->first();

        $admrRef = [
            'code'  => $admr ? "ADMR (" . ($infoKtr->nama_kuartal ?? 'N/A') . "-" . ($infoTahun->tahun ?? 'N/A') . ")" : 'ADMR (No Data)',
            'cr'    => $admr ? (float)$admr->current_ratio : 0,
            'qr'    => $admr ? (float)$admr->quick_ratio : 0,
            'cash'  => $admr ? (float)$admr->cash_ratio : 0,
            'der'   => $admr ? (float)$admr->der : 0,
            'roa'   => $admr ? (float)$admr->roa : 0,
            'roe'   => $admr ? (float)$admr->roe : 0,
            'npm'   => $admr ? (float)$admr->npm : 0,
        ];

        return view('user.quick_compare', compact('admrRef', 'availablePeriods', 'selectedIdTahun', 'selectedIdKuartal'));
    }

    /**
     * API Endpoint jika Anda ingin memperbarui data referensi via AJAX (Opsional)
     */
    public function getAdmrData()
    {
        $admr = FactKinerjaKeuangan::whereHas('perusahaan', fn($q) => $q->where('kode_saham', 'ADMR'))
            ->latest('id_fakta')
            ->first();

        if (!$admr) {
            return response()->json(['error' => 'Data referensi tidak ditemukan'], 404);
        }

        return response()->json([
            'code' => 'ADMR',
            'cr'   => round($admr->current_ratio, 2),
            'der'  => round($admr->der, 2),
            'roe'  => round($admr->roe, 2),
        ]);
    }
}
