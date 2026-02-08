@extends('layouts.admin')

@section('title', 'Riwayat Data Keuangan')

@section('content')
<Head>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/as.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/as.png') }}">
</Head>
<style>
    .bg-gradient-primary { background: linear-gradient(to right, #10b981, #0d9488); }
    .table-row-hover:hover { background-color: #f0fdfa; }

    /* Animasi Modal & Toast */
    .fade-enter { opacity: 0; transform: scale(0.95); }
    .fade-enter-active { opacity: 1; transform: scale(1); transition: opacity 0.3s, transform 0.3s; }
    .fade-exit { opacity: 1; transform: scale(1); }
    .fade-exit-active { opacity: 0; transform: scale(0.95); transition: opacity 0.3s, transform 0.3s; }
</style>

<div class="max-w-7xl mx-auto relative min-h-screen pb-20">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#064e3b]">Riwayat & Arsip Data</h1>
            <p class="text-[#047857]/70 text-sm mt-1">Repository laporan keuangan yang telah divalidasi dan dihitung.</p>
        </div>
        <a href="{{ route('admin.input') }}" class="flex items-center gap-2 bg-gradient-primary text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-[#10b981]/30 transition transform hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> Input Laporan Baru
        </a>
    </div>

    {{-- FILTER SECTION --}}
    <form action="{{ route('admin.riwayat') }}" method="GET">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-[#34d399]/30 mb-6 flex flex-col md:flex-row items-end gap-4">

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-[#0d9488] uppercase mb-1 tracking-wider">Tahun</label>
                <select name="filter_tahun" class="w-full bg-[#f0fdfa] border border-[#34d399]/30 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#10b981] font-medium text-[#064e3b]">
                    <option value="">Semua</option>
                    @foreach(range(date('Y'), 2020) as $year)
                        <option value="{{ $year }}" {{ request('filter_tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            {{-- TAMBAHAN: FILTER URUTAN (SORT) --}}
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-[#0d9488] uppercase mb-1 tracking-wider">Urutkan Berdasarkan</label>
                <div class="relative">
                    <i class="fas fa-sort-amount-down absolute left-3 top-3 text-[#34d399] text-xs"></i>
                    <select name="sort" class="pl-8 w-full bg-[#f0fdfa] border border-[#34d399]/30 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#10b981] font-medium text-[#064e3b]">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Input Terbaru</option>
                        <option value="year_asc" {{ request('sort') == 'year_asc' ? 'selected' : '' }}>Tahun (Terkecil - Terbesar)</option>
                        <option value="company_az" {{ request('sort') == 'company_az' ? 'selected' : '' }}>Perusahaan (A - Z)</option>
                    </select>
                </div>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-[#0d9488] uppercase mb-1 tracking-wider">Filter Triwulan</label>
                <select name="filter_triwulan" class="w-full bg-[#f0fdfa] border border-[#34d399]/30 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#10b981] font-medium text-[#064e3b]">
                    <option value="">Semua Triwulan</option>
                    <option value="1" {{ request('filter_triwulan') == '1' ? 'selected' : '' }}>Triwulan I</option>
                    <option value="2" {{ request('filter_triwulan') == '2' ? 'selected' : '' }}>Triwulan II</option>
                    <option value="3" {{ request('filter_triwulan') == '3' ? 'selected' : '' }}>Triwulan III</option>
                    <option value="4" {{ request('filter_triwulan') == '4' ? 'selected' : '' }}>Triwulan IV</option>
                </select>
            </div>

            <button type="submit" class="w-full md:w-auto bg-[#064e3b] text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-[#047857] transition flex items-center justify-center gap-2">
                <i class="fas fa-search text-[#34d399]"></i> Terapkan
            </button>
        </div>
    </form>

    {{-- TABEL DATA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#34d399]/20 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#ecfdf5] border-b border-[#34d399]/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#047857] uppercase tracking-widest">Periode Laporan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#047857] uppercase tracking-widest">Rasio Likuiditas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#047857] uppercase tracking-widest">Rasio Solvabilitas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#047857] uppercase tracking-widest">Rasio Profitabilitas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#047857] uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ecfdf5]">
                    @forelse($data as $item)
                        <tr class="table-row-hover transition-colors group">
                            {{-- Kolom Periode --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-[#f0fdfa] text-[#0d9488] flex items-center justify-center font-bold text-xs border border-[#34d399]/30">
                                        {{ $item->waktu->tahun->tahun ?? '-' }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#064e3b] text-sm">{{ $item->waktu->kuartal->nama_kuartal ?? '-' }}</div>
                                        <div class="text-[10px] text-[#0d9488]/70 mt-0.5">{{ $item->perusahaan->kode_saham ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            {{-- Kolom Likuiditas --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs"><span class="text-[#047857]/70">CR</span><span class="font-bold text-[#064e3b]">{{ number_format($item->current_ratio, 2) }} x</span></div>
                                    <div class="flex justify-between text-xs"><span class="text-[#047857]/70">Cash</span><span class="font-bold text-[#064e3b]">{{ number_format($item->cash_ratio, 2) }} x</span></div>
                                </div>
                            </td>
                            {{-- Kolom Solvabilitas --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="px-3 py-1.5 rounded-lg bg-[#ecfdf5] border border-[#34d399]/30 text-[#0d9488] text-xs font-bold">
                                        DER: {{ number_format($item->der, 2) }} x
                                    </div>
                                </div>
                            </td>
                            {{-- Kolom Profitabilitas --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs"><span class="text-[#047857]/70">ROA</span><span class="font-bold text-[#064e3b]">{{ number_format($item->roa, 1) }}%</span></div>
                                    <div class="flex justify-between text-xs"><span class="text-[#047857]/70">ROE</span><span class="font-bold text-[#064e3b]">{{ number_format($item->roe, 1) }}%</span></div>
                                </div>
                            </td>
                            {{-- Kolom Aksi --}}
                            <td class="px-6 py-4 align-middle text-center">
                                <button type="button"
                                onclick="openDeleteModal('{{ route('admin.destroy', $item->id_fakta) }}','{{ $item->perusahaan->nama_perusahaan }} ({{ $item->waktu->tahun->tahun }} - {{ $item->waktu->kuartal->nama_kuartal }})')"
                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-white border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 transition-all shadow-sm">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-[#047857]/50 italic">Belum ada data riwayat keuangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
        <div class="bg-[#f0fdfa] px-6 py-4 border-t border-[#34d399]/20">
            {{ $data->links() }}
        </div>
        @endif
    </div>

    {{-- ======================================================= --}}
    {{-- MODAL KONFIRMASI DELETE (Tailwind Modern) --}}
    {{-- ======================================================= --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="deleteBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                {{-- Modal Panel --}}
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md scale-95 opacity-0" id="deletePanel">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Hapus Data Permanen?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Anda akan menghapus data laporan: <br>
                                        <span id="deleteItemName" class="font-bold text-gray-800 block mt-1"></span>
                                    </p>
                                    <p class="text-xs text-red-500 mt-2 bg-red-50 p-2 rounded border border-red-100">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Perhatian: Data ini beserta seluruh riwayat perhitungannya akan dihapus permanen dari Data Warehouse.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <form id="deleteForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-transform hover:scale-105">Ya, Hapus Data</button>
                        </form>
                        <button type="button" onclick="closeDeleteModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- TOAST NOTIFICATION (SUKSES) --}}
    {{-- ======================================================= --}}
    @if(session('success'))
    <div id="toast-success" class="fixed top-5 right-5 flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-xl shadow-2xl border-l-4 border-green-500 z-50 transform translate-x-full transition-transform duration-500" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
        </div>
        <div class="ml-3 text-sm font-normal text-gray-800">{{ session('success') }}</div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" onclick="document.getElementById('toast-success').remove()">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
    @endif

</div>

{{-- JAVASCRIPT UNTUK MODAL & TOAST --}}
<script>
    // --- Logic Modal Delete ---
    const modal = document.getElementById('deleteModal');
    const backdrop = document.getElementById('deleteBackdrop');
    const panel = document.getElementById('deletePanel');
    const deleteForm = document.getElementById('deleteForm');
    const deleteItemName = document.getElementById('deleteItemName');

    function openDeleteModal(actionUrl, itemName) {
        deleteForm.action = actionUrl;
        deleteItemName.innerText = itemName;

        modal.classList.remove('hidden');
        // Trigger Animation
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // --- Logic Toast Slide-in ---
    document.addEventListener('DOMContentLoaded', () => {
        const toast = document.getElementById('toast-success');
        if (toast) {
            // Slide in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);

            // Auto hide after 4 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    });
</script>
@endsection
