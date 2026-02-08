<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Keuangan - Alamtri Minerals</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 landscape; /* Landscape agar muat banyak kolom */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.4;
            color: #1f2937;
            margin: 0;
            padding: 0;
            font-size: 10px;
        }

        /* Header Style */
        .header {
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #065f46;
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p { margin: 2px 0; color: #6b7280; font-size: 11px; }

        /* Table Style */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            table-layout: fixed; /* Menjaga lebar kolom tetap */
        }
        th {
            background-color: #f0fdf4;
            color: #065f46;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 8px 4px;
            border: 1px solid #d1fae5;
            text-align: center;
        }
        .group-header {
            background-color: #059669;
            color: white;
            border: 1px solid #047857;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #e5e7eb;
            text-align: center;
            word-wrap: break-word;
        }
        tr:nth-child(even) { background-color: #f9fafb; }

        .stock-code { font-weight: bold; color: #065f46; }
        .desc-text {
            text-align: left;
            font-size: 8px;
            color: #4b5563;
            line-height: 1.2;
        }

        .positive { color: #059669; font-weight: bold; }
        .negative { color: #dc2626; font-weight: bold; }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Detail Kinerja & Analisis Rasio</h1>
        <p>PT. Alamtri Minerals Indonesia Tbk - Divisi Business Intelligence</p>
        <p>Periode Laporan: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No</th>
                <th rowspan="2" style="width: 8%;">Emiten & Periode</th>
                <th colspan="3" class="group-header">Rasio Likuiditas (x)</th>
                <th colspan="1" class="group-header">Solvabilitas (x)</th>
                <th colspan="3" class="group-header">Profitabilitas (%)</th>
                <th rowspan="2" style="width: 25%;">Analisis & Keterangan Status</th>
            </tr>
            <tr>
                <th>CR</th>
                <th>QR</th>
                <th>Cash</th>
                <th>DAR</th>
                <th>ROA</th>
                <th>ROE</th>
                <th>NPM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>
                    <span class="stock-code">{{ $row->perusahaan->kode_saham }}</span><br>
                    Q{{ $row->waktu->kuartal->nomor_kuartal }}-{{ $row->waktu->tahun->tahun }}
                </td>
                <td>{{ number_format($row->current_ratio, 2) }}</td>
                <td>{{ number_format($row->quick_ratio, 2) }}</td>
                <td>{{ number_format($row->cash_ratio, 2) }}</td>
                <td>{{ number_format($row->dar, 2) }}</td>
                <td class="{{ $row->roa < 0 ? 'negative' : 'positive' }}">{{ number_format($row->roa, 2) }}%</td>
                <td class="{{ $row->roe < 0 ? 'negative' : 'positive' }}">{{ number_format($row->roe, 2) }}%</td>
                <td class="{{ $row->npm < 0 ? 'negative' : 'positive' }}">{{ number_format($row->npm, 2) }}%</td>
                <td class="desc-text">
                    @php
                        // Mengambil keterangan dari relasi rasio (sesuai logika store Anda)
                        $likuiditas = $row->rasio->likuiditas ?? collect();
                        $solvabilitas = $row->rasio->solvabilitas ?? collect();
                        $profitabilitas = $row->rasio->profitabilitas ?? collect();

                        $ketLikuid = $likuiditas->where('nama_rasio', 'Current Ratio')->first()->keterangan ?? '-';
                        $ketSolvid = $solvabilitas->where('nama_rasio', 'DER')->first()->keterangan ?? '-';
                        $ketROA = $profitabilitas->where('nama_rasio', 'ROA')->first()->keterangan ?? '-';
                    @endphp
                    <strong>Likuiditas:</strong> {{ $ketLikuid }} |
                    <strong>Solvabilitas:</strong> {{ $ketSolvid }} |
                    <strong>Profit:</strong> {{ $ketROA }}<br>
                    <em>Catatan: Kondisi perusahaan dinyatakan {{ strtolower($ketLikuid) }} dengan efisiensi profit {{ strtolower($ketROA) }}.</em>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Halaman : Sistem FinSight Alamtri | Dicetak pada: {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
