@extends('layouts.admin')

@section('title', 'Laporan Kinerja Keuangan')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#064e3b]">Pusat Pelaporan Kinerja</h1>
            <p class="text-[#047857]/80 text-sm mt-1">Ekspor data analisis keuangan ke format PDF atau Excel.</p>
        </div>
        <div class="flex items-center gap-2 bg-[#ecfdf5] px-4 py-2 rounded-xl border border-[#34d399]/30 shadow-sm text-[#0d9488]">
            <i class="fas fa-file-invoice"></i>
            <span class="text-sm font-semibold">Reporting Module</span>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="bg-white/80 backdrop-blur-md border border-[#34d399]/30 rounded-2xl p-6 shadow-sm mb-8">
        {{-- Cari bagian Filter Panel dan ganti grid-cols-1 md:grid-cols-4 menjadi md:grid-cols-5 --}}
<form action="{{ route('admin.laporan') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

    {{-- FILTER PERUSAHAAN (TAMBAHAN) --}}
    <div>
        <label class="block text-xs font-bold text-[#0d9488] uppercase mb-2 tracking-wider">Perusahaan</label>
        <select name="perusahaan" class="w-full bg-[#f9fafb] border-[#e5e7eb] rounded-xl text-sm focus:ring-[#10b981] transition-all">
            <option value="">Semua Perusahaan</option>
            @foreach($perusahaan as $p)
                <option value="{{ $p->id_perusahaan }}" {{ request('perusahaan') == $p->id_perusahaan ? 'selected' : '' }}>
                    {{ $p->kode_saham }} - {{ $p->nama_perusahaan }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Filter Tahun --}}
    <div>
        <label class="block text-xs font-bold text-[#0d9488] uppercase mb-2 tracking-wider">Tahun</label>
        <select name="tahun" class="w-full bg-[#f9fafb] border-[#e5e7eb] rounded-xl text-sm focus:ring-[#10b981]">
            <option value="">Semua Tahun</option>
            @foreach($tahun as $t)
                <option value="{{ $t->tahun }}" {{ request('tahun') == $t->tahun ? 'selected' : '' }}>{{ $t->tahun }}</option>
            @endforeach
        </select>
    </div>

    {{-- Filter Kuartal --}}
    <div>
        <label class="block text-xs font-bold text-[#0d9488] uppercase mb-2 tracking-wider">Kuartal</label>
        <select name="kuartal" class="w-full bg-[#f9fafb] border-[#e5e7eb] rounded-xl text-sm focus:ring-[#10b981]">
            <option value="">Semua Kuartal</option>
            @foreach($kuartal as $k)
                <option value="{{ $k->nomor_kuartal }}" {{ request('kuartal') == $k->nomor_kuartal ? 'selected' : '' }}>Q{{ $k->nomor_kuartal }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tombol Terapkan --}}
    <div class="flex gap-2">
        <button type="submit" class="w-full bg-[#10b981] text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-[#059669] transition shadow-sm">
            <i class="fas fa-filter"></i>
        </button>
        <a href="{{ route('admin.laporan') }}" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition">
            <i class="fas fa-undo"></i>
        </a>
    </div>

    {{-- Tombol Export --}}
    <div class="flex gap-2 justify-end">
        <a href="{{ route('admin.laporan.pdf', request()->all()) }}" target="_blank" class="bg-rose-500 text-white p-2.5 rounded-xl hover:bg-rose-600 transition shadow-sm">
            <i class="fas fa-file-pdf"></i>
        </a>
        <a href="{{ route('admin.laporan.excel', request()->all()) }}" class="bg-emerald-600 text-white p-2.5 rounded-xl hover:bg-emerald-700 transition shadow-sm">
            <i class="fas fa-file-excel"></i>
        </a>
    </div>
</form>
    </div>

    {{-- PREVIEW TABLE --}}
    {{-- PREVIEW TABLE --}}
<div class="bg-white border border-[#34d399]/20 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-[#ecfdf5] bg-[#f0fdfa]/50 flex items-center justify-between">
        <h3 class="font-bold text-[#064e3b] flex items-center">
            <i class="fas fa-eye mr-2 text-[#10b981]"></i> Pratinjau Analisis Lengkap
        </h3>
        <span class="text-xs bg-white px-3 py-1 rounded-full border border-[#34d399]/30 text-[#0d9488] font-medium">
            {{ $data->count() }} Entitas ditemukan
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-[10px] text-[#047857] uppercase bg-[#ecfdf5] border-b border-[#34d399]/20">
                <tr>
                    <th rowspan="2" class="px-4 py-4 border-r border-[#34d399]/10">Perusahaan & Periode</th>
                    <th colspan="3" class="px-4 py-2 text-center border-b border-[#34d399]/10 bg-[#f0fdf4]">Rasio Likuiditas (x)</th>
                    <th colspan="1" class="px-4 py-2 text-center border-b border-[#34d399]/10 bg-[#ecfdf5]">Solvabilitas</th>
                    <th colspan="3" class="px-4 py-2 text-center border-b border-[#34d399]/10 bg-[#fffbeb]">Profitabilitas (%)</th>
                    <th rowspan="2" class="px-4 py-4 border-l border-[#34d399]/10">Analisis Status & Keterangan</th>
                </tr>
                <tr>
                    <th class="px-3 py-2 text-center">Current</th>
                    <th class="px-3 py-2 text-center">Quick</th>
                    <th class="px-3 py-2 text-center">Cash</th>
                    <th class="px-3 py-2 text-center">DAR (x)</th>
                    <th class="px-3 py-2 text-center">ROA</th>
                    <th class="px-3 py-2 text-center">ROE</th>
                    <th class="px-3 py-2 text-center">NPM</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ecfdf5]">
                @forelse($data as $row)
                    <tr class="hover:bg-[#f0fdfa] transition-colors">
                        {{-- Emiten --}}
                        <td class="px-4 py-4 border-r border-[#f0fdfa]">
                            <span class="font-bold text-[#064e3b]">{{ $row->perusahaan->kode_saham }}</span>
                            <span class="block text-[10px] text-gray-400">Q{{ $row->waktu->kuartal->nomor_kuartal }} - {{ $row->waktu->tahun->tahun }}</span>
                        </td>

                        {{-- Likuiditas --}}
                        <td class="px-3 py-4 text-center font-medium">{{ number_format($row->current_ratio, 2) }}</td>
                        <td class="px-3 py-4 text-center font-medium">{{ number_format($row->quick_ratio, 2) }}</td>
                        <td class="px-3 py-4 text-center font-medium">{{ number_format($row->cash_ratio, 2) }}</td>

                        {{-- Solvabilitas --}}
                        <td class="px-3 py-4 text-center font-medium bg-[#f9fafb]">{{ number_format($row->dar, 2) }}</td>

                        {{-- Profitabilitas --}}
                        <td class="px-3 py-4 text-center font-bold {{ $row->roa < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($row->roa, 2) }}%</td>
                        <td class="px-3 py-4 text-center font-bold {{ $row->roe < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($row->roe, 2) }}%</td>
                        <td class="px-3 py-4 text-center font-bold {{ $row->npm < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($row->npm, 2) }}%</td>

                        {{-- Deskripsi Status --}}
                        <td class="px-4 py-4 border-l border-[#f0fdfa]">
                            <div class="flex flex-col gap-1">
                                @php
                                    $likuiditas = $row->rasio->likuiditas ?? collect();
                                    $solvabilitas = $row->rasio->solvabilitas ?? collect();
                                    $profitabilitas = $row->rasio->profitabilitas ?? collect();

                                    $ketCR = $likuiditas->where('nama_rasio', 'Current Ratio')->first()->keterangan ?? '-';
                                    $ketDER = $solvabilitas->where('nama_rasio', 'DER')->first()->keterangan ?? '-';
                                    $ketROA = $profitabilitas->where('nama_rasio', 'ROA')->first()->keterangan ?? '-';
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">L: {{ $ketCR }}</span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">S: {{ $ketDER }}</span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">P: {{ $ketROA }}</span>
                                </div>
                                <p class="text-[10px] text-gray-500 leading-tight mt-1 italic">
                                    "Kondisi keuangan {{ strtolower($ketCR) }} dengan efisiensi {{ strtolower($ketROA) }}."
                                </p>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-search text-4xl text-gray-200 mb-4"></i>
                                <p class="text-gray-400 italic">Data tidak ditemukan untuk filter ini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
    </div>
</div>
@endsection
