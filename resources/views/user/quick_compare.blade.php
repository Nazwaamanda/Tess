<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Benchmarking | Alamtri FinSight</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/as.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/as.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; color: #1f2937; }
        .bg-gradient-header { background: linear-gradient(90deg, #007945 0%, #004d2c 100%); }
        .card-white { background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 1.5rem; }
        .form-input { width: 100%; padding: 0.6rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; font-size: 0.875rem; outline: none; }
        .form-input:focus { border-color: #007945; box-shadow: 0 0 0 2px rgba(0, 121, 69, 0.1); }
        .tab-btn { transition: all 0.3s ease; border-radius: 0.75rem; cursor: pointer; }
        .status-badge { font-size: 9px; font-weight: 800; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 10px; }
    </style>
</head>
<body class="pb-20">

    <header class="bg-gradient-header pt-16 pb-32 px-6 text-center text-white relative">
        <a href="{{ url('/') }}" class="absolute top-6 left-6 flex items-center gap-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl backdrop-blur-md transition-all text-xs font-bold border border-white/30">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-extrabold mb-3 tracking-tight">Smart Benchmarking Tool</h1>
            <p class="text-green-100 opacity-90 font-medium">Bandingkan performa finansial berdasarkan standar rasio korporasi ADMR.</p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto -mt-20 px-4 sm:px-6 lg:px-8">
        {{-- Filter Section --}}
        <div class="card-white p-4 mb-6 bg-white/90 backdrop-blur border-l-4 border-[#007945] shadow-lg">
            <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Tahun</label>
                    <select name="tahun" class="form-input !w-40 font-bold text-[#005832]">
                        @foreach($availablePeriods->unique('id_tahun') as $p)
                            <option value="{{ $p->id_tahun }}" {{ $selectedIdTahun == $p->id_tahun ? 'selected' : '' }}>
                                Tahun {{ $p->tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Kuartal</label>
                    <select name="kuartal" class="form-input !w-40 font-bold text-[#005832]">
                        @php $availableQuarters = $availablePeriods->where('id_tahun', $selectedIdTahun)->unique('id_kuartal'); @endphp
                        @foreach($availableQuarters as $p)
                            <option value="{{ $p->id_kuartal }}" {{ $selectedIdKuartal == $p->id_kuartal ? 'selected' : '' }}>
                                {{ $p->nama_kuartal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-[#007945] text-white px-6 py-2.5 rounded-xl text-xs font-bold hover:bg-[#005832] transition-all h-[42px]">
                    <i class="fas fa-sync-alt mr-1"></i> Update Referensi
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Input Form --}}
            <div class="lg:col-span-4 space-y-4">
                <div class="card-white p-6 shadow-xl border-t-4 border-[#007945]">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="font-bold text-[#005832] flex items-center gap-2 text-sm"><i class="fas fa-edit"></i> INPUT DATA</h2>
                        <button onclick="addNewCompany()" class="text-[10px] bg-green-100 text-[#007945] px-3 py-1.5 rounded-lg font-bold uppercase">Tambah</button>
                    </div>

                    <div id="companyContainer" class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scroll">
                        <div class="input-card bg-gray-50 p-5 rounded-2xl border border-gray-100 relative" id="card-1">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="bg-[#007945] text-white w-6 h-6 rounded flex items-center justify-center text-[10px] font-bold">1</span>
                                <input type="text" placeholder="KODE SAHAM" class="form-input font-bold uppercase company-code text-[#005832]">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="number" class="form-input val-al" placeholder="Aset Lancar">
                                <input type="number" class="form-input val-inv" placeholder="Persediaan">
                                <input type="number" class="form-input val-kas" placeholder="Kas">
                                <input type="number" class="form-input val-ul" placeholder="Utang Lancar">
                                <input type="number" class="form-input val-tu" placeholder="Total Utang">
                                <input type="number" class="form-input val-te" placeholder="Total Ekuitas">
                                <input type="number" class="form-input val-ta" placeholder="Total Aset">
                                <input type="number" class="form-input val-rev" placeholder="Pendapatan">
                                <input type="number" class="form-input col-span-2 val-laba" placeholder="Laba Bersih">
                            </div>
                        </div>
                    </div>

                    <button onclick="calculateComparison()" class="w-full mt-6 bg-[#007945] hover:bg-[#005832] text-white font-bold py-4 rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-bolt"></i> PROSES BENCHMARKING
                    </button>

                    <a href="{{ url('/') }}" class="block text-center mt-4 text-xs font-bold text-gray-400 hover:text-[#007945] transition-colors">
                        <i class="fas fa-home mr-1"></i> Ke Halaman Utama
                    </a>
                </div>
            </div>

            {{-- Visualisasi & Analisis --}}
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-200 flex gap-2">
                    <button onclick="switchTab('liq')" id="tab-liq" class="tab-btn flex-1 py-3 font-bold text-xs uppercase bg-[#007945] text-white shadow-md">Likuiditas</button>
                    <button onclick="switchTab('sol')" id="tab-sol" class="tab-btn flex-1 py-3 font-bold text-xs uppercase text-gray-500">Solvabilitas</button>
                    <button onclick="switchTab('prof')" id="tab-prof" class="tab-btn flex-1 py-3 font-bold text-xs uppercase text-gray-500">Profitabilitas</button>
                </div>

                <div class="card-white p-8 shadow-xl min-h-[500px]">
                    <h3 id="chartTitle" class="text-xs font-bold text-[#005832] uppercase tracking-widest mb-6 border-b pb-4">Perbandingan Likuiditas</h3>
                    <div id="compareChart"></div>

                    <div class="mt-10 border-t border-gray-100 pt-8">
                        <h4 class="font-bold text-[#005832] mb-6 flex items-center text-md">
                            <span class="bg-yellow-100 p-2 rounded-lg mr-3"><i class="fas fa-lightbulb text-yellow-600"></i></span>
                            Status Kondisi Perusahaan (Benchmarking)
                        </h4>
                        <div id="insightContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Hasil Analisis --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let companyCount = 1;
        let activeTab = 'liq';
        let chart;
        const colors = { primary: '#007945', secondary: '#cddd2e', dark: '#005832' };

        const admrData = {
            code: "{{ $admrRef['code'] }}",
            cr: {{ $admrRef['cr'] }}, qr: {{ $admrRef['qr'] }}, cash: {{ $admrRef['cash'] }},
            der: {{ $admrRef['der'] }}, roa: {{ $admrRef['roa'] }}, roe: {{ $admrRef['roe'] }}, npm: {{ $admrRef['npm'] }}
        };

        function initChart() {
            const options = {
                series: [],
                chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', dataLabels: { position: 'top' } } },
                xaxis: { categories: [], labels: { style: { fontWeight: 700 } } },
                colors: [colors.dark, colors.primary, colors.secondary, '#84cc16'],
                dataLabels: { enabled: true, offsetY: -20, style: { fontSize: '10px', colors: ['#334155'] } },
                legend: { position: 'top' }
            };
            chart = new ApexCharts(document.querySelector("#compareChart"), options);
            chart.render();
        }

        function calculateComparison() {
            const cards = document.querySelectorAll('.input-card');
            let results = [admrData];

            cards.forEach(card => {
                const getVal = (cls) => parseFloat(card.querySelector(cls).value) || 0;
                const al = getVal('.val-al'), inv = getVal('.val-inv'), kas = getVal('.val-kas');
                const ul = getVal('.val-ul'), tu = getVal('.val-tu'), te = getVal('.val-te');
                const ta = getVal('.val-ta'), rev = getVal('.val-rev'), laba = getVal('.val-laba');

                results.push({
                    code: card.querySelector('.company-code').value.toUpperCase() || 'ENTITAS',
                    cr: ul ? (al / ul) : 0, qr: ul ? ((al - inv) / ul) : 0, cash: ul ? (kas / ul) : 0,
                    der: te ? (tu / te) : 0, roa: ta ? (laba / ta * 100) : 0, roe: te ? (laba / te * 100) : 0, npm: rev ? (laba / rev * 100) : 0
                });
            });
            updateVisuals(results);
        }

        function renderStatusItem(label, value, status, suffix = '') {
            const isPositive = ["Likuid", "Sangat Baik", "Baik", "Sangat Likuid", "Sangat Sehat", "Efisien", "Margin Baik"].includes(status);
            const badgeClass = isPositive ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100';

            return `
                <div class="flex justify-between items-center py-1 border-b border-gray-50 last:border-0">
                    <span class="text-[11px] text-gray-600">${label}</span>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-[#005832] text-xs">${value.toFixed(2)}${suffix}</span>
                        <span class="status-badge border ${badgeClass}" style="font-size: 8px;">${status}</span>
                    </div>
                </div>`;
        }

        function updateVisuals(data) {
            const categories = data.map(d => d.code);
            let series = [];

            if (activeTab === 'liq') {
                series = [
                    { name: 'Current', data: data.map(d => d.cr.toFixed(2)) },
                    { name: 'Quick', data: data.map(d => d.qr.toFixed(2)) },
                    { name: 'Cash', data: data.map(d => d.cash.toFixed(2)) }
                ];
            } else if (activeTab === 'sol') {
                series = [{ name: 'DER', data: data.map(d => d.der.toFixed(2)) }];
            } else {
                series = [
                    { name: 'ROA (%)', data: data.map(d => d.roa.toFixed(2)) },
                    { name: 'ROE (%)', data: data.map(d => d.roe.toFixed(2)) },
                    { name: 'NPM (%)', data: data.map(d => d.npm.toFixed(2)) }
                ];
            }

            chart.updateOptions({ xaxis: { categories: categories } });
            chart.updateSeries(series);

            const container = document.getElementById('insightContainer');
            container.innerHTML = data.slice(1).map(d => {
                const statusCR = d.cr >= 2.0 ? "Likuid" : (d.cr >= 1.0 ? "Waspada" : "Illikuid");
                const statusQR = d.qr >= 1.5 ? "Sangat Baik" : (d.qr >= 1.0 ? "Baik" : "Kurang Baik");
                const statusDER = d.der <= 1.0 ? "Sangat Sehat" : (d.der <= 1.5 ? "Waspada" : "Berisiko");
                const statusROE = d.roe >= 15 ? "Sangat Baik" : "Rendah";
                const statusNPM = d.npm >= 10 ? "Margin Baik" : "Margin Rendah";

                let isBetter = false;
                if(activeTab === 'liq') isBetter = d.cr > admrData.cr;
                if(activeTab === 'sol') isBetter = d.der < admrData.der;
                if(activeTab === 'prof') isBetter = d.roe > admrData.roe;

                return `
                    <div class="card-white p-5 border-l-4 border-[#007945] bg-white shadow-sm">
                        <div class="flex justify-between items-start mb-4 border-b pb-2">
                            <h5 class="font-bold text-[#005832] text-sm uppercase">${d.code}</h5>
                            <span class="status-badge ${isBetter ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">
                                ${isBetter ? 'Unggul vs ADMR' : 'Dibawah ADMR'}
                            </span>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Likuiditas</p>
                                ${renderStatusItem('Current Ratio', d.cr, statusCR)}
                                ${renderStatusItem('Quick Ratio', d.qr, statusQR)}
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Solvabilitas</p>
                                ${renderStatusItem('DER', d.der, statusDER)}
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Profitabilitas</p>
                                ${renderStatusItem('ROE', d.roe, statusROE, '%')}
                                ${renderStatusItem('NPM', d.npm, statusNPM, '%')}
                            </div>
                        </div>
                    </div>`;
            }).join('');
        }

        function switchTab(tab) {
            activeTab = tab;
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('bg-[#007945]', 'text-white', 'shadow-md');
                b.classList.add('text-gray-500');
            });
            document.getElementById(`tab-${tab}`).classList.add('bg-[#007945]', 'text-white', 'shadow-md');
            document.getElementById('chartTitle').innerText = 'Perbandingan ' + (tab === 'liq' ? 'Likuiditas' : tab === 'sol' ? 'Solvabilitas' : 'Profitabilitas');
            calculateComparison();
        }

        function addNewCompany() {
            if (companyCount >= 5) return alert('Maksimal 5 perusahaan.');
            companyCount++;
            const container = document.getElementById('companyContainer');
            const newCard = document.getElementById('card-1').cloneNode(true);
            newCard.id = `card-${companyCount}`;
            newCard.querySelector('span').innerText = companyCount;
            newCard.querySelectorAll('input').forEach(i => i.value = '');
            container.appendChild(newCard);
        }

        document.addEventListener('DOMContentLoaded', initChart);
    </script>
</body>
</html>
