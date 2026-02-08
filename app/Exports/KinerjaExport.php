<?php
namespace App\Exports;

use App\Models\FactKinerjaKeuangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KinerjaExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tahun, $kuartal, $perusahaan; // Tambah properti

    public function __construct($tahun = null, $kuartal = null, $perusahaan = null) {
        $this->tahun = $tahun;
        $this->kuartal = $kuartal;
        $this->perusahaan = $perusahaan;
    }

    public function collection() {
        $query = FactKinerjaKeuangan::with(['perusahaan', 'waktu.tahun', 'waktu.kuartal']);

        if ($this->tahun) {
            $query->whereHas('waktu.tahun', fn($q) => $q->where('tahun', $this->tahun));
        }
        if ($this->kuartal) {
            $query->whereHas('waktu.kuartal', fn($q) => $q->where('nomor_kuartal', $this->kuartal));
        }
        if ($this->perusahaan) {
            $query->where('id_perusahaan', $this->perusahaan);
        }

        return $query->get();
    }

    public function headings(): array {
        return ["Perusahaan", "Tahun", "Kuartal", "Pendapatan", "Total Hutang", "ROA (%)", "ROE (%)"];
    }

    public function map($row): array {
        return [
            $row->perusahaan->nama_perusahaan,
            $row->waktu->tahun->tahun,
            'Q' . $row->waktu->kuartal->nomor_kuartal,
            $row->pendapatan,
            $row->total_utang,
            $row->roa,
            $row->roe,
        ];
    }
}
