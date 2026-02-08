@extends('layouts.admin')

@section('title', 'Input Laporan Keuangan')

@section('content')
<style>
    .bg-gradient-primary { background: linear-gradient(to right, #10b981, #0d9488); }
    .input-field {
        width: 100%; background-color: #f0fdfa; border: 1px solid rgba(52, 211, 153, 0.3);
        border-radius: 0.75rem; padding: 0.6rem 1rem; font-weight: 600; color: #064e3b; outline: none;
    }
    .input-field:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    .label-text { display: block; font-size: 11px; font-weight: 700; color: #0d9488; margin-bottom: 4px; text-transform: uppercase; }
    .sub-label { display: block; font-size: 10px; color: #64748b; font-style: italic; margin-bottom: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .fa-spin-custom { animation: spin 1s linear infinite; }

    /* Simple Fade In Animation */
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>

<div class="max-w-6xl mx-auto pb-20 relative">
    <form action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#064e3b]">Input Data Keuangan</h1>
                <p class="text-[#047857]/70 text-sm">Upload file .PDF atau .Excel untuk pengisian otomatis.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-[#047857] bg-white border rounded-lg">Batal</a>
                <button type="submit" class="px-6 py-2 text-white bg-gradient-primary rounded-lg font-bold hover:shadow-lg transition">Simpan Data</button>
            </div>
        </div>

        {{-- Upload Area --}}
        <div class="bg-[#ecfdf5]/20 border-2 border-dashed border-[#10b981]/30 rounded-2xl p-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center text-[#10b981] shadow-sm"><i class="fas fa-file-invoice-dollar text-2xl"></i></div>
                <div>
                    <h3 class="font-bold text-[#064e3b]">Smart ETL Import</h3>
                    <p class="text-xs text-[#047857]/70">Otomatis membaca Neraca & Laba Rugi (PDF/Excel).</p>
                </div>
            </div>
            <div>
                <input type="file" id="etl_file" class="hidden" accept=".xlsx, .xls, .csv, .pdf" onchange="runETL(this)">
                <label for="etl_file" class="cursor-pointer px-5 py-3 text-xs font-bold text-white bg-gradient-primary rounded-xl hover:shadow-lg transition flex items-center gap-2">
                    <span id="btn_text"><i class="fas fa-magic"></i> Upload File</span>
                    <i id="loading_icon" class="fas fa-spinner fa-spin-custom hidden"></i>
                </label>
            </div>
        </div>

        {{-- Input Fields --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-[#064e3b] mb-4">Identitas Entitas</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div><label class="label-text">Nama Perusahaan</label><input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan') }}" class="input-field" required></div>
                        <div><label class="label-text">Kode Saham</label><input type="text" name="kode_saham" value="{{ old('kode_saham') }}" class="input-field uppercase" required></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="label-text">Sektor</label><select name="sektor" class="input-field"><option value="Energy">Energi</option><option value="Mining">Pertambangan</option></select></div>
                        <div><label class="label-text">Tahun</label><input type="number" name="tahun_fiskal" value="{{ old('tahun_fiskal', date('Y')) }}" class="input-field"></div>
                        <div><label class="label-text">Kuartal</label><select name="periode_triwulan" class="input-field"><option value="1">Q1</option><option value="2">Q2</option><option value="3">Q3</option><option value="4">Q4</option></select></div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-[#064e3b] mb-4">Posisi Keuangan (Neraca)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 border-b pb-2 mb-2"><label class="label-text text-lg">Total Aset (Total Assets)</label><input type="number" step="any" id="total_aset" name="total_aset" value="{{ old('total_aset') }}" class="input-field bg-green-50" placeholder="0" required></div>

                        <div><label class="label-text">Aset Lancar</label><span class="sub-label">Current Assets</span><input type="number" step="any" id="aset_lancar" name="aset_lancar" value="{{ old('aset_lancar') }}" class="input-field" required></div>
                        <div><label class="label-text">Utang Lancar</label><span class="sub-label">Current Liabilities</span><input type="number" step="any" id="kewajiban_lancar" name="kewajiban_lancar" value="{{ old('kewajiban_lancar') }}" class="input-field" required></div>

                        <div><label class="label-text">Kas & Setara Kas</label><span class="sub-label">Cash & Equivalents</span><input type="number" step="any" id="kas" name="kas" value="{{ old('kas') }}" class="input-field" required></div>
                        <div><label class="label-text">Persediaan</label><span class="sub-label">Inventories</span><input type="number" step="any" id="persediaan" name="persediaan" value="{{ old('persediaan') }}" class="input-field" required></div>

                        <div class="col-span-2 border-t pt-4 mt-2"><label class="label-text text-lg">Liabilitas & Ekuitas</label></div>
                        <div><label class="label-text">Total Utang</label><span class="sub-label">Total Liabilities</span><input type="number" step="any" id="total_kewajiban" name="total_kewajiban" value="{{ old('total_kewajiban') }}" class="input-field" required></div>
                        <div><label class="label-text">Total Ekuitas</label><span class="sub-label">Total Equity</span><input type="number" step="any" id="ekuitas" name="ekuitas" value="{{ old('ekuitas') }}" class="input-field" required></div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-[#064e3b] mb-4">Laba Rugi</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="label-text">Pendapatan</label><span class="sub-label">Revenue / Sales</span><input type="number" step="any" id="pendapatan" name="pendapatan" value="{{ old('pendapatan') }}" class="input-field" required></div>
                        <div><label class="label-text">Laba Komprehensif</label><span class="sub-label">Total Comprehensive Income (Net of Tax)</span><input type="number" step="any" id="laba_setelah_pajak" name="laba_setelah_pajak" value="{{ old('laba_setelah_pajak') }}" class="input-field" required></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-[#064e3b] text-white p-6 rounded-2xl mb-6 shadow-lg">
                    <h3 class="font-bold mb-3 border-b border-green-400 pb-2">Status Upload</h3>
                    <div id="status_box" class="text-sm opacity-80">Belum ada file diupload.</div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="font-bold text-[#064e3b] text-sm mb-4">Estimasi Rasio</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between text-sm items-center"><span>Current Ratio</span><span id="preview_cr" class="font-bold text-green-600">-</span></div>
                        <div class="flex justify-between text-sm items-center mt-4"><span>DER</span><span id="preview_der" class="font-bold text-blue-600">-</span></div>
                        <div class="flex justify-between text-sm items-center mt-4"><span>ROA</span><span id="preview_roa" class="font-bold text-orange-600">-</span></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ================= MODAL DUPLICATE ERROR ================= --}}
    @if(session('duplicate_error'))
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 backdrop-blur-sm fade-in">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative">
            <div class="flex items-start gap-4">
                <div class="bg-red-100 rounded-full p-3 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Data Sudah Ada!</h3>
                    <p class="text-gray-600 text-sm mt-2 leading-relaxed">{!! session('duplicate_error') !!}</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">Mengerti</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ================= MODAL SUCCESS (Optional if you want popup instead of simple alert) ================= --}}
    @if(session('success'))
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 backdrop-blur-sm fade-in">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="mx-auto bg-green-100 rounded-full w-16 h-16 flex items-center justify-center text-green-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Berhasil!</h3>
            <p class="text-gray-600 text-sm mt-2">{{ session('success') }}</p>
            <div class="mt-6">
                <button onclick="this.closest('.fixed').remove()" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[type="number"]').forEach(el => el.addEventListener('input', calcRatios));
        // Hitung ulang jika ada old input (saat error validasi)
        if(document.getElementById('aset_lancar').value) calcRatios();
    });

    function runETL(input) {
        if (!input.files[0]) return;

        const fd = new FormData();
        fd.append('file', input.files[0]);

        const btn = document.getElementById('btn_text');
        const icon = document.getElementById('loading_icon');
        btn.innerText = 'Menganalisis...';
        icon.classList.remove('hidden');

        fetch("{{ route('admin.input.parse') }}", {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                const fill = (id, val) => {
                    const el = document.getElementById(id);
                    if(el && val) {
                        el.value = val;
                        el.classList.add('bg-green-100');
                        setTimeout(() => el.classList.remove('bg-green-100'), 1500);
                    }
                };

                fill('aset_lancar', d.aset_lancar);
                fill('kas', d.kas);
                fill('persediaan', d.persediaan);
                fill('kewajiban_lancar', d.kewajiban_lancar);
                fill('total_kewajiban', d.total_kewajiban);
                fill('ekuitas', d.ekuitas);
                fill('total_aset', d.total_aset);
                fill('pendapatan', d.pendapatan);
                fill('laba_setelah_pajak', d.laba_setelah_pajak);

                document.getElementById('status_box').innerHTML = '✅ Data berhasil diekstrak!<br>Periksa angka sebelum simpan.';
                calcRatios();
                btn.innerText = 'Selesai';
            } else {
                alert('Gagal: ' + res.message);
                btn.innerText = 'Upload File';
            }
        })
        .catch(e => {
            console.error(e);
            alert('Terjadi kesalahan sistem.');
            btn.innerText = 'Upload File';
        })
        .finally(() => {
            icon.classList.add('hidden');
            input.value = '';
        });
    }

    function calcRatios() {
        const v = (id) => parseFloat(document.getElementById(id).value) || 0;
        const cr = v('kewajiban_lancar') ? (v('aset_lancar') / v('kewajiban_lancar')).toFixed(2) : 0;
        const der = v('ekuitas') ? (v('total_kewajiban') / v('ekuitas')).toFixed(2) : 0;
        const roa = v('total_aset') ? ((v('laba_setelah_pajak') / v('total_aset')) * 100).toFixed(1) : 0;

        document.getElementById('preview_cr').innerText = cr + 'x';
        document.getElementById('preview_der').innerText = der + 'x';
        document.getElementById('preview_roa').innerText = roa + '%';
    }
</script>
@endsection
