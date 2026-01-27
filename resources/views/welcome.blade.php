<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Keuangan Alamtri | Corporate Green</title>

    {{-- CDN Scripts & Styles --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Font Mono untuk Jam --}}
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; color: #1f2937; }
        :root { --color-primary: #007945; --color-dark: #005832; --color-lime: #cddd2e; }

        /* Gradient Header Background */
        .bg-gradient-header { background: linear-gradient(90deg, #007945 0%, #004d2c 100%); }

        /* Text Gradient untuk Logo */
        .text-gradient-logo {
            background: linear-gradient(to right, #ffffff 0%, #eaff00 50%, #cddd2e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.1));
        }

        /* Card Styling */
        .card-white { background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 1.5rem; transition: all 0.2s; }
        .card-white:hover { border-color: #84cc16; transform: translateY(-2px); }

        /* Filter Buttons */
        .filter-btn { background: white; color: #007945; border: 1px solid #e5e7eb; transition: all 0.2s; }
        .filter-btn:hover { background: #f0fdf4; }
        .filter-btn.active { background: #007945; color: white; border-color: #005832; box-shadow: 0 4px 6px rgba(0, 121, 69, 0.3); }

        /* Inputs */
        .sidebar-select { width: 100%; padding: 0.75rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; outline: none; }

        /* --- Custom Scrollbar (Transparan Abu) --- */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5); /* Abu transparan */
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background-color: rgba(107, 114, 128, 0.8);
        }

        .font-mono-clock { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-gray-50">

    {{-- NAVBAR --}}
    <nav class="bg-gradient-header fixed w-full z-50 top-0 shadow-lg border-b border-[#005832]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-24">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('assets/img/alamtri_logo.png') }}" alt="Alamtri Logo" class="h-20 w-20 object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
                    <div class="flex flex-col justify-center">
                        <span class="text-3xl font-extrabold text-gradient-logo tracking-tight leading-none">Alamtri FinSight</span>
                        <span class="text-[10px] text-green-100/80 font-medium tracking-widest uppercase mt-1 pl-0.5">Corporate Financial Dashboard</span>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden md:flex items-center px-5 py-2 rounded-full border border-white/20 bg-black/10 backdrop-blur-md shadow-inner">
                        <span id="clock-full" class="text-[11px] font-medium text-white/90 tracking-wide font-mono-clock">Loading...</span>
                    </div>
                    <a href="{{ route('login') }}" class="group flex items-center gap-2 text-white/80 hover:text-[#cddd2e] transition-colors duration-300">
                        <i class="fas fa-lock text-sm group-hover:rotate-12 transition-transform"></i>
                        <span class="font-bold text-xs tracking-widest uppercase">ADMIN</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-32 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        {{-- SECTION 1 --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#005832]">Kinerja Per Kuartal</h1>
                <p class="text-gray-500 text-sm mt-1">Snapshot data tahun terakhir tersedia: <span class="font-bold text-[#007945] bg-green-100 px-2 rounded">{{ $latestYear }}</span></p>
            </div>
            <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-200 flex flex-wrap gap-2">
                <button onclick="fetchSnapshotData('Q1')" id="btn-Q1" class="px-5 py-2 text-xs font-bold rounded-xl filter-btn">Quarter I</button>
                <button onclick="fetchSnapshotData('Q2')" id="btn-Q2" class="px-5 py-2 text-xs font-bold rounded-xl filter-btn">Quarter II</button>
                <button onclick="fetchSnapshotData('Q3')" id="btn-Q3" class="px-5 py-2 text-xs font-bold rounded-xl filter-btn">Quarter III</button>
                <button onclick="fetchSnapshotData('Q4')" id="btn-Q4" class="px-5 py-2 text-xs font-bold rounded-xl filter-btn">Quarter IV</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            {{-- Likuiditas --}}
            <div class="card-white p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-[#005832]">Likuiditas</h3>
                    <i class="fas fa-wallet text-xl text-[#007945]"></i>
                </div>
                <div id="liquidityChart" class="min-h-[200px]"></div>
                <div class="bg-[#f0fdf4] p-3 rounded-xl border border-green-100 mt-4">
                    <span class="text-[10px] uppercase font-bold text-green-600 block mb-1">Analisis Rasio</span>
                    <p id="liq-desc" class="text-xs text-[#005832] leading-relaxed">Memuat data...</p>
                </div>
            </div>
            {{-- Solvabilitas --}}
            <div class="card-white p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-[#005832]">Solvabilitas</h3>
                    <i class="fas fa-balance-scale text-xl text-lime-600"></i>
                </div>
                <div id="derChart" class="min-h-[200px] flex justify-center"></div>
                <div class="bg-[#f7fee7] p-3 rounded-xl border border-lime-200 mt-4">
                    <span class="text-[10px] uppercase font-bold text-lime-700 block mb-1">Analisis Rasio</span>
                    <p id="der-desc" class="text-xs text-[#3f6212] leading-relaxed font-medium">Memuat data...</p>
                </div>
            </div>
            {{-- Profitabilitas --}}
            <div class="card-white p-6 lg:col-span-1 md:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-[#005832]">Profitabilitas</h3>
                    <i class="fas fa-chart-line text-xl text-yellow-600"></i>
                </div>
                <div id="profitChart" class="min-h-[200px]"></div>
                <div class="bg-yellow-50 p-3 rounded-xl border border-yellow-100 mt-4">
                    <span class="text-[10px] uppercase font-bold text-yellow-700 block mb-1">Analisis Rasio</span>
                    <p id="prof-desc" class="text-xs text-yellow-800 leading-relaxed font-medium">Memuat data...</p>
                </div>
            </div>
        </div>

        {{-- SECTION 2 --}}
        <div class="border-t border-gray-200 pt-10">
            <h2 class="text-xl font-bold text-[#005832] mb-8 flex items-center"><i class="fas fa-history text-lime-600 mr-3"></i> Analisis Tren & Historis</h2>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                <div class="lg:col-span-3 space-y-8">
                    <div class="card-white p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="font-bold text-[#005832] text-sm uppercase">Tren Likuiditas</h4>
                        </div>
                        <div id="trendLiqChart" class="w-full h-[280px]"></div>
                    </div>
                    <div class="card-white p-6">
                        <h4 class="font-bold text-[#005832] text-sm uppercase mb-4">Tren Solvabilitas</h4>
                        <div id="trendSolvChart" class="w-full h-[280px]"></div>
                    </div>
                    <div class="card-white p-6">
                        <h4 class="font-bold text-[#005832] text-sm uppercase mb-4">Tren Profitabilitas</h4>
                        <div id="trendProfChart" class="w-full h-[280px]"></div>
                    </div>
                </div>

                <div class="lg:col-span-1 sticky top-32 space-y-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Filter Data</label>
                        <select id="companySlicer" onchange="refreshAllData()" class="sidebar-select mt-1 mb-3 cursor-pointer">
                            @foreach($companies as $company)
                                <option value="{{ $company->id_perusahaan }}">{{ $company->kode_saham }} - {{ \Illuminate\Support\Str::limit($company->nama_perusahaan, 15) }}</option>
                            @endforeach
                        </select>
                        <select id="yearSelect" onchange="updateSidePanel()" class="sidebar-select cursor-pointer">
                            <option value="all">Semua Tahun (Roll-Up)</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}">Tahun {{ $year }} (Drill-Down)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="card-white p-6 border-l-4 border-[#cddd2e]">
                        <h4 class="font-bold text-[#005832] mb-4 text-sm uppercase tracking-wider">Analisis Kinerja</h4>
                        <ul class="space-y-4">
                            <li class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="text-xs font-bold text-[#007945] mb-2 border-b border-gray-200 pb-1">LIKUIDITAS</div>
                                <div id="status-liq-trend" class="text-[11px] text-gray-600 leading-relaxed max-h-40 overflow-y-auto custom-scroll">Loading...</div>
                            </li>
                            <li class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="text-xs font-bold text-[#cddd2e] mb-2 border-b border-gray-200 pb-1">SOLVABILITAS</div>
                                <div id="status-sol-trend" class="text-[11px] text-gray-600 leading-relaxed max-h-40 overflow-y-auto custom-scroll">Loading...</div>
                            </li>
                            <li class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="text-xs font-bold text-yellow-600 mb-2 border-b border-gray-200 pb-1">PROFITABILITAS</div>
                                <div id="status-prof-trend" class="text-[11px] text-gray-600 leading-relaxed max-h-40 overflow-y-auto custom-scroll">Loading...</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3 --}}
        <div class="border-t border-gray-200 pt-10 mt-10">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#005832] flex items-center"><i class="fas fa-balance-scale-right text-lime-600 mr-3"></i> Perbandingan Kinerja Perusahaan</h2>
                    <p class="text-gray-500 text-sm mt-1">Benchmarking rasio antar entitas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="bg-white p-1 rounded-xl border border-gray-200 shadow-sm flex items-center">
                        <select id="compareYear" class="p-2 text-xs font-bold text-[#005832] outline-none bg-transparent cursor-pointer">
                            @foreach($years as $year)
                                <option value="{{ $year }}">Tahun {{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="w-px h-4 bg-gray-300 mx-1"></div>
                        <select id="compareQuarter" class="p-2 text-xs font-bold text-[#005832] outline-none bg-transparent cursor-pointer">
                            <option value="all">Setahun Penuh</option>
                            <option value="Q1">Quarter I</option>
                            <option value="Q2">Quarter II</option>
                            <option value="Q3">Quarter III</option>
                            <option value="Q4">Quarter IV</option>
                        </select>
                    </div>
                    <button onclick="fetchComparisonData()" class="bg-[#007945] hover:bg-[#005832] text-white p-2.5 rounded-xl shadow-sm transition-all active:scale-95" title="Terapkan Filter"><i class="fas fa-sync-alt text-xs"></i></button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="card-white p-5">
                    <h4 class="font-bold text-[#005832] text-xs uppercase mb-4 text-center">Komparasi Likuiditas</h4>
                    <div id="cmpLiqChart" class="min-h-[250px]"></div>
                </div>
                <div class="card-white p-5">
                    <h4 class="font-bold text-[#005832] text-xs uppercase mb-4 text-center">Komparasi Solvabilitas</h4>
                    <div id="cmpSolvChart" class="min-h-[250px]"></div>
                </div>
                <div class="card-white p-5">
                    <h4 class="font-bold text-[#005832] text-xs uppercase mb-4 text-center">Komparasi Profitabilitas</h4>
                    <div id="cmpProfChart" class="min-h-[250px]"></div>
                </div>
            </div>

            <div class="card-white p-8 border-l-4 border-[#cddd2e]">
                <h4 class="font-bold text-[#005832] text-lg mb-6">Analisis Perbandingan Kinerja Keuangan Perusahaan</h4>
                <div id="comparison-analysis-container" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
            </div>
        </div>
    </main>

    {{-- SCRIPTS (SAMA SEPERTI SEBELUMNYA) --}}
    <script>
        const latestYear = "{{ $latestYear }}";
        const colors = { primary: '#007945', secondary: '#cddd2e', dark: '#005832', light: '#84cc16', warning: '#eab308' };

        function updateRealTimeClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayName = days[now.getDay()];
            const dayNum = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const fullString = `${dayName}, ${dayNum} ${monthName} ${year} &nbsp;|&nbsp; ${h}.${m}.${s} WIB`;
            document.getElementById('clock-full').innerHTML = fullString;
        }
        setInterval(updateRealTimeClock, 1000); updateRealTimeClock();

        // CHARTS INIT
        var liqChart = new ApexCharts(document.querySelector("#liquidityChart"), { series: [{ data: [] }], chart: { type: 'bar', height: 220, toolbar: {show:false}}, colors: [colors.primary, colors.light, colors.secondary], plotOptions: { bar: { distributed: true, borderRadius: 6, columnWidth: '55%' } }, xaxis: { categories: ['Current', 'Quick', 'Cash'], labels: { style: { fontSize: '11px', fontWeight: 600 } } }, legend: { show: false }, grid: { borderColor: '#f1f1f1' } }); liqChart.render();
        var derChart = new ApexCharts(document.querySelector("#derChart"), { series: [0, 0], chart: { type: 'donut', height: 240 }, labels: ['Total Hutang', 'Total Modal'], colors: [colors.secondary, colors.dark], legend: { position: 'bottom', fontSize: '12px' }, dataLabels: { enabled: false }, plotOptions: { pie: { donut: { size: '65%' } } } }); derChart.render();
        var profChart = new ApexCharts(document.querySelector("#profitChart"), { series: [{ name: 'Ratio', data: [] }], chart: { type: 'area', height: 220, toolbar: {show:false} }, stroke: { curve: 'smooth', width: 3 }, fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3, stops: [0, 90, 100] } }, colors: [colors.warning], dataLabels: { enabled: true, style: { colors: ['#005832'] }, formatter: function (val) { return val.toFixed(2); } }, xaxis: { categories: ['ROA', 'ROE', 'NPM'], labels: { style: { fontSize: '11px', fontWeight: 600 } } }, yaxis: { show: true }, grid: { borderColor: '#f1f1f1' }, tooltip: { theme: 'light' } }); profChart.render();

        const trendOpts = { chart: { type: 'area', height: 280, toolbar: { show: false } }, stroke: { curve: 'smooth', width: 2 }, fill: { type: 'gradient' }, legend: { position: 'top', fontSize: '11px' } };
        var tLiq = new ApexCharts(document.querySelector("#trendLiqChart"), { chart: { type: 'bar', height: 280, toolbar: { show: false } }, series: [], colors: ['#005832', '#007945', '#cddd2e'], plotOptions: { bar: { horizontal: false, columnWidth: '60%', endingShape: 'rounded', borderRadius: 3 } }, dataLabels: { enabled: false }, legend: { show: false }, stroke: { show: true, width: 3, colors: ['transparent'] }, xaxis: { categories: [] }, grid: { borderColor: '#f1f1f1' } }); tLiq.render();
        var tSolv = new ApexCharts(document.querySelector("#trendSolvChart"), { ...trendOpts, series: [], colors: [colors.secondary] }); tSolv.render();
        var tProf = new ApexCharts(document.querySelector("#trendProfChart"), { chart: { type: 'area', height: 280, toolbar: { show: false }, stacked: false }, series: [], colors: [colors.dark, colors.primary, colors.warning], fill: { type: 'solid', opacity: 0.3 }, stroke: { curve: 'smooth', width: 2 }, dataLabels: { enabled: false }, legend: { position: 'top', fontSize: '11px' }, grid: { borderColor: '#f1f1f1' } }); tProf.render();

        const cmpOpts = { chart: { type: 'bar', height: 250, toolbar: { show: false } }, plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } }, dataLabels: { enabled: false }, stroke: { show: true, width: 2, colors: ['transparent'] }, xaxis: { categories: [] }, legend: { position: 'top', fontSize: '10px' } };
        var cmpLiqChart = new ApexCharts(document.querySelector("#cmpLiqChart"), { ...cmpOpts, series: [], colors: [colors.primary, colors.light, colors.secondary] }); cmpLiqChart.render();
        var cmpSolvChart = new ApexCharts(document.querySelector("#cmpSolvChart"), { ...cmpOpts, series: [], colors: [colors.dark] }); cmpSolvChart.render();
        var cmpProfChart = new ApexCharts(document.querySelector("#cmpProfChart"), { ...cmpOpts, series: [], colors: [colors.warning, colors.light, colors.primary] }); cmpProfChart.render();

        // FUNCTIONS
        function refreshAllData() { fetchSnapshotData('Q1'); updateSidePanel(); fetchComparisonData(); }

        async function fetchSnapshotData(quarter) {
            const companyId = document.getElementById('companySlicer').value;
            document.querySelectorAll('.filter-btn').forEach(btn => btn.className = "px-5 py-2 text-xs font-bold rounded-xl filter-btn");
            document.getElementById('btn-' + quarter).className = "px-5 py-2 text-xs font-bold rounded-xl filter-btn active shadow-md transform scale-105";

            try {
                const url = `{{ route('api.bi.data') }}?type=snapshot&year=${latestYear}&quarter=${quarter}&company_id=${companyId}`;
                const res = await (await fetch(url)).json();
                const d = res.data;

                if(d) {
                    const derVal = parseFloat(d.der).toFixed(2);
                    liqChart.updateSeries([{ data: [d.current_ratio, d.quick_ratio, d.cash_ratio] }]);
                    profChart.updateSeries([{ name: 'Rasio', data: [d.roa, d.roe, d.npm] }]);
                    derChart.updateSeries([parseFloat(d.total_utang), parseFloat(d.total_ekuitas)]);
                    derChart.updateOptions({ plotOptions: { pie: { donut: { labels: { show: true, name: { show: true, fontSize: '10px', color: '#005832', offsetY: -5 }, value: { show: true, fontSize: '16px', fontWeight: 700, color: '#005832', offsetY: 5 }, total: { show: true, showAlways: true, label: 'Nilai DER', color: '#005832', formatter: function (w) { return derVal + "x"; } } } } } } });
                } else {
                    liqChart.updateSeries([{ data: [0,0,0] }]); derChart.updateSeries([0,0]); profChart.updateSeries([{ data: [0,0,0] }]);
                    derChart.updateOptions({ plotOptions: { pie: { donut: { labels: { show: false } } } } });
                }

                if (res.analysis) {
                    document.getElementById('liq-desc').innerHTML = res.analysis.liq;
                    document.getElementById('der-desc').innerHTML = res.analysis.sol;
                    document.getElementById('prof-desc').innerHTML = res.analysis.prof;
                }
            } catch (e) { console.error(e); }
        }

        async function updateSidePanel() {
            const companyId = document.getElementById('companySlicer').value;
            const year = document.getElementById('yearSelect').value;
            try {
                const url = `{{ route('api.bi.data') }}?type=trend&year=${year}&company_id=${companyId}`;
                const res = await (await fetch(url)).json();
                const data = res.data;
                const labels = data.map(d => d.label);

                tLiq.updateOptions({ xaxis: { categories: labels } });
                tLiq.updateSeries([{ name: 'Current Ratio', data: data.map(d => parseFloat(d.cr).toFixed(2)) }, { name: 'Quick Ratio', data: data.map(d => parseFloat(d.qr).toFixed(2)) }, { name: 'Cash Ratio', data: data.map(d => parseFloat(d.cash).toFixed(2)) }]);
                tSolv.updateOptions({ xaxis: { categories: labels } });
                tSolv.updateSeries([{ name: 'DER', data: data.map(d => parseFloat(d.der).toFixed(2)) }]);
                tProf.updateOptions({ xaxis: { categories: labels } });
                tProf.updateSeries([{ name: 'ROA', data: data.map(d => parseFloat(d.roa).toFixed(2)) }, { name: 'ROE', data: data.map(d => parseFloat(d.roe).toFixed(2)) }, { name: 'NPM', data: data.map(d => parseFloat(d.npm).toFixed(2)) }]);

                if (res.trend_analysis) {
                    document.getElementById('status-liq-trend').innerHTML = res.trend_analysis.liq;
                    document.getElementById('status-sol-trend').innerHTML = res.trend_analysis.sol;
                    document.getElementById('status-prof-trend').innerHTML = res.trend_analysis.prof;
                }
            } catch (e) { console.error(e); }
        }

        async function fetchComparisonData() {
            const year = document.getElementById('compareYear').value;
            const quarter = document.getElementById('compareQuarter').value;
            const container = document.getElementById('comparison-analysis-container');
            container.innerHTML = '<div class="col-span-2 text-center text-gray-400 py-4"><i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat data terbaru...</div>';

            try {
                const url = `{{ route('api.bi.data') }}?type=comparison&year=${year}&quarter=${quarter}`;
                const res = await (await fetch(url)).json();
                const data = res.chart_data;

                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="col-span-2 text-center text-red-400 py-4">Data tidak ditemukan untuk periode ini.</div>';
                    cmpLiqChart.updateSeries([]); cmpSolvChart.updateSeries([]); cmpProfChart.updateSeries([]);
                    return;
                }
                const companies = data.map(d => d.kode_saham);
                cmpLiqChart.updateOptions({ xaxis: { categories: companies } });
                cmpLiqChart.updateSeries([{ name: 'CR', data: data.map(d => parseFloat(d.current_ratio || 0).toFixed(2)) }, { name: 'QR', data: data.map(d => parseFloat(d.quick_ratio || 0).toFixed(2)) }, { name: 'Cash', data: data.map(d => parseFloat(d.cash_ratio || 0).toFixed(2)) }]);
                cmpSolvChart.updateOptions({ xaxis: { categories: companies } });
                cmpSolvChart.updateSeries([ { name: 'DER', data: data.map(d => parseFloat(d.der || 0).toFixed(2)) } ]);
                cmpProfChart.updateOptions({ xaxis: { categories: companies } });
                cmpProfChart.updateSeries([{ name: 'ROA', data: data.map(d => parseFloat(d.roa || 0).toFixed(2)) }, { name: 'ROE', data: data.map(d => parseFloat(d.roe || 0).toFixed(2)) }, { name: 'NPM', data: data.map(d => parseFloat(d.npm || 0).toFixed(2)) }]);

                container.innerHTML = '';
                res.analysis_list.forEach(item => {
                    container.innerHTML += `<div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 hover:border-green-300 transition-colors shadow-sm"><h5 class="font-bold text-[#005832] text-md mb-3 border-b border-gray-200 pb-2">${item.company} <span class="text-[#007945]">(${item.code})</span></h5><div class="text-xs text-gray-700 leading-relaxed text-justify space-y-2">${item.text}</div></div>`;
                });
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="col-span-2 text-center text-red-500">Terjadi kesalahan memuat data.</div>';
            }
        }
        document.addEventListener('DOMContentLoaded', () => { refreshAllData(); });
    </script>
</body>
</html>
