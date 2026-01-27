<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Smalot\PdfParser\Parser as PdfParser;

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
    public function dashboard()
    {
        $totalData = FactKinerjaKeuangan::count();
        $latestRecord = FactKinerjaKeuangan::with(['waktu.kuartal', 'waktu.tahun'])
            ->orderBy('tanggal_pencatatan', 'desc')->first();

        $labaRugi    = $latestRecord ? $latestRecord->laba_setelah_pajak : 0;
        $totalHutang = $latestRecord ? $latestRecord->total_utang : 0;
        $periodeLabel = $latestRecord
            ? "Periode " . $latestRecord->waktu->kuartal->nama_kuartal . " " . $latestRecord->waktu->tahun->tahun
            : "-";

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
        $query = FactKinerjaKeuangan::with(['waktu.tahun', 'waktu.kuartal', 'perusahaan']);
        if ($request->filled('filter_tahun')) {
            $query->whereHas('waktu.tahun', fn($q) => $q->where('tahun', $request->filter_tahun));
        }
        if ($request->filled('filter_triwulan')) {
            $query->whereHas('waktu.kuartal', fn($q) => $q->where('nomor_kuartal', $request->filter_triwulan));
        }
        $data = $query->orderBy('tanggal_pencatatan', 'desc')->paginate(10)->withQueryString();
        return view('admin.riwayat', compact('data'));
    }

   // ==========================================
    // DESTROY: HAPUS DATA WAREHOUSE (CASCADE)
    // ==========================================
    // ==========================================
    // DESTROY: HAPUS DATA WAREHOUSE (CASCADE)
    // ==========================================
  // ==========================================
    // DESTROY: HAPUS TOTAL (BERSIH-BERSIH DATA)
    // ==========================================
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // 1. Ambil Data Fakta beserta relasi untuk mendapatkan ID dimensi
            // Kita butuh ID ini sebelum data utamanya dihapus
            $fakta = FactKinerjaKeuangan::with(['waktu'])->findOrFail($id);

            $idRasio = $fakta->id_rasio;
            $idWaktu = $fakta->id_waktu;
            $idPerusahaan = $fakta->id_perusahaan;

            // Ambil ID Tahun dan Kuartal dari relasi Waktu sebelum dihapus
            $idTahun = $fakta->waktu ? $fakta->waktu->id_tahun : null;
            $idKuartal = $fakta->waktu ? $fakta->waktu->id_kuartal : null;

            // ---------------------------------------------------------
            // TAHAP 1: Hapus Data Utama (Fakta)
            // ---------------------------------------------------------
            $fakta->delete();

            // ---------------------------------------------------------
            // TAHAP 2: Hapus Data Rasio (Pasti hapus, karena unik per laporan)
            // ---------------------------------------------------------
            if ($idRasio) {
                DimLikuiditas::where('id_rasio', $idRasio)->delete();
                DimSolvabilitas::where('id_rasio', $idRasio)->delete();
                DimProfitabilitas::where('id_rasio', $idRasio)->delete();
                DimRasio::where('id_rasio', $idRasio)->delete();
            }

            // ---------------------------------------------------------
            // TAHAP 3: Hapus Perusahaan (Cek apakah masih ada laporan lain milik PT ini?)
            // ---------------------------------------------------------
            if ($idPerusahaan) {
                $perusahaanMasihDipakai = FactKinerjaKeuangan::where('id_perusahaan', $idPerusahaan)->exists();
                if (!$perusahaanMasihDipakai) {
                    DimPerusahaan::where('id_perusahaan', $idPerusahaan)->delete();
                }
            }

            // ---------------------------------------------------------
            // TAHAP 4: Hapus Waktu, Tahun, & Kuartal (Cek Ketergantungan)
            // ---------------------------------------------------------
            if ($idWaktu) {
                // Cek 1: Apakah 'Waktu' (Kombinasi Tahun+Kuartal) ini dipakai laporan lain?
                $waktuMasihDipakai = FactKinerjaKeuangan::where('id_waktu', $idWaktu)->exists();

                if (!$waktuMasihDipakai) {
                    // Hapus Dimensi Waktu (Jembatan)
                    DimWaktu::where('id_waktu', $idWaktu)->delete();

                    // Cek 2: Setelah Waktu dihapus, apakah 'Tahun' ini masih dipakai di DimWaktu lain?
                    if ($idTahun) {
                        $tahunMasihDipakai = DimWaktu::where('id_tahun', $idTahun)->exists();
                        if (!$tahunMasihDipakai) {
                            DimTahun::where('id_tahun', $idTahun)->delete();
                        }
                    }

                    // Cek 3: Setelah Waktu dihapus, apakah 'Kuartal' ini masih dipakai di DimWaktu lain?
                    if ($idKuartal) {
                        $kuartalMasihDipakai = DimWaktu::where('id_kuartal', $idKuartal)->exists();
                        if (!$kuartalMasihDipakai) {
                            DimKuartal::where('id_kuartal', $idKuartal)->delete();
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.riwayat')->with('success', 'Data berhasil dihapus. Database telah dibersihkan dari data yang tidak terpakai.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // ==========================================
    // STORE: LOGIKA PENYIMPANAN UTAMA
    // ==========================================
    public function store(Request $request)
    {
        // 1. Validasi Input
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

            // 2. Cek/Buat Dimensi (Perusahaan, Tahun, Kuartal, Waktu)
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

            // 3. CEK DUPLIKASI
            $isDuplicate = FactKinerjaKeuangan::where('id_perusahaan', $perusahaan->id_perusahaan)
                ->where('id_waktu', $dimWaktu->id_waktu)
                ->exists();

            if ($isDuplicate) {
                DB::rollBack();
                return back()
                    ->with('duplicate_error', "Data Laporan Keuangan <strong>{$val['nama_perusahaan']}</strong> periode <strong>Tahun {$val['tahun_fiskal']} (Q{$val['periode_triwulan']})</strong> sudah tersedia di database.")
                    ->withInput();
            }

            // 4. PERHITUNGAN RASIO
            $safeDiv = fn($n, $d) => $d != 0 ? ($n / $d) : 0;
            $fmt = fn($n) => number_format($n, 2, ',', '.');

            // a. Likuiditas (Hasil Decimal)
            // Note: 200% = 2.0, 150% = 1.5, dst.
            $cr = $safeDiv($val['aset_lancar'], $val['kewajiban_lancar']);
            $qr = $safeDiv(($val['aset_lancar'] - $val['persediaan']), $val['kewajiban_lancar']);
            $cashr = $safeDiv($val['kas'], $val['kewajiban_lancar']);

            // b. Solvabilitas (Hasil Decimal)
            $der = $safeDiv($val['total_kewajiban'], $val['ekuitas']);
            $dar = $safeDiv($val['total_kewajiban'], $val['total_aset']);

            // c. Profitabilitas (Hasil Persen - Dikali 100)
            $roa = $safeDiv($val['laba_setelah_pajak'], $val['total_aset']) * 100;
            $roe = $safeDiv($val['laba_setelah_pajak'], $val['ekuitas']) * 100;
            $npm = $safeDiv($val['laba_setelah_pajak'], $val['pendapatan']) * 100;

            // ---------------------------------------------------------
            // 5. LOGIKA KETERANGAN (STANDARISASI BARU)
            // ---------------------------------------------------------

            // A. Current Ratio (CR) - Target: >= 200% (2.0)
            if ($cr >= 2.0) { $ketCR = "Likuid"; }
            elseif ($cr >= 1.5) { $ketCR = "Baik"; }
            elseif ($cr >= 1.0) { $ketCR = "Waspada"; }
            else { $ketCR = "Illikuid"; }

            // B. Quick Ratio (QR) - Target: >= 150% (1.5)
            if ($qr >= 1.5) { $ketQR = "Sangat Baik"; }
            elseif ($qr >= 1.0) { $ketQR = "Baik"; }
            elseif ($qr >= 0.5) { $ketQR = "Cukup"; }
            else { $ketQR = "Kurang Baik"; }

            // C. Cash Ratio - Target: >= 50% (0.5)
            if ($cashr >= 0.5) { $ketCash = "Liquid"; }
            elseif ($cashr >= 0.2) { $ketCash = "Waspada"; }
            else { $ketCash = "Illiquid"; }

            // D. Debt to Equity Ratio (DER) - Target: <= 90% (0.9)
            // Note: DER semakin kecil semakin baik, urutan if dibalik atau pakai <=
            if ($der <= 0.9) { $ketDER = "Sangat Baik"; }
            elseif ($der <= 1.0) { $ketDER = "Baik"; }
            elseif ($der <= 1.5) { $ketDER = "Waspada"; }
            else { $ketDER = "Berisiko Tinggi"; }

            // E. Net Profit Margin (NPM) - Target: >= 20%
            if ($npm >= 20) { $ketNPM = "Sangat Baik"; }
            elseif ($npm >= 10) { $ketNPM = "Baik"; }
            elseif ($npm >= 5) { $ketNPM = "Cukup"; }
            else { $ketNPM = "Kurang Baik"; }

            // F. Return on Assets (ROA) - Target: >= 10%
            if ($roa >= 10) { $ketROA = "Sangat Baik"; }
            elseif ($roa >= 6) { $ketROA = "Baik"; }
            elseif ($roa >= 3) { $ketROA = "Cukup"; }
            else { $ketROA = "Kurang Baik"; }

            // G. Return on Equity (ROE) - Target: >= 20%
            if ($roe >= 20) { $ketROE = "Sangat Baik"; }
            elseif ($roe >= 15) { $ketROE = "Baik"; }
            elseif ($roe >= 10) { $ketROE = "Cukup"; }
            else { $ketROE = "Kurang Baik"; }


            // 6. Simpan Dimensi Rasio & Detailnya
            $dimRasio = DimRasio::create(['kategori' => "Analisis {$val['kode_saham']} {$val['tahun_fiskal']} Q{$val['periode_triwulan']}"]);

            // -- Simpan Detail Likuiditas --
            DimLikuiditas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'Current Ratio',
                'rumus' => "{$fmt($val['aset_lancar'])} / {$fmt($val['kewajiban_lancar'])}",
                'keterangan' => $ketCR
            ]);
            DimLikuiditas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'Quick Ratio',
                'rumus' => "({$fmt($val['aset_lancar'])} - {$fmt($val['persediaan'])}) / {$fmt($val['kewajiban_lancar'])}",
                'keterangan' => $ketQR
            ]);
            DimLikuiditas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'Cash Ratio',
                'rumus' => "{$fmt($val['kas'])} / {$fmt($val['kewajiban_lancar'])}",
                'keterangan' => $ketCash
            ]);

            // -- Simpan Detail Solvabilitas --
            DimSolvabilitas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'DER',
                'rumus' => "{$fmt($val['total_kewajiban'])} / {$fmt($val['ekuitas'])}",
                'keterangan' => $ketDER
            ]);

            // -- Simpan Detail Profitabilitas --
            DimProfitabilitas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'ROA',
                'rumus' => "({$fmt($val['laba_setelah_pajak'])} / {$fmt($val['total_aset'])}) x 100%",
                'keterangan' => $ketROA
            ]);
            DimProfitabilitas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'ROE',
                'rumus' => "({$fmt($val['laba_setelah_pajak'])} / {$fmt($val['ekuitas'])}) x 100%",
                'keterangan' => $ketROE
            ]);
            DimProfitabilitas::create([
                'id_rasio' => $dimRasio->id_rasio,
                'nama_rasio' => 'NPM',
                'rumus' => "({$fmt($val['laba_setelah_pajak'])} / {$fmt($val['pendapatan'])}) x 100%",
                'keterangan' => $ketNPM
            ]);

            // 7. Simpan ke Tabel FAKTA (Pusat Data)
            FactKinerjaKeuangan::create([
                'id_perusahaan' => $perusahaan->id_perusahaan,
                'id_waktu'      => $dimWaktu->id_waktu,
                'id_rasio'      => $dimRasio->id_rasio,
                // Data Mentah
                'kas'               => $val['kas'],
                'persediaan'        => $val['persediaan'],
                'aktiva_lancar'     => $val['aset_lancar'],
                'utang_lancar'      => $val['kewajiban_lancar'],
                'total_utang'       => $val['total_kewajiban'],
                'total_ekuitas'     => $val['ekuitas'],
                'total_aktiva'      => $val['total_aset'],
                'pendapatan'        => $val['pendapatan'],
                'laba_setelah_pajak'=> $val['laba_setelah_pajak'],
                // Nilai Rasio
                'current_ratio' => $cr,
                'quick_ratio'   => $qr,
                'cash_ratio'    => $cashr,
                'der'           => $der,
                'dar'           => $dar,
                'roa'           => $roa,
                'roe'           => $roe,
                'npm'           => $npm,
                'tanggal_pencatatan' => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.riwayat')->with('success', 'Data berhasil disimpan dan dihitung otomatis!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Error System: ' . $e->getMessage()])->withInput();
        }
    }

    // ==========================================
    // PARSING EXCEL/PDF (TIDAK BERUBAH)
    // ==========================================
    public function parseExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv,pdf|max:10240']);

        try {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $dataFound = [];

            $keywords = [
                'aset_lancar' => ['total aset lancar', 'jumlah aset lancar', 'total current assets', 'aset lancar'],
                'kas' => ['kas dan setara kas', 'cash and cash equivalents'],
                'persediaan' => ['persediaan', 'inventories'],
                'kewajiban_lancar' => ['total liabilitas jangka pendek', 'jumlah liabilitas jangka pendek', 'total current liabilities', 'utang lancar'],
                'total_kewajiban' => ['total liabilitas', 'jumlah liabilitas', 'total liabilities', 'total utang'],
                'ekuitas' => ['total ekuitas', 'jumlah ekuitas', 'total equity'],
                'total_aset' => ['total aset', 'jumlah aset', 'total assets', 'total aktiva'],
                'total_liabilitas_ekuitas' => ['total liabilitas dan ekuitas', 'total liabilities and equity'],
                'pendapatan' => ['pendapatan usaha', 'revenue', 'sales', 'penjualan dan pendapatan usaha'],
                'laba_setelah_pajak' => ['net of tax', 'setelah pajak', 'total comprehensive income', 'jumlah laba rugi komprehensif', 'laba periode berjalan', 'profit for the period'],
            ];

            if ($ext === 'pdf') {
                $dataFound = $this->processPdfFile($file, $keywords);
            } else {
                $dataFound = $this->processExcelFile($file, $keywords);
            }

            // Fallback Logic
            if (empty($dataFound['total_aset']) && !empty($dataFound['total_liabilitas_ekuitas'])) {
                $dataFound['total_aset'] = $dataFound['total_liabilitas_ekuitas'];
            }
            if (empty($dataFound['total_aset'])) {
                $u = $dataFound['total_kewajiban'] ?? 0;
                $e = $dataFound['ekuitas'] ?? 0;
                if ($u > 0 && $e > 0) $dataFound['total_aset'] = $u + $e;
            }
            if (empty($dataFound['total_kewajiban'])) {
                $a = $dataFound['total_aset'] ?? 0;
                $e = $dataFound['ekuitas'] ?? 0;
                if ($a > 0 && $e > 0) $dataFound['total_kewajiban'] = $a - $e;
            }

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
        $parser = new PdfParser();
        $pdf = $parser->parseFile($file->getPathname());
        $lines = explode("\n", $pdf->getText());
        $found = [];

        foreach ($lines as $line) {
            $cleanLine = trim($line);
            if (empty($cleanLine)) continue;
            $lineLower = strtolower($cleanLine);

            foreach ($keywords as $dbKey => $terms) {
                foreach ($terms as $term) {
                    if (str_contains($lineLower, $term)) {
                        if ($dbKey == 'pendapatan') {
                            if (str_contains($lineLower, 'cost') || str_contains($lineLower, 'beban')) continue;
                            if (str_contains($lineLower, 'other') || str_contains($lineLower, 'lain')) continue;
                            if (str_contains($lineLower, 'finance') || str_contains($lineLower, 'keuangan')) continue;
                            if (str_contains($lineLower, 'deferred') || str_contains($lineLower, 'ditangguhkan')) continue;
                        }
                        if ($dbKey == 'aset_lancar' && (str_contains($lineLower, 'tidak lancar') || str_contains($lineLower, 'non'))) continue;
                        if ($dbKey == 'total_aset' && (str_contains($lineLower, 'tidak lancar') || str_contains($lineLower, 'non'))) continue;
                        if ($dbKey == 'kewajiban_lancar' && (str_contains($lineLower, 'panjang') || str_contains($lineLower, 'non'))) continue;
                        if ($dbKey == 'total_kewajiban' && (str_contains($lineLower, 'ekuitas') || str_contains($lineLower, 'equity'))) continue;

                        if (preg_match_all('/\(?[\d,.]+\)?/', $cleanLine, $matches)) {
                            foreach ($matches[0] as $potentialNum) {
                                $val = $this->cleanNumber($potentialNum);
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

    private function processExcelFile($file, $keywords)
    {
        $sheets = Excel::toArray([], $file);
        $found = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet as $row) {
                $rowText = implode(' ', $row);
                $rowLower = strtolower($rowText);
                $value = 0;
                for ($i = 1; $i < count($row); $i++) {
                    $v = $this->cleanNumber($row[$i]);
                    if (abs($v) > 0) {
                        $value = $v;
                        break;
                    }
                }
                if ($value == 0) continue;

                foreach ($keywords as $dbKey => $terms) {
                    foreach ($terms as $term) {
                        if (str_contains($rowLower, $term)) {
                            if ($dbKey == 'pendapatan') {
                                if (str_contains($rowLower, 'cost') || str_contains($rowLower, 'beban')) continue;
                                if (str_contains($rowLower, 'other') || str_contains($rowLower, 'lain')) continue;
                                if (str_contains($rowLower, 'finance') || str_contains($rowLower, 'keuangan')) continue;
                                if (str_contains($rowLower, 'deferred') || str_contains($rowLower, 'ditangguhkan')) continue;
                            }
                            if ($dbKey == 'aset_lancar' && (str_contains($rowLower, 'tidak lancar') || str_contains($rowLower, 'non'))) continue;
                            if ($dbKey == 'total_aset' && (str_contains($rowLower, 'tidak lancar') || str_contains($rowLower, 'non'))) continue;
                            if ($dbKey == 'kewajiban_lancar' && (str_contains($rowLower, 'panjang') || str_contains($rowLower, 'non'))) continue;
                            if ($dbKey == 'total_kewajiban' && (str_contains($rowLower, 'ekuitas') || str_contains($rowLower, 'equity'))) continue;

                            $found[$dbKey] = $value;
                            break 2;
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
        $val = trim((string)$val);
        $isNeg = false;
        if (str_starts_with($val, '(') && str_ends_with($val, ')')) {
            $isNeg = true;
            $val = str_replace(['(', ')'], '', $val);
        }
        if (str_contains($val, ',')) {
            $val = str_replace(',', '', $val);
        } else {
            $val = str_replace('.', '', $val);
        }
        $clean = preg_replace('/[^0-9.]/', '', $val);
        $num = (float)$clean;
        return $isNeg ? -$num : $num;
    }
}
