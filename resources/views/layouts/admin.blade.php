<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/as.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/as.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Alamtri FinSight</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);
        }

        /* --- Custom Color Classes --- */
        .text-emerald-dark { color: #064e3b; }
        .text-emerald-medium { color: #0d9488; }
        .bg-emerald-light { background-color: #d1fae5; }
        .border-emerald-soft { border-color: rgba(52, 211, 153, 0.3); }

        /* Sidebar Styling */
        aside {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(167, 243, 208, 0.5);
        }

        /* Active Menu Item Styling */
        .nav-item-active {
            background: linear-gradient(to right, #ecfdf5, #f0fdfa);
            color: #0d9488;
            border-left: 3px solid #10b981;
        }

        .nav-item-inactive:hover {
            background-color: #f0fdfa;
            color: #059669;
        }
    </style>
</head>
<body class="text-slate-800">

    <div class="flex min-h-screen">

        <aside class="w-64 flex-shrink-0 hidden md:flex flex-col z-40 shadow-sm fixed h-full">

            <div class="p-2 mb-10      ">
                <div class="flex items-center ">
                    <img src="{{ asset('assets/img/alamtri_logo.png') }}"
                         alt="Logo Alamtri"
                         class="h-20 w-auto object-contain"><br>


                    <span class="text-xl font-bold text-emerald-dark tracking-tight">FinSight</span>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2">
                <p class="px-4 text-[10px] font-bold text-emerald-medium/70 uppercase tracking-widest mb-2">Menu Utama</p>

                {{-- DASHBOARD --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 p-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'nav-item-active shadow-sm' : 'text-slate-500 nav-item-inactive' }}">
                    <i class="fas fa-home w-5 {{ request()->routeIs('admin.dashboard') ? 'text-[#10b981]' : '' }}"></i>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>

                {{-- INPUT DATA --}}
                <a href="{{ route('admin.input') }}"
                   class="flex items-center gap-3 p-3 rounded-xl transition-all {{ request()->routeIs('admin.input') ? 'nav-item-active shadow-sm' : 'text-slate-500 nav-item-inactive' }}">
                    <i class="fas fa-plus-circle w-5 {{ request()->routeIs('admin.input') ? 'text-[#10b981]' : '' }}"></i>
                    <span class="text-sm font-semibold">Input Data</span>
                </a>

                {{-- RIWAYAT DATA --}}
                <a href="{{ route('admin.riwayat') }}"
                   class="flex items-center gap-3 p-3 rounded-xl transition-all {{ request()->routeIs('admin.riwayat') ? 'nav-item-active shadow-sm' : 'text-slate-500 nav-item-inactive' }}">
                    <i class="fas fa-history w-5 {{ request()->routeIs('admin.riwayat') ? 'text-[#10b981]' : '' }}"></i>
                    <span class="text-sm font-semibold">Riwayat Data</span>
                </a>

                {{-- REPORTING (MENU BARU) --}}
                <a href="{{ route('admin.laporan') }}"
                   class="flex items-center gap-3 p-3 rounded-xl transition-all {{ request()->routeIs('admin.laporan') ? 'nav-item-active shadow-sm' : 'text-slate-500 nav-item-inactive' }}">
                    <i class="fas fa-file-medical-alt w-5 {{ request()->routeIs('admin.laporan') ? 'text-[#10b981]' : '' }}"></i>
                    <span class="text-sm font-semibold">Laporan Keuangan</span>
                </a>
            </nav>

            <div class="px-4 pb-6 space-y-2 border-t border-emerald-soft pt-4">
                <a href="/" class="flex items-center gap-3 p-3 rounded-xl text-slate-500 hover:bg-[#f0fdfa] hover:text-[#0d9488] transition-all group">
                    <i class="fas fa-external-link-alt w-5 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Lihat Website</span>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 p-3 w-full text-red-500 hover:bg-red-50 rounded-xl transition-all text-sm font-bold group">
                        <i class="fas fa-power-off w-5 group-hover:rotate-12 transition-transform"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 overflow-hidden md:pl-64 transition-all duration-300">

            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-emerald-soft h-16 flex items-center justify-between px-8">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-emerald-medium/60 uppercase tracking-widest">
                        Admin Control Panel
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-[11px] font-bold text-emerald-dark leading-none uppercase">Administrator</p>
                        <div class="flex items-center justify-end gap-1 mt-1">
                            <span class="w-1.5 h-1.5 bg-[#10b981] rounded-full animate-pulse"></span>
                            <p class="text-[10px] text-slate-400">Online</p>
                        </div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=Admin&background=ecfdf5&color=0d9488" class="w-9 h-9 rounded-xl border border-emerald-soft shadow-sm">
                </div>
            </header>

            <div class="p-8 overflow-y-auto max-h-[calc(100vh-4rem)]">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
