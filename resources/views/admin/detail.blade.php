@extends('layouts.admin')

@section('title', 'Detail Laporan Keuangan')

@section('content')
<style>
    /* --- STYLE TAMPILAN WEB (NORMAL) --- */
    .bg-gradient-primary { background: linear-gradient(to right, #10b981, #0d9488); }

    /* --- STYLE KHUSUS CETAK (PRINT) --- */
    @media print {
        /* 1. Reset Kertas & Warna */
        @page { size: A4; margin: 1.5cm; }

        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 12pt !important;
            -webkit-print-color-adjust: exact !important; /* Paksa cetak background jika perlu */
        }

        /* 2. HILANGKAN TOOLBAR, SIDEBAR, NAVIGASI, LOGO BESAR */
        .no-print,
        nav,
        header,
        aside,
        .sidebar,
        button,
        .btn,
        .header-decoration, /* Hiasan Blob */
        .header-icon {      /* Ikon Logo Besar */
            display: none !important;
        }

        /* 3. Reset Layout Container */
        .max-w-6xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }

        /* Ubah Grid jadi Block agar urut ke bawah */
        .grid { display: block !important; }
        .lg\:col-span-2 { width: 100% !important; }

        /* 4. Styling Kartu menjadi Kotak Formal */
        .bg-white, .bg-gradient-to-br, .card-print {
            background: #ffffff !important;
            box-shadow: none !important;
            border: 1px solid #000 !important; /* Border hitam tegas */
            border-radius: 0 !important;
            margin-bottom: 20px !important;
            color: #000 !important;
            page-break-inside: avoid; /* Jangan potong elemen di tengah halaman */
        }

        /* Header Info Perusahaan (Tanpa background hijau) */
        .company-header {
            background: none !important;
            border: none !important;
            border-bottom: 2px solid #000 !important;
            color: #000 !important;
            padding: 0 !important;
            margin-bottom: 30px !important;
        }
        .company-header * { color: #000 !important; }

        /* Judul Section */
        h3, h4 { color: #000 !important; font-weight: bold !important; text-transform: uppercase; }

        /* Progress Bar (Ubah jadi border kotak sederhana) */
        .progress-bg { border: 1px solid #000 !important; background: #fff !important; height: 10px !important;}
        .progress-fill { background: #888 !important; }

        /* Tabel & List */
        .bg-ecfdf5, .bg-f0fdfa, .bg-fef2f2 { background: #fff !important; border: none !important; }
        li { border-bottom: 1px dotted #ccc; padding-bottom: 5px; margin-bottom: 5px; }

        /* Footer Cetak */
        .print-footer {
            display: block !important;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        /* Paksa semua teks jadi hitam */
        .text-gray-600, .text-white, .text-[#064e3b], .text-[#0d9488], .text-red-800 {
            color: #000 !important;
        }
    }

    /* Sembunyikan footer cetak di tampilan web */
    .print-footer { display: none; }
</style>

<div class="max-w-6xl mx-auto pb-10">

    {{-- HEADER & TOMBOL KEMBALI (Class 'no-print' menyembunyikan ini saat dicetak) --}}
    <div class="flex items-center justify-between mb-6 no-print">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-[#0d9488] hover:text-[#064e3b] text-sm mb-2 inline-flex items-center gap-1 font-semibold transition-colors">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-bold text-[#064e3b]">Detail Laporan Keuangan</h1>
            <p class="text-[#047857]/70 text-sm">
                ID Dokumen: #{{ str_pad($data->id_fakta, 6, '0', STR_PAD_LEFT) }} •
                Diinput pada {{ \Carbon\Carbon::parse($data->tanggal_pencatatan)->translatedFormat('d F Y') }}
            </p>
        </div>

        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-[#34d399]/30 text-[#0d9488] rounded-xl text-sm font-bold hover:bg-[#ecfdf5] transition shadow-sm">
                <i class="fas fa-print mr-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI --}}
        <div class="space-y-6">

            {{-- INFO PERUSAHAAN --}}
            {{-- Class 'company-header' untuk styling khusus print --}}
            <div class="bg-gradient-to-br from-[#064e3b] to-[#0d9488] rounded-2xl p-6 text-white shadow-lg relative overflow-hidden company-header">

                {{-- Hiasan Blob (Hilang saat print karena class header-decoration) --}}
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full blur-3xl header-decoration"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        {{-- Ikon Gedung (Hilang saat print karena class header-icon) --}}
                        <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl header-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">{{ $data->perusahaan->nama_perusahaan }}</h3>
                            <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-bold tracking-wider border border-transparent print:border-black print:text-black">
                                KODE SAHAM: {{ $data->perusahaan->kode_saham }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-white/10 pt-4 print:border-black">
                        <div>
                            <p class="text-[10px] text-white/70 uppercase tracking-wider">Tahun Fiskal</p>
                            <p class="font-bold text-lg">{{ $data->waktu->tahun->tahun }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-white/70 uppercase tracking-wider">Periode</p>
                            <p class="font-bold text-lg">{{ $data->waktu->kuartal->nama_kuartal }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] text-white/70 uppercase tracking-wider">Sektor Industri</p>
                            <p class="font-medium text-sm">{{ $data->perusahaan->sektor }}</p>
                        </div>
                        <div class="col-span-2 mt-2 pt-2 border-t border-white/10 print:border-black">
                             <p class="text-[10px] text-white/70 italic uppercase tracking-wider">* Angka disajikan dalam ribuan Dolar AS (USD '000)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ANALISIS RASIO --}}
            {{-- Class 'card-print' memastikan border hitam saat print --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#34d399]/20 p-6 card-print">
                <h3 class="font-bold text-[#064e3b] mb-4 flex items-center gap-2">
                    {{-- Ikon chart hilang saat print --}}
                    <i class="fas fa-chart-pie text-[#10b981] no-print"></i> Analisis Rasio
                </h3>

                <div class="space-y-5">
                    {{-- Liquidity --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-[#0d9488]">Current Ratio</span>
                            <span class="font-bold text-[#064e3b]">{{ number_format($data->current_ratio, 2) }}x</span>
                        </div>
                        <div class="w-full bg-[#f0fdfa] rounded-full h-1.5 progress-bg">
                            <div class="bg-[#10b981] h-1.5 rounded-full progress-fill" style="width: {{ min($data->current_ratio * 20, 100) }}%"></div>
                        </div>
                    </div>

                    {{-- Solvency --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-[#0d9488]">DER (Debt to Equity)</span>
                            <span class="font-bold text-[#064e3b]">{{ number_format($data->der, 2) }}x</span>
                        </div>
                        <div class="w-full bg-[#f0fdfa] rounded-full h-1.5 progress-bg">
                            <div class="bg-[#f59e0b] h-1.5 rounded-full progress-fill" style="width: {{ min($data->der * 30, 100) }}%"></div>
                        </div>
                    </div>

                    {{-- Profitability --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-[#0d9488]">ROA (Return on Asset)</span>
                            <span class="font-bold text-[#064e3b]">{{ number_format($data->roa, 2) }}%</span>
                        </div>
                        <div class="w-full bg-[#f0fdfa] rounded-full h-1.5 progress-bg">
                            <div class="bg-[#06b6d4] h-1.5 rounded-full progress-fill" style="width: {{ min($data->roa * 5, 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-[#0d9488]">NPM (Net Profit Margin)</span>
                            <span class="font-bold text-[#064e3b]">{{ number_format($data->npm, 2) }}%</span>
                        </div>
                        <div class="w-full bg-[#f0fdfa] rounded-full h-1.5 progress-bg">
                            <div class="bg-[#06b6d4] h-1.5 rounded-full progress-fill" style="width: {{ min($data->npm * 2, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: DATA KEUANGAN LENGKAP --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- DATA NERACA (POSISI KEUANGAN) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#34d399]/20 overflow-hidden card-print">
                <div class="bg-[#ecfdf5] px-6 py-4 border-b border-[#34d399]/20 flex items-center justify-between">
                    <h3 class="font-bold text-[#064e3b]">Posisi Keuangan (Neraca)</h3>
                    {{-- Badge USD hilang saat print jika mengganggu --}}
                    <span class="text-[10px] bg-white px-2 py-1 rounded text-[#047857] border border-[#34d399]/20 font-bold no-print">In Thousands USD</span>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 print:block">
                    {{-- Aset --}}
                    <div class="print:mb-4">
                        <h4 class="text-xs font-bold text-[#0d9488] uppercase mb-3 border-b border-[#f0fdfa] pb-1 print:border-black">Aset</h4>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-600">Kas & Setara Kas</span>
                                <span class="font-mono font-medium text-[#064e3b]">$ {{ number_format($data->kas, 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-600">Persediaan</span>
                                <span class="font-mono font-medium text-[#064e3b]">$ {{ number_format($data->persediaan, 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between pt-2 border-t border-dashed border-gray-100 print:border-black">
                                <span class="text-[#0d9488] font-semibold">Total Aset Lancar</span>
                                <span class="font-mono font-bold text-[#064e3b]">$ {{ number_format($data->aktiva_lancar, 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between mt-4 p-2 bg-[#f0fdfa] rounded-lg print:border print:border-black print:bg-white">
                                <span class="text-[#064e3b] font-bold">TOTAL ASET</span>
                                <span class="font-mono font-extrabold text-[#064e3b]">$ {{ number_format($data->total_aktiva, 0, ',', '.') }}</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Liabilitas & Ekuitas --}}
                    <div>
                        <h4 class="text-xs font-bold text-[#0d9488] uppercase mb-3 border-b border-[#f0fdfa] pb-1 print:border-black">Liabilitas & Ekuitas</h4>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-600">Utang Lancar</span>
                                <span class="font-mono font-medium text-[#064e3b]">$ {{ number_format($data->utang_lancar, 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-[#0d9488] font-semibold">Total Utang</span>
                                <span class="font-mono font-bold text-[#064e3b]">$ {{ number_format($data->total_utang, 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between mt-4">
                                <span class="text-[#0d9488] font-semibold">Total Ekuitas</span>
                                <span class="font-mono font-bold text-[#064e3b]">$ {{ number_format($data->total_ekuitas, 0, ',', '.') }}</span>
                            </li>
                            <li class="flex justify-between mt-4 p-2 bg-[#fef2f2] rounded-lg border border-red-100 print:border-black print:bg-white">
                                <span class="text-red-800 font-bold text-xs">Balance Check</span>
                                <span class="font-mono font-bold text-red-800 text-xs">$ {{ number_format($data->total_utang + $data->total_ekuitas, 0, ',', '.') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- DATA LABA RUGI --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#34d399]/20 overflow-hidden card-print">
                <div class="bg-[#ecfdf5] px-6 py-4 border-b border-[#34d399]/20 flex items-center justify-between">
                    <h3 class="font-bold text-[#064e3b]">Kinerja (Laba Rugi)</h3>
                    <span class="text-[10px] bg-white px-2 py-1 rounded text-[#047857] border border-[#34d399]/20 font-bold no-print">In Thousands USD</span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between items-center p-3 bg-white border border-[#f0fdfa] rounded-xl shadow-sm print:border-black">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#ecfdf5] flex items-center justify-center text-[#10b981] no-print">
                                    <i class="fas fa-money-bill-alt"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-600">Pendapatan (Revenue)</span>
                            </div>
                            <span class="font-mono font-bold text-lg text-[#064e3b]">$ {{ number_format($data->pendapatan, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-[#ecfdf5] to-white border border-[#34d399]/20 rounded-xl shadow-sm print:border-black">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#10b981] flex items-center justify-center text-white no-print">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <span class="text-sm font-bold text-[#064e3b]">Laba Bersih (Net Income)</span>
                            </div>
                            <span class="font-mono font-extrabold text-xl text-[#064e3b]">$ {{ number_format($data->laba_setelah_pajak, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- FOOTER KHUSUS CETAK --}}
    <div class="print-footer">
        Dokumen ini dicetak otomatis dari Sistem Arsip Keuangan pada tanggal {{ date('d F Y H:i') }}. <br>
        PT Alamtri Minerals Indonesia Tbk. | Angka disajikan dalam ribuan Dolar AS.
    </div>
</div>
@endsection
