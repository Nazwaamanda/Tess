@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
<style>
    /* Custom Gradient Text */
    .text-gradient-main {
        background: linear-gradient(to right, #10b981, #0d9488);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .text-gradient-ocean {
        background: linear-gradient(to right, #06b6d4, #14b8a6);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .text-gradient-danger {
        background: linear-gradient(to right, #f43f5e, #e11d48);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* Card Hover Effect */
    .dashboard-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(16, 185, 129, 0.1);
        transition: all 0.3s ease;
    }
    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(3, 110, 74, 0.15);
    }
</style>

<div class="max-w-7xl mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#064e3b]">Dashboard Keuangan</h1>
            <p class="text-[#047857]/80 text-sm mt-1">Ringkasan kinerja keuangan berdasarkan data terbaru.</p>
        </div>
        <div class="flex items-center gap-2 bg-[#ecfdf5] px-4 py-2 rounded-xl border border-[#34d399]/30 shadow-sm text-[#0d9488]">
            <i class="far fa-calendar-alt"></i>
            <span class="text-sm font-semibold">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</span>
        </div>
    </div>

    {{-- CARDS STATISTIK --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

        {{-- CARD 1: PENDAPATAN USAHA (REVENUE) --}}
        <div class="dashboard-card p-5 rounded-2xl shadow-sm group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-[#0d9488]/70 uppercase tracking-wider">Pendapatan Usaha</p>
                    {{-- Format angka dalam Ribuan USD --}}
                    <h3 class="text-xl font-extrabold text-gradient-main mt-1 truncate" title="$ {{ number_format($labaRugi, 0, ',', '.') }}">
                        $ {{ number_format($labaRugi, 0, ',', '.') }}
                        <span class="text-xs text-[#064e3b] font-normal">(000)</span>
                    </h3>
                    <span class="text-[10px] text-[#064e3b] font-bold bg-[#ecfdf5] px-2 py-0.5 rounded-full mt-2 inline-block border border-[#d1fae5]">
                        <i class="fas fa-chart-line mr-1"></i> {{ $periodeLabel ?? 'Data Terkini' }}
                    </span>
                </div>
                <div class="bg-gradient-to-br from-[#10b981] to-[#0d9488] p-3 rounded-xl text-white shadow-sm shadow-emerald-100 group-hover:scale-110 transition-transform">
                    <i class="fas fa-coins text-lg"></i>
                </div>
            </div>
        </div>

        {{-- CARD 2: TOTAL HUTANG --}}
        <div class="dashboard-card p-5 rounded-2xl shadow-sm group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-[#0d9488]/70 uppercase tracking-wider">Total Hutang</p>
                    {{-- Format angka dalam Ribuan USD --}}
                    <h3 class="text-xl font-extrabold text-gradient-danger mt-1 truncate" title="$ {{ number_format($totalHutang, 0, ',', '.') }}">
                        $ {{ number_format($totalHutang, 0, ',', '.') }}
                        <span class="text-xs text-[#be123c] font-normal">(000)</span>
                    </h3>
                    <span class="text-[10px] text-[#be123c] font-bold bg-[#ffe4e6] px-2 py-0.5 rounded-full mt-2 inline-block border border-[#fecdd3]">
                        <i class="fas fa-exclamation-circle mr-1"></i> Kewajiban Perusahaan
                    </span>
                </div>
                <div class="bg-gradient-to-br from-[#f43f5e] to-[#e11d48] p-3 rounded-xl text-white shadow-sm shadow-rose-100 group-hover:scale-110 transition-transform">
                    <i class="fas fa-hand-holding-usd text-lg"></i>
                </div>
            </div>
        </div>

        {{-- CARD 3: TOTAL DATA ENTRY --}}
        <div class="dashboard-card p-5 rounded-2xl shadow-sm group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-[#0d9488]/70 uppercase tracking-wider">Total Arsip Data</p>
                    <h3 class="text-2xl font-extrabold text-gradient-ocean mt-1">{{ number_format($totalData) }} Entri</h3>
                    <p class="text-[10px] text-[#064e3b]/50 mt-2 font-medium">Laporan tersimpan di sistem</p>
                </div>
                <div class="bg-gradient-to-br from-[#06b6d4] to-[#14b8a6] p-3 rounded-xl text-white shadow-sm shadow-cyan-100 group-hover:scale-110 transition-transform">
                    <i class="fas fa-database text-lg"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- SIDEBAR: AKSES CEPAT --}}
        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-[#064e3b] to-[#0d9488] rounded-2xl p-6 text-white shadow-lg shadow-emerald-100 sticky top-24 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>

                <h3 class="font-bold text-lg mb-1 relative z-10">Menu Pintas</h3>
                <p class="text-[#ecfdf5]/80 text-xs mb-6 relative z-10">Kelola data keuangan dengan cepat.</p>

                <div class="space-y-3 relative z-10">
                    <a href="{{ route('admin.input') }}" class="flex items-center justify-between p-3 bg-white/10 rounded-xl hover:bg-white/20 transition-all cursor-pointer backdrop-blur-sm border border-white/10 group">
                        <div class="flex items-center gap-3">
                            <div class="bg-white text-[#0d9488] w-8 h-8 rounded-lg flex items-center justify-center text-xs shadow-sm"><i class="fas fa-plus"></i></div>
                            <span class="text-sm font-semibold">Input Laporan Baru</span>
                        </div>
                        <i class="fas fa-chevron-right text-xs text-white/50 group-hover:translate-x-1 transition-transform"></i>
                    </a>

                    <a href="{{ route('admin.riwayat') }}" class="w-full flex items-center justify-between p-3 bg-white/10 rounded-xl hover:bg-white/20 transition-all cursor-pointer backdrop-blur-sm border border-white/10 group">
                        <div class="flex items-center gap-3">
                            <div class="bg-[#34d399] text-[#064e3b] w-8 h-8 rounded-lg flex items-center justify-center text-xs shadow-sm"><i class="fas fa-list"></i></div>
                            <span class="text-sm font-semibold">Lihat Semua Data</span>
                        </div>
                        <i class="fas fa-arrow-right text-xs text-white/50 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT: TABEL AKTIVITAS --}}
        <div class="lg:col-span-2">
            <div class="bg-white/80 backdrop-blur-sm border border-[#34d399]/30 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-[#ecfdf5] flex justify-between items-center bg-[#f0fdfa]/50">
                    <div>
                        <h3 class="font-bold text-[#064e3b]">Aktivitas Input Terakhir</h3>
                        <p class="text-[#0d9488]/70 text-xs mt-1">Data yang baru saja ditambahkan.</p>
                    </div>
                    <a href="{{ route('admin.riwayat') }}" class="text-xs font-bold text-[#10b981] hover:text-[#059669] hover:underline">Lihat Semua</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#064e3b]">
                        <thead class="text-xs text-[#047857] uppercase bg-[#ecfdf5]">
                            <tr>
                                <th scope="col" class="px-6 py-4">Periode</th>
                                <th scope="col" class="px-6 py-4">Tanggal Input</th>
                                <th scope="col" class="px-6 py-4">Status</th>
                                <th scope="col" class="px-6 py-4 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ecfdf5]">
                            @forelse($recents as $item)
                                <tr class="bg-white hover:bg-[#f0fdfa] transition-colors">
                                    <td class="px-6 py-4 font-medium">
                                        @php
                                            $qName = match($item->waktu->kuartal->nomor_kuartal) {
                                                1 => 'Jan - Mar', 2 => 'Apr - Jun', 3 => 'Jul - Sep', 4 => 'Okt - Des', default => '-'
                                            };
                                        @endphp
                                        TW {{ $item->waktu->kuartal->nomor_kuartal ?? '-' }} {{ $item->waktu->tahun->tahun ?? '-' }}
                                        <span class="block text-[10px] text-[#0d9488]/60 font-normal">{{ $qName }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-[#047857]">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pencatatan)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#ecfdf5] text-[#059669] text-xs font-bold px-2.5 py-0.5 rounded-full border border-[#34d399]/30">
                                            <i class="fas fa-check-circle text-[10px] mr-1"></i> Tersimpan
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.riwayat.show', $item->id_fakta) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-[#34d399]/50 text-[#0d9488] hover:bg-[#0d9488] hover:text-white transition shadow-sm tooltip" title="Lihat Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-[#047857]/50 italic text-xs">
                                        Belum ada data inputan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
