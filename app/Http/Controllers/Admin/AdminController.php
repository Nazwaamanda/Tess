<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Smalot\PdfParser\Parser as PdfParser;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\KinerjaExport;

// Import Model
use App\Models\DimTahun;
use App\Models\DimKuartal;
use App\Models\DimWaktu;
use App\Models\DimPerusahaan;
use App\Models\DimRasio;
use App\Models\DimLikuiditas;
use App\Models\DimSolvabilitas;
use App\Models\DimProfitabilitas;
use App\Models\FactKinerjaKeuangan;

class AdminController extends Controller
{
    // ... (Fungsi dashboard, show, input, riwayat, dan destroy tetap sama seperti sebelumnya) ...

    public function dashboard()
    {
        // 1. Hitung total semua data (tetap global atau bisa difilter ADMR jika mau)
        $totalData = FactKinerjaKeuangan::count();

        // 2. Ambil data ADMR pada periode Tahun dan Kuartal TERAKHIR
        $latestRecord = FactKinerjaKeuangan::with(['waktu.kuartal', 'waktu.tahun', 'perusahaan'])
            ->join('dim_perusahaan', 'fact_kinerja_keuangan.id_perusahaan', '=', 'dim_perusahaan.id_perusahaan')
            ->join('dim_waktu', 'fact_kinerja_keuangan.id_waktu', '=', 'dim_waktu.id_waktu')
            ->join('dim_tahun', 'dim_waktu.id_tahun', '=', 'dim_tahun.id_tahun')
            ->join('dim_kuartal', 'dim_waktu.id_kuartal', '=', 'dim_kuartal.id_kuartal')
            ->where('dim_perusahaan.kode_saham', 'ADMR') // Filter khusus ADMR
            ->orderBy('dim_tahun.tahun', 'desc')         // Tahun terbaru
            ->orderBy('dim_kuartal.nomor_kuartal', 'desc') // Kuartal terbaru
            ->select('fact_kinerja_keuangan.*')
            ->first();

        // 3. Masukkan ke variabel untuk View
        $labaRugi    = $latestRecord ? $latestRecord->pendapatan : 0; // Mengambil field pendapatan
        $totalHutang = $latestRecord ? $latestRecord->total_utang : 0;

        // Label periode untuk memperjelas tampilan (Contoh: ADMR - Periode Q4 2024)
        $periodeLabel = $latestRecord
            ? "ADMR - " . $latestRecord->waktu->kuartal->nama_kuartal . " " . $latestRecord->waktu->tahun->tahun
            : "Data ADMR tidak ditemukan";

        // 4. Riwayat 5 inputan terakhir (bisa tetap global untuk memantau semua aktivitas)
        $recents = FactKinerjaKeuangan::with(['waktu.tahun', 'waktu.kuartal', 'perusahaan'])
            ->orderBy('id_fakta', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact('totalData', 'labaRugi', 'totalHutang', 'recents', 'periodeLabel'));
    }

    public function show($id)
    {
        $data = FactKinerjaKeuangan::with([
            'perusahaan', 'waktu.tahun', 'waktu.kuartal',
            'rasio.likuiditas', 'rasio.solvabilitas', 'rasio.profitabilitas'
        ])->findOrFail($id);
        return view('admin.detail', compact('data'));
    }

    public function input() { return view('admin.input'); }

    public function riwayat(Request $request)
    {
        // Join tabel dimensi agar filter dan sorting berjalan di level database
        $query = FactKinerjaKeuangan::with(['waktu.tahun', 'waktu.kuartal', 'perusahaan'])
            ->join('dim_waktu', 'fact_kinerja_keuangan.id_waktu', '=', 'dim_waktu.id_waktu')
            ->join('dim_tahun', 'dim_waktu.id_tahun', '=', 'dim_tahun.id_tahun')
            ->join('dim_kuartal', 'dim_waktu.id_kuartal', '=', 'dim_kuartal.id_kuartal') // Tambahkan join kuartal
            ->join('dim_perusahaan', 'fact_kinerja_keuangan.id_perusahaan', '=', 'dim_perusahaan.id_perusahaan');

        // --- Filter Tahun ---
        if ($request->filled('filter_tahun')) {
            $query->where('dim_tahun.tahun', $request->filter_tahun);
        }

        // --- Filter Triwulan (Menggunakan kolom hasil join agar lebih cepat) ---
        if ($request->filled('filter_triwulan')) {
            $query->where('dim_kuartal.nomor_kuartal', $request->filter_triwulan);
        }

        // --- Sorting Logic ---
        $sort = $request->get('sort', 'latest');

        switch ($sort) {
            case 'year_asc':
                // Urutkan tahun dari terkecil, lalu kuartal dari terkecil (Q1 ke Q4)
                $query->orderBy('dim_tahun.tahun', 'asc')
                      ->orderBy('dim_kuartal.nomor_kuartal', 'asc');
                break;
            case 'company_az':
                $query->orderBy('dim_perusahaan.nama_perusahaan', 'asc');
                break;
            case 'latest':
            default:
                // Input terakhir yang dimasukkan muncul paling atas
                $query->orderBy('fact_kinerja_keuangan.id_fakta', 'desc');
                break;
        }

        // Select hanya kolom dari tabel fakta untuk menghindari bentrok nama kolom (seperti 'id')
        $data = $query->select('fact_kinerja_keuangan.*')->paginate(10)->withQueryString();

        return view('admin.riwayat', compact('data'));
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $fakta = FactKinerjaKeuangan::with(['waktu'])->findOrFail($id);
            $idRasio = $fakta->id_rasio;
            $idWaktu = $fakta->id_waktu;
            $idPerusahaan = $fakta->id_perusahaan;
            $idTahun = $fakta->waktu ? $fakta->waktu->id_tahun : null;
            $idKuartal = $fakta->waktu ? $fakta->waktu->id_kuartal : null;

            $fakta->delete();

            if ($idRasio) {
                DimLikuiditas::where('id_rasio', $idRasio)->delete();
                DimSolvabilitas::where('id_rasio', $idRasio)->delete();
                DimProfitabilitas::where('id_rasio', $idRasio)->delete();
                DimRasio::where('id_rasio', $idRasio)->delete();
            }

            if ($idPerusahaan) {
                $perusahaanMasihDipakai = FactKinerjaKeuangan::where('id_perusahaan', $idPerusahaan)->exists();
                if (!$perusahaanMasihDipakai) {
                    DimPerusahaan::where('id_perusahaan', $idPerusahaan)->delete();
                }
            }

            if ($idWaktu) {
                $waktuMasihDipakai = FactKinerjaKeuangan::where('id_waktu', $idWaktu)->exists();
                if (!$waktuMasihDipakai) {
                    DimWaktu::where('id_waktu', $idWaktu)->delete();
                    if ($idTahun) {
                        $tahunMasihDipakai = DimWaktu::where('id_tahun', $idTahun)->exists();
                        if (!$tahunMasihDipakai) DimTahun::where('id_tahun', $idTahun)->delete();
                    }
                    if ($idKuartal) {
                        $kuartalMasihDipakai = DimWaktu::where('id_kuartal', $idKuartal)->exists();
                        if (!$kuartalMasihDipakai) DimKuartal::where('id_kuartal', $idKuartal)->delete();
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.riwayat')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    public function laporan(Request $request)
    {
        $tahun = DimTahun::orderBy('tahun', 'desc')->get();
        $kuartal = DimKuartal::orderBy('nomor_kuartal', 'asc')->get();
        $perusahaan = DimPerusahaan::orderBy('nama_perusahaan', 'asc')->get(); // Tambah ini

        $query = FactKinerjaKeuangan::with(['waktu.tahun', 'waktu.kuartal', 'perusahaan']);

        // Logika Filter
        if ($request->filled('tahun')) {
            $query->whereHas('waktu.tahun', fn($q) => $q->where('tahun', $request->tahun));
        }
        if ($request->filled('kuartal')) {
            $query->whereHas('waktu.kuartal', fn($q) => $q->where('nomor_kuartal', $request->kuartal));
        }
        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan); // Filter Perusahaan
        }

        $data = $query->latest('id_fakta')->get();

        return view('admin.laporan', compact('data', 'tahun', 'kuartal', 'perusahaan'));
    }

    public function exportPdf(Request $request)
    {
        $query = FactKinerjaKeuangan::with(['waktu.tahun', 'waktu.kuartal', 'perusahaan']);

        if ($request->filled('tahun')) {
            $query->whereHas('waktu.tahun', fn($q) => $q->where('tahun', $request->tahun));
        }
        if ($request->filled('kuartal')) {
            $query->whereHas('waktu.kuartal', fn($q) => $q->where('nomor_kuartal', $request->kuartal));
        }
        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }

        $data = $query->get();
        $pdf = Pdf::loadView('admin.pdf_template', compact('data'))->setPaper('a4', 'landscape');
        return $pdf->download('Laporan_Kinerja_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Kirim ID Perusahaan ke class Export
        return Excel::download(
            new KinerjaExport($request->tahun, $request->kuartal, $request->perusahaan),
            'Laporan_Kinerja_' . date('Ymd') . '.xlsx'
        );
    }

    public function store(Request $request)
    {
        $val = $request->validate([
            'nama_perusahaan'   => 'required|string',
            'kode_saham'        => 'required|string',
            'sektor'            => 'required|string',
            'tahun_fiskal'      => 'required|numeric',
            'periode_triwulan'  => 'required|numeric',
            'aset_lancar'       => 'required|numeric',
            'kewajiban_lancar'  => 'required|numeric',
            'persediaan'        => 'required|numeric',
            'kas'               => 'required|numeric',
            'total_kewajiban'   => 'required|numeric',
            'ekuitas'           => 'required|numeric',
            'total_aset'        => 'required|numeric',
            'pendapatan'        => 'required|numeric',
            'laba_setelah_pajak'=> 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $perusahaan = DimPerusahaan::firstOrCreate(
                ['kode_saham' => strtoupper($val['kode_saham'])],
                ['nama_perusahaan' => $val['nama_perusahaan'], 'sektor' => $val['sektor']]
            );

            $dimTahun = DimTahun::firstOrCreate(['tahun' => $val['tahun_fiskal']]);
            $dimKuartal = DimKuartal::firstOrCreate(
                ['nomor_kuartal' => $val['periode_triwulan']],
                ['nama_kuartal' => 'Q' . $val['periode_triwulan']]
            );
            $dimWaktu = DimWaktu::firstOrCreate([
                'id_tahun'   => $dimTahun->id_tahun,
                'id_kuartal' => $dimKuartal->id_kuartal,
            ]);

            $isDuplicate = FactKinerjaKeuangan::where('id_perusahaan', $perusahaan->id_perusahaan)
                ->where('id_waktu', $dimWaktu->id_waktu)
                ->exists();

            if ($isDuplicate) {
                DB::rollBack();
                return back()->with('duplicate_error', "Data sudah tersedia.")->withInput();
            }

            $safeDiv = fn($n, $d) => $d != 0 ? ($n / $d) : 0;
            $fmt = fn($n) => number_format($n, 2, ',', '.');

            $cr = $safeDiv($val['aset_lancar'], $val['kewajiban_lancar']);
            $qr = $safeDiv(($val['aset_lancar'] - $val['persediaan']), $val['kewajiban_lancar']);
            $cashr = $safeDiv($val['kas'], $val['kewajiban_lancar']);
            $der = $safeDiv($val['total_kewajiban'], $val['ekuitas']);
            $dar = $safeDiv($val['total_kewajiban'], $val['total_aset']);
            $roa = $safeDiv($val['laba_setelah_pajak'], $val['total_aset']) * 100;
            $roe = $safeDiv($val['laba_setelah_pajak'], $val['ekuitas']) * 100;
            $npm = $safeDiv($val['laba_setelah_pajak'], $val['pendapatan']) * 100;

            // Logika Keterangan (Diringkas untuk efisiensi)
            $ketCR = $cr >= 2.0 ? "Likuid" : ($cr >= 1.0 ? "Waspada" : "Illikuid");
            $ketQR = $qr >= 1.5 ? "Sangat Baik" : ($qr >= 1.0 ? "Baik" : "Kurang Baik");
            $ketCash = $cashr >= 0.5 ? "Liquid" : "Illiquid";
            $ketDER = $der <= 1.0 ? "Sangat Baik" : ($der <= 1.5 ? "Waspada" : "Berisiko");
            $ketNPM = $npm >= 10 ? "Baik" : "Kurang Baik";
            $ketROA = $roa >= 6 ? "Baik" : "Kurang Baik";
            $ketROE = $roe >= 15 ? "Baik" : "Kurang Baik";

            $dimRasio = DimRasio::create(['kategori' => "Analisis {$val['kode_saham']} {$val['tahun_fiskal']} Q{$val['periode_triwulan']}"]);

            DimLikuiditas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'Current Ratio', 'rumus' => "{$fmt($val['aset_lancar'])} / {$fmt($val['kewajiban_lancar'])}", 'keterangan' => $ketCR]);
            DimLikuiditas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'Quick Ratio', 'rumus' => "({$fmt($val['aset_lancar'])} - {$fmt($val['persediaan'])}) / {$fmt($val['kewajiban_lancar'])}", 'keterangan' => $ketQR]);
            DimLikuiditas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'Cash Ratio', 'rumus' => "{$fmt($val['kas'])} / {$fmt($val['kewajiban_lancar'])}", 'keterangan' => $ketCash]);
            DimSolvabilitas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'DER', 'rumus' => "{$fmt($val['total_kewajiban'])} / {$fmt($val['ekuitas'])}", 'keterangan' => $ketDER]);
            DimProfitabilitas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'ROA', 'rumus' => "({$fmt($val['laba_setelah_pajak'])} / {$fmt($val['total_aset'])}) x 100%", 'keterangan' => $ketROA]);
            DimProfitabilitas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'ROE', 'rumus' => "({$fmt($val['laba_setelah_pajak'])} / {$fmt($val['ekuitas'])}) x 100%", 'keterangan' => $ketROE]);
            DimProfitabilitas::create(['id_rasio' => $dimRasio->id_rasio, 'nama_rasio' => 'NPM', 'rumus' => "({$fmt($val['laba_setelah_pajak'])} / {$fmt($val['pendapatan'])}) x 100%", 'keterangan' => $ketNPM]);

            FactKinerjaKeuangan::create([
                'id_perusahaan' => $perusahaan->id_perusahaan,
                'id_waktu'      => $dimWaktu->id_waktu,
                'id_rasio'      => $dimRasio->id_rasio,
                'kas'               => $val['kas'],
                'persediaan'        => $val['persediaan'],
                'aktiva_lancar'     => $val['aset_lancar'],
                'utang_lancar'      => $val['kewajiban_lancar'],
                'total_utang'       => $val['total_kewajiban'],
                'total_ekuitas'     => $val['ekuitas'],
                'total_aktiva'      => $val['total_aset'],
                'pendapatan'        => $val['pendapatan'],
                'laba_setelah_pajak'=> $val['laba_setelah_pajak'],
                'current_ratio' => $cr, 'quick_ratio' => $qr, 'cash_ratio' => $cashr, 'der' => $der, 'dar' => $dar, 'roa' => $roa, 'roe' => $roe, 'npm' => $npm,
                'tanggal_pencatatan' => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.riwayat')->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    // ==========================================
    // LOGIKA PARSING SMART ETL (FIXED)
    // ==========================================
    public function parseExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv,pdf|max:20480']);

        try {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            // KEYWORDS MAPPING (Diperbarui sesuai permintaan)
            $keywords = [
                'aset_lancar'       => [
                    'total aset lancar', // Prioritas Utama
                    'jumlah aset lancar',
                    'total current assets'
                ],
                'kas'               => ['kas dan setara kas', 'cash and cash equivalents'],
                'persediaan'        => ['persediaan lancar', 'inventories', 'persediaan'],
                'kewajiban_lancar'  => ['jumlah liabilitas jangka pendek', 'total current liabilities', 'liabilitas lancar'],
                'total_kewajiban'   => ['jumlah liabilitas', 'total liabilities'],
                'ekuitas'           => ['jumlah ekuitas', 'total equity'],
                'total_aset'        => ['jumlah aset', 'total assets'],
                'pendapatan'        => ['penjualan dan pendapatan usaha', 'revenue', 'sales'],
                'laba_setelah_pajak'=> [
                    'total penghasilan komprehensif periode berjalan', // Prioritas
                    'total rugi komprehensif periode berjalan',
                    'total comprehensive income for the period',
                    'total comprehensive loss for the period',
                    'jumlah laba rugi komprehensif',
                    'setelah pajak'
                ],
            ];

            if ($ext === 'pdf') {
                $dataFound = $this->processPdfFile($file, $keywords);
            } else {
                $dataFound = $this->processExcelFile($file, $keywords);
            }

            // Smart Fallback
            if (empty($dataFound['total_aset']) && isset($dataFound['total_kewajiban'], $dataFound['ekuitas'])) {
                $dataFound['total_aset'] = $dataFound['total_kewajiban'] + $dataFound['ekuitas'];
            }

            // Normalisasi Akhir
            foreach ($dataFound as $k => $v) {
                $dataFound[$k] = ($k !== 'laba_setelah_pajak') ? abs((float)$v) : (float)$v;
            }

            return response()->json(['status' => 'success', 'data' => $dataFound]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function processPdfFile($file, $keywords)
    {
        // 1. Tambahkan ini untuk mencegah timeout saat proses parsing berat
        set_time_limit(180); // Naikkan ke 3 menit khusus untuk fungsi ini

        $parser = new PdfParser();
        $pdf = $parser->parseFile($file->getPathname());

        // 2. Optimasi: Ambil hanya halaman tertentu (misal halaman 1-20)
        // Laporan Keuangan Konsolidasian biasanya ada di halaman awal.
        $pages = $pdf->getPages();
        $limitHalaman = min(count($pages), 20); // Batasi maksimal 20 halaman

        $found = [];

        for ($i = 0; $i < $limitHalaman; $i++) {
            $text = $pages[$i]->getText();
            $lines = explode("\n", $text);

            foreach ($lines as $line) {
                $cleanLine = trim($line);
                if (empty($cleanLine)) continue;
                $lineLower = strtolower($cleanLine);

                foreach ($keywords as $dbKey => $terms) {
                    if (isset($found[$dbKey])) continue;

                    foreach ($terms as $term) {
                        if (str_contains($lineLower, $term)) {
                            // Filter khusus Aset Lancar & Laba Komprehensif
                            if ($dbKey == 'total_aset' && (str_contains($lineLower, 'lancar') || str_contains($lineLower, 'tidak'))) continue;
                            if ($dbKey == 'aset_lancar' && !str_contains($lineLower, 'total')) continue; // Pastikan ambil TOTAL aset lancar

                            if (preg_match_all('/\(?[\d,.]+\)?/', $cleanLine, $matches)) {
                                foreach ($matches[0] as $potentialNum) {
                                    $val = $this->cleanNumber($potentialNum);

                                    // Melewati Note/Catatan (angka kecil)
                                    if (abs($val) > 100) {
                                        $found[$dbKey] = $val;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Jika semua keyword sudah ketemu, berhenti parsing halaman selanjutnya
            if (count($found) >= count($keywords)) break;
        }
        return $found;
    }

    private function processExcelFile($file, $keywords)
    {
        $sheets = Excel::toArray([], $file);
        $found = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet as $row) {
                $rowText = strtolower(implode(' ', array_filter($row)));

                foreach ($keywords as $dbKey => $terms) {
                    if (isset($found[$dbKey])) continue;

                    foreach ($terms as $term) {
                        if (str_contains($rowText, $term)) {
                            // Filter khusus
                            if ($dbKey == 'total_aset' && str_contains($rowText, 'lancar')) continue;

                            // Cari nilai di kolom, lewati angka kecil (Note)
                            foreach ($row as $cell) {
                                $val = $this->cleanNumber($cell);
                                if (abs($val) > 100) {
                                    $found[$dbKey] = $val;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $found;
    }

    private function cleanNumber($val)
    {
        if (is_null($val) || $val === '') return 0;
        if (is_numeric($val)) return (float)$val;

        $val = trim((string)$val);
        $isNeg = false;

        // Cek jika angka dalam kurung (format akuntansi untuk negatif)
        if (preg_match('/^\((.*)\)$/', $val, $m)) {
            $isNeg = true;
            $val = $m[1];
        }

        // Hapus spasi yang sering muncul saat parsing PDF
        $val = str_replace(' ', '', $val);

        // Standarisasi pemisah ribuan dan desimal
        if (str_contains($val, '.') && str_contains($val, ',')) {
            // Jika format ID 1.234,56 (titik lalu koma)
            if (strrpos($val, '.') < strrpos($val, ',')) {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // Jika format US 1,234.56
                $val = str_replace(',', '', $val);
            }
        } else {
            // Jika hanya satu jenis pemisah, cek apakah itu desimal (2 angka di belakang)
            if (preg_match('/[.,]\d{2}$/', $val)) {
                $val = str_replace(['.', ','], ['#', '.'], $val);
                $val = str_replace('#', '', $val);
            } else {
                // Anggap sebagai pemisah ribuan
                $val = str_replace(['.', ','], '', $val);
            }
        }

        $clean = preg_replace('/[^0-9.-]/', '', $val);
        return $isNeg ? -(float)$clean : (float)$clean;
    }
}
