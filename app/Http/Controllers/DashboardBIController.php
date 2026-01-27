<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardBIController extends Controller
{
    private $mainCompanyId = 1;

    public function index()
    {
        $years = DB::table('dim_tahun')->orderBy('tahun', 'desc')->pluck('tahun');
        $companies = DB::table('dim_perusahaan')->select('id_perusahaan', 'nama_perusahaan', 'kode_saham')->get();
        $latestYear = $years->first() ?? date('Y');

        return view('welcome', compact('years', 'companies', 'latestYear'));
    }

    public function getBiData(Request $request)
    {
        $type = $request->type;
        $year = $request->year;
        $quarter = $request->quarter;
        $companyId = $request->company_id ?? $this->mainCompanyId;

        if ($type == 'snapshot') {
            return $this->getSnapshotData($companyId, $year, $quarter);
        } elseif ($type == 'trend') {
            return $this->getTrendData($companyId, $year);
        } elseif ($type == 'comparison') {
            return $this->getComparisonData($year, $quarter);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    // ================================================================
    // 1. SNAPSHOT DATA
    // ================================================================
    private function getSnapshotData($companyId, $year, $quarter)
    {
        $data = DB::table('fact_kinerja_keuangan as f')
            ->join('dim_waktu as w', 'f.id_waktu', '=', 'w.id_waktu')
            ->join('dim_tahun as t', 'w.id_tahun', '=', 't.id_tahun')
            ->join('dim_kuartal as k', 'w.id_kuartal', '=', 'k.id_kuartal')
            ->where('f.id_perusahaan', $companyId)
            ->where('t.tahun', $year)
            ->where('k.nama_kuartal', $quarter)
            ->select('f.*') // Ambil semua ID untuk join nanti
            ->first();

        if (!$data) return response()->json(['data' => null]);

        return response()->json([
            'data' => $data,
            'analysis' => [
                'liq'  => $this->getAnalysisTextFromDB('dim_likuiditas', $data->id_rasio),
                'sol'  => $this->getAnalysisTextFromDB('dim_solvabilitas', $data->id_rasio),
                'prof' => $this->getAnalysisTextFromDB('dim_profitabilitas', $data->id_rasio)
            ]
        ]);
    }

    // ================================================================
    // 2. TREND DATA (PERBAIKAN UTAMA: JOIN KE DIMENSI)
    // ================================================================
    private function getTrendData($companyId, $year)
    {
        $query = DB::table('fact_kinerja_keuangan as f')
            ->join('dim_waktu as w', 'f.id_waktu', '=', 'w.id_waktu')
            ->join('dim_tahun as t', 'w.id_tahun', '=', 't.id_tahun')
            ->join('dim_kuartal as k', 'w.id_kuartal', '=', 'k.id_kuartal')
            ->where('f.id_perusahaan', $companyId)
            ->select(
                'f.id_rasio', // Kunci Utama untuk ambil keterangan
                't.tahun',
                'k.nama_kuartal',
                'k.nomor_kuartal',
                'f.current_ratio as cr', 'f.quick_ratio as qr', 'f.cash_ratio as cash',
                'f.der',
                'f.roa', 'f.roe', 'f.npm'
            );

        if ($year == 'all') {
            // [SCENARIO A: SEMUA TAHUN - Ambil Kuartal Terakhir]
            $rawData = $query->orderBy('t.tahun', 'asc')
                             ->orderBy('k.nomor_kuartal', 'desc')
                             ->get()
                             ->unique('tahun'); // Ambil 1 data per tahun (kuartal terakhir)

            $data = $rawData->map(function($item) {
                $item->label = $item->tahun;
                return $item;
            })->values();

        } else {
            // [SCENARIO B: SATU TAHUN - Ambil Q1-Q4]
            $data = $query->where('t.tahun', $year)
                          ->orderBy('k.nomor_kuartal', 'asc')
                          ->get()
                          ->map(function($item) {
                              $item->label = $item->nama_kuartal;
                              return $item;
                          });
        }

        // --- GENERATE HTML TEXT ANALISIS DARI DATABASE ---
        $htmlLiq = '';
        $htmlSol = '';
        $htmlProf = '';

        foreach ($data as $item) {
            $header = ($year == 'all')
                ? "<span class='font-bold text-[#005832] text-xs block mt-3 mb-1 border-b border-gray-200 pb-1'>Tahun {$item->tahun} (Data Akhir)</span>"
                : "<span class='font-bold text-[#005832] text-xs block mt-3 mb-1 border-b border-gray-200 pb-1'>{$item->nama_kuartal}</span>";

            // Panggil Helper yang mengambil kolom 'keterangan' dari DB
            $htmlLiq  .= $header . $this->getAnalysisTextFromDB('dim_likuiditas', $item->id_rasio);
            $htmlSol  .= $header . $this->getAnalysisTextFromDB('dim_solvabilitas', $item->id_rasio);
            $htmlProf .= $header . $this->getAnalysisTextFromDB('dim_profitabilitas', $item->id_rasio);
        }

        if ($data->isEmpty()) {
            $msg = "<span class='text-gray-400 italic text-[10px]'>Data belum tersedia untuk periode ini.</span>";
            $htmlLiq = $htmlSol = $htmlProf = $msg;
        }

        return response()->json([
            'data' => $data,
            'trend_analysis' => [
                'liq' => $htmlLiq,
                'sol' => $htmlSol,
                'prof' => $htmlProf
            ]
        ]);
    }

  // ================================================================
    // 3. COMPARISON DATA (FIX: Tambah Keterangan Profitabilitas)
    // ================================================================
    private function getComparisonData($year, $quarter)
    {
        if ($quarter == 'all') {
            // Cari kuartal terakhir
            $latestData = DB::table('fact_kinerja_keuangan as f')
                ->join('dim_waktu as w', 'f.id_waktu', '=', 'w.id_waktu')
                ->join('dim_tahun as t', 'w.id_tahun', '=', 't.id_tahun')
                ->join('dim_kuartal as k', 'w.id_kuartal', '=', 'k.id_kuartal')
                ->where('t.tahun', $year)
                ->orderBy('k.nomor_kuartal', 'desc')
                ->first();

            $targetQ = $latestData ? $latestData->nama_kuartal : 'Q4';
            $labelPeriode = "Setahun Penuh (Data Terakhir: $targetQ)";
        } else {
            $targetQ = $quarter;
            $labelPeriode = "Kuartal $quarter";
        }

        $data = DB::table('fact_kinerja_keuangan as f')
            ->join('dim_perusahaan as p', 'f.id_perusahaan', '=', 'p.id_perusahaan')
            ->join('dim_waktu as w', 'f.id_waktu', '=', 'w.id_waktu')
            ->join('dim_tahun as t', 'w.id_tahun', '=', 't.id_tahun')
            ->join('dim_kuartal as k', 'w.id_kuartal', '=', 'k.id_kuartal')
            ->where('t.tahun', $year)
            ->where('k.nama_kuartal', $targetQ)
            ->select(
                'f.id_rasio',
                'p.kode_saham',
                'p.nama_perusahaan',
                'f.current_ratio',
                'f.quick_ratio',
                'f.cash_ratio',
                'f.der',
                'f.roa',
                'f.roe',
                'f.npm'
            )
            ->orderBy('p.id_perusahaan', 'asc')
            ->get();

        $analysisList = [];
        foreach($data as $d) {
            // 1. Ambil Keterangan Likuiditas (CR)
            $ketLiq = DB::table('dim_likuiditas')
                        ->where('id_rasio', $d->id_rasio)
                        ->where('nama_rasio', 'Current Ratio')
                        ->value('keterangan') ?? '-';

            // 2. Ambil Keterangan Solvabilitas (DER)
            $ketSol = DB::table('dim_solvabilitas')
                        ->where('id_rasio', $d->id_rasio)
                        ->where('nama_rasio', 'DER')
                        ->value('keterangan') ?? '-';

            // 3. BARU: Ambil Keterangan Profitabilitas (ROA)
            $ketProf = DB::table('dim_profitabilitas')
                        ->where('id_rasio', $d->id_rasio)
                        ->where('nama_rasio', 'ROA')
                        ->value('keterangan') ?? '-';

            // Susun HTML untuk ditampilkan di Frontend
            $text = "<ul class='list-disc list-inside text-[10px] space-y-1 text-gray-600'>";
            $text .= "<li><b>Likuiditas (CR):</b> $ketLiq</li>";
            $text .= "<li><b>Solvabilitas (DER):</b> $ketSol</li>";
            $text .= "<li><b>Profitabilitas (ROA):</b> $ketProf</li>"; // <--- Baris ini ditambahkan
            $text .= "</ul>";

            $analysisList[] = [
                'company' => $d->nama_perusahaan,
                'code' => $d->kode_saham,
                'text' => $text
            ];
        }

        return response()->json([
            'chart_data' => $data,
            'analysis_list' => $analysisList
        ]);
    }

    // ================================================================
    // HELPER: AMBIL KETERANGAN DARI TABEL DIMENSI
    // ================================================================
    private function getAnalysisTextFromDB($table, $idRasio)
    {
        // Query langsung ke tabel dimensi terkait
        $rows = DB::table($table)->where('id_rasio', $idRasio)->get();

        if ($rows->isEmpty()) return '<span class="text-[10px] text-gray-400 italic"> - Tidak ada data -</span>';

        $html = '<ul class="space-y-1 mt-1">';

        foreach ($rows as $row) {
            // Logika Warna Badge berdasarkan isi teks keterangan
            $keterangan = $row->keterangan;
            $lowerKet = strtolower($keterangan);

            $colorClass = 'text-gray-600'; // Default
            if (str_contains($lowerKet, 'sehat') || str_contains($lowerKet, 'aman') || str_contains($lowerKet, 'bagus') || str_contains($lowerKet, 'efisien') || str_contains($lowerKet, 'likuid') || str_contains($lowerKet, 'kuat')) {
                $colorClass = 'text-[#007945] font-bold'; // Hijau Tua
            } elseif (str_contains($lowerKet, 'waspada') || str_contains($lowerKet, 'masalah') || str_contains($lowerKet, 'risiko') || str_contains($lowerKet, 'rendah')) {
                $colorClass = 'text-red-600 font-bold'; // Merah
            } elseif (str_contains($lowerKet, 'cukup') || str_contains($lowerKet, 'standar')) {
                $colorClass = 'text-yellow-600 font-bold'; // Kuning
            }

            // Format: Nama Indikator: Keterangan (dari DB)
            $html .= "<li class='text-[10px] flex justify-between items-start gap-2 border-b border-dashed border-gray-100 pb-1 last:border-0'>
                        <span class='font-semibold w-1/3 shrink-0 text-gray-700'>{$row->nama_rasio}</span>
                        <span class='{$colorClass} text-right w-2/3 leading-tight'>{$keterangan}</span>
                      </li>";
        }
        $html .= '</ul>';

        return $html;
    }
}
