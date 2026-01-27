<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator | Alamtri FinSight</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Custom Gradient Backgrounds - Hijau Adaro Style */
        .bg-gradient-login {
            /* Linear gradient dari Hijau Medium ke Hijau Tua */
            background: linear-gradient(135deg, #00753b 0%, #02562c 100%);
        }

        /* Gradient Text untuk Aksen (Kuning/Lime muda ke Putih) */
        .text-gradient-logo {
            background: linear-gradient(to right, #eaff00, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>

    <div class="bg-gradient-login font-[sans-serif] relative overflow-hidden">

        <div class="absolute bottom-0 right-0 w-96 h-96 bg-gradient-to-tl from-[#cddd2e]/30 to-transparent rounded-tl-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-64 h-48 bg-gradient-to-tl from-[#eaff00]/20 to-transparent rounded-tl-[80px] pointer-events-none"></div>

        <div class="min-h-screen flex flex-col items-center justify-center p-6 relative z-10">
            <div class="grid md:grid-cols-2 items-center gap-10 max-w-6xl w-full">

                <div class="max-w-lg max-md:mx-auto max-md:text-center flex flex-col justify-center">

                    <a href="javascript:void(0)" class="mb-4 inline-block self-start max-md:self-center -ml-14">
                        <div>
                            <img src="assets/img/alamtri_logo.png"
                                 alt="Logo Alamtri"
                                 class="h-50 w-40 object-contain block drop-shadow-md">
                        </div>
                    </a>

                    <h1 class="text-4xl md:text-5xl font-bold !leading-tight text-white tracking-tight">
                        Alamtri FinSight <br>
                        <span class="text-gradient-logo">Financial Dashboard</span>
                    </h1>

                    <p class="text-[15px] mt-6 text-white leading-relaxed opacity-90 max-w-md max-md:mx-auto">
                        Selamat datang di panel kontrol keuangan PT Alamtri Resource Indonesia Tbk.
                        Kelola data aset, likuiditas, dan profitabilitas perusahaan secara terpusat dan aman.
                    </p>

                    <div class="mt-10 flex items-center gap-4 text-[#e0f2fe] text-sm font-medium max-md:justify-center">
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-full border border-white/20 backdrop-blur-sm">
                            <i class="fas fa-shield-alt text-[#eaff00]"></i> Secure Access
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-full border border-white/20 backdrop-blur-sm">
                            <i class="fas fa-check-circle text-[#eaff00]"></i> Authorized Only
                        </div>
                    </div>
                </div>

                <form action="{{ route('login') }}" method="POST" class="bg-white rounded-[2rem] px-8 py-12 max-w-md md:ml-auto max-md:mx-auto w-full shadow-2xl shadow-[#004d26]/40 border border-white/50 relative overflow-hidden">
                    @csrf

                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#009b4d]/20 rounded-full blur-xl"></div>

                    <div class="mb-10 relative">
                        <h2 class="text-[#006a35] text-3xl font-bold mb-2">Masuk Akun</h2>
                        <p class="text-slate-500 text-sm">Silakan masukkan kredensial administrator Anda.</p>
                    </div>

                    <div class="space-y-5 relative">
                        <div>
                            <label class="text-[#006a35] text-xs font-bold uppercase mb-2 block tracking-wider">Email Perusahaan</label>
                            <div class="relative flex items-center group">
                                <input name="email" type="email" required
                                    class="w-full text-sm bg-slate-50 border border-slate-200 px-4 py-3.5 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-[#009b4d]/50 focus:border-[#009b4d] transition-all pl-11"
                                    placeholder="Masukkan Email Perusahaan" />
                                <i class="fas fa-envelope w-4 h-4 absolute left-4 text-slate-400 group-focus-within:text-[#009b4d] transition-colors"></i>
                            </div>
                        </div>

                        <div>
                            <label class="text-[#006a35] text-xs font-bold uppercase mb-2 block tracking-wider">Password</label>
                            <div class="relative flex items-center group">
                                <input name="password" type="password" required
                                    class="w-full text-sm bg-slate-50 border border-slate-200 px-4 py-3.5 rounded-xl outline-none focus:bg-white focus:ring-2 focus:ring-[#009b4d]/50 focus:border-[#009b4d] transition-all pl-11"
                                    placeholder="Masukkan password" />
                                <i class="fas fa-lock w-4 h-4 absolute left-4 text-slate-400 group-focus-within:text-[#009b4d] transition-colors"></i>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember" type="checkbox" class="h-4 w-4 text-[#009b4d] focus:ring-[#009b4d] border-gray-300 rounded cursor-pointer accent-[#009b4d]" />
                                <label for="remember-me" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">Ingat saya</label>
                            </div>
                            <div class="text-sm">
                                <a href="javascript:void(0);" class="text-[#008542] hover:text-[#005f2c] font-semibold hover:underline transition-colors">
                                    Lupa password?
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="w-full py-4 px-6 text-sm font-bold tracking-wide rounded-xl text-white bg-gradient-to-r from-[#009b4d] to-[#006a35] hover:from-[#008542] hover:to-[#004d26] focus:outline-none transition-all shadow-lg hover:shadow-[#009b4d]/40 flex items-center justify-center gap-2 transform active:scale-[0.99]">
                            Masuk Dashboard <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>

                    <div class="mt-8 text-center border-t border-slate-100 pt-6">
                        <p class="text-xs text-slate-400 leading-relaxed">
                            &copy; 2024 PT Alamtri Resource Indonesia Tbk. <br> Protected by Enterprise Security.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
