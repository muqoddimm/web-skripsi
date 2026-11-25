<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RJ GRESIK ENGINEERING</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#121316] text-[#18181B] m-0 p-0 relative selection:bg-[#C5A070] selection:text-white flex items-center justify-center overflow-x-hidden">

    <!-- Container Utama Fullscreen Terpadu (Tanpa Sekat Terpisah) -->
    <div class="relative min-h-screen w-full flex items-center justify-center p-4 sm:p-6 lg:p-10">
        
        <!-- Background Mesin Workshop RJ GRESIK (Gradasi Halus Terpadu) -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img src="{{ asset('img/bg.png') }}"
                 onerror="this.onerror=null; this.src='{{ asset('img/workshop-lineup.jpg') }}';"
                 alt="RJ GRESIK Roasting Workshop"
                 class="w-full h-full object-cover object-center filter contrast-115 brightness-95">
            
            <!-- Vignette & Gradasi Sinematik: Gelap di kiri untuk teks, transparan di tengah memperlihatkan mesin, dan aksen hangat di kanan -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#0C0D0F]/90 via-[#101114]/75 to-[#16171B]/60"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#0C0D0F]/70 via-transparent to-[#0C0D0F]/85"></div>
        </div>

        <!-- Ornamen Pita Sudut Kiri Atas (Sesuai Desain Acuan) -->
        <div class="absolute top-0 left-0 w-44 sm:w-64 h-44 sm:h-64 pointer-events-none z-10 overflow-hidden">
            <svg viewBox="0 0 200 200" class="w-full h-full" fill="none">
                <defs>
                    <linearGradient id="carbonRibbonTop" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1F2024" />
                        <stop offset="60%" stop-color="#141518" />
                        <stop offset="100%" stop-color="#0B0C0E" />
                    </linearGradient>
                </defs>
                <polygon points="0,0 150,0 0,150" fill="url(#carbonRibbonTop)" />
                <polygon points="150,0 156,0 0,156 0,150" fill="#4B4D54" opacity="0.6" />
                <polygon points="168,0 173,0 0,173 0,168" fill="#C5A070" opacity="0.35" />
            </svg>
        </div>

        <!-- Ornamen Pita Emas & Sampanye Sudut Kanan Bawah (Sesuai Desain Acuan) -->
        <div class="absolute bottom-0 right-0 w-72 sm:w-96 lg:w-[480px] h-72 sm:h-96 lg:h-[480px] pointer-events-none z-0 overflow-hidden hidden sm:block">
            <svg viewBox="0 0 500 500" class="w-full h-full" fill="none">
                <defs>
                    <linearGradient id="goldRibbonBottom1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#EADDCF" stop-opacity="0.9" />
                        <stop offset="50%" stop-color="#C5A070" stop-opacity="0.95" />
                        <stop offset="100%" stop-color="#8C5E3C" stop-opacity="0.9" />
                    </linearGradient>
                    <linearGradient id="creamRibbonBottom" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.95" />
                        <stop offset="50%" stop-color="#FAF8F5" stop-opacity="0.95" />
                        <stop offset="100%" stop-color="#E5DEC3" stop-opacity="0.9" />
                    </linearGradient>
                    <linearGradient id="bronzeRibbonBottom" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#9C6B45" stop-opacity="0.85" />
                        <stop offset="100%" stop-color="#5A3A1F" stop-opacity="0.9" />
                    </linearGradient>
                </defs>
                <polygon points="120,500 500,120 500,190 190,500" fill="url(#bronzeRibbonBottom)" />
                <polygon points="190,500 500,190 500,280 280,500" fill="url(#goldRibbonBottom1)" />
                <polygon points="280,500 500,280 500,390 390,500" fill="url(#creamRibbonBottom)" />
                <polygon points="390,500 500,390 500,500 500,500" fill="url(#goldRibbonBottom1)" />
            </svg>
        </div>

        <!-- Grid 2 Kolom Seimbang (Sesuai Tata Letak Desain Acuan) -->
        <div class="relative z-10 w-full max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- KOLOM KIRI: Brand Logo, Headline & 3 Pilar Keunggulan -->
            <div class="lg:col-span-7 space-y-6 sm:space-y-8 pr-0 lg:pr-4">
                
                <!-- Logo Brand 3D RJ GRESIK ENGINEERING (Sesuai Desain Acuan) -->
                <div>
                    <img src="{{ asset('img/logo-rj-gresik-3d.png') }}"
                         onerror="this.onerror=null; this.src='{{ asset('img/logo.png') }}';"
                         alt="RJ GRESIK ENGINEERING"
                         class="h-12 sm:h-14 w-auto object-contain filter drop-shadow-md">
                </div>

                <!-- Headline & Deskripsi -->
                <div class="space-y-3">
                    <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black text-white leading-[1.15] tracking-tight">
                        Teknologi Presisi<br>
                        untuk Setiap <span class="text-[#C5A070]">Proses Roasting.</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-[#D1D5DB] leading-relaxed max-w-md font-normal">
                        Inovasi dan kualitas terbaik untuk mendukung produktivitas industri Anda.
                    </p>
                </div>

                <!-- 3 Pilar Keunggulan Horizontal (Sesuai Desain Acuan) -->
                <div class="flex flex-wrap items-center gap-4 sm:gap-6 pt-1">
                    <!-- 1. Aman & Terpercaya -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full border border-[#C5A070]/60 bg-black/50 backdrop-blur-xs flex items-center justify-center text-[#C5A070] shrink-0 shadow-inner">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="text-xs leading-tight">
                            <strong class="text-white block font-bold text-xs">Aman</strong>
                            <span class="text-[#9CA3AF] text-[11px]">&amp; Terpercaya</span>
                        </div>
                    </div>

                    <div class="h-8 w-[1px] bg-white/20 hidden sm:block"></div>

                    <!-- 2. Presisi Tinggi -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full border border-[#C5A070]/60 bg-black/50 backdrop-blur-xs flex items-center justify-center text-[#C5A070] shrink-0 shadow-inner">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="text-xs leading-tight">
                            <strong class="text-white block font-bold text-xs">Presisi</strong>
                            <span class="text-[#9CA3AF] text-[11px]">Tinggi</span>
                        </div>
                    </div>

                    <div class="h-8 w-[1px] bg-white/20 hidden sm:block"></div>

                    <!-- 3. Dukungan Profesional -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full border border-[#C5A070]/60 bg-black/50 backdrop-blur-xs flex items-center justify-center text-[#C5A070] shrink-0 shadow-inner">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 00-14 0m14 0a2 2 0 012 2v3a2 2 0 01-2 2h-1a1 1 0 01-1-1v-4a1 1 0 011-1h1m-14 0a2 2 0 00-2 2v3a2 2 0 002 2h1a1 1 0 001-1v-4a1 1 0 00-1-1H5z" />
                            </svg>
                        </div>
                        <div class="text-xs leading-tight">
                            <strong class="text-white block font-bold text-xs">Dukungan</strong>
                            <span class="text-[#9CA3AF] text-[11px]">Profesional</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: Floating Card Login (Sesuai Desain Acuan) -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="w-full max-w-[390px] bg-white/95 backdrop-blur-md rounded-[28px] p-6 sm:p-7 shadow-2xl border border-white/60 space-y-5 relative z-10">
                    
                    <!-- Tombol Navigasi Atas Card -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#57534E] hover:text-[#18181B] transition py-1 px-3 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] border border-[#E5E7EB]">
                            <span>&larr;</span>
                            <span>Kembali ke Beranda</span>
                        </a>
                        <a href="{{ route('catalog') }}" class="text-xs font-bold text-[#8C5E3C] hover:text-[#744A29] transition">
                            Katalog Mesin &rarr;
                        </a>
                    </div>

                    <!-- Header Form Login -->
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black text-[#18181B] tracking-tight">Selamat Datang</h2>
                        <p class="text-[11px] text-[#6B7280] leading-relaxed">
                            Login akun Anda untuk mencari serta konsultasi kebutuhan bisnis yang sesuai.
                        </p>
                    </div>

                    @if(session('info'))
                        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-3.5 py-2.5 rounded-xl text-xs leading-relaxed flex items-start gap-2 shadow-xs">
                            <svg class="w-4 h-4 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('info') }}</span>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3.5 py-2.5 rounded-xl text-xs leading-relaxed flex items-start gap-2 shadow-xs">
                            <svg class="w-4 h-4 shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3.5 py-2.5 rounded-xl text-xs leading-relaxed flex items-start gap-2 shadow-xs">
                            <svg class="w-4 h-4 shrink-0 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('login.submit') }}" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-[#18181B] mb-1">Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <input type="email" name="email" placeholder="Masukkan alamat email Anda" required
                                    class="w-full bg-[#F3F4F6] border border-[#E5E7EB] rounded-xl pl-10 pr-4 py-2.5 sm:py-3 text-xs sm:text-sm text-[#18181B] placeholder:text-[#9CA3AF] focus:outline-none focus:border-[#8C5E3C] focus:bg-white focus:ring-1 focus:ring-[#8C5E3C] transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#18181B] mb-1">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input type="password" id="password-field" name="password" placeholder="Masukkan password Anda" required
                                    class="w-full bg-[#F3F4F6] border border-[#E5E7EB] rounded-xl pl-10 pr-10 py-2.5 sm:py-3 text-xs sm:text-sm text-[#18181B] placeholder:text-[#9CA3AF] focus:outline-none focus:border-[#8C5E3C] focus:bg-white focus:ring-1 focus:ring-[#8C5E3C] transition">
                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9CA3AF] hover:text-[#18181B] focus:outline-none cursor-pointer">
                                    <svg id="eye-icon-closed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    <svg id="eye-icon-open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-[#18181B] hover:bg-black text-white font-bold py-3 px-4 rounded-xl text-xs sm:text-sm transition shadow-md flex items-center justify-center gap-2 cursor-pointer mt-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Login ke Akun</span>
                        </button>
                    </form>

                    <div class="relative flex py-0.5 items-center">
                        <div class="flex-grow border-t border-[#E5E7EB]"></div>
                        <span class="flex-shrink mx-3 text-[10px] text-[#9CA3AF] uppercase tracking-wider font-bold">Atau</span>
                        <div class="flex-grow border-t border-[#E5E7EB]"></div>
                    </div>

                    <!-- Tombol Google OAuth 2.0 -->
                    <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-2.5 bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] text-[#18181B] font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-2xs cursor-pointer">
                        <svg class="h-4 w-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                        <span>Masuk dengan Google</span>
                    </a>

                    <p class="text-center text-[11px] text-[#6B7280]">
                        Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-[#8C5E3C] hover:underline">Daftar sekarang (Register)</a>
                    </p>
                </div>
            </div>

        </div>

    </div>

    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordField = document.getElementById('password-field');
        const eyeIconClosed = document.getElementById('eye-icon-closed');
        const eyeIconOpen = document.getElementById('eye-icon-open');
        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            eyeIconClosed.classList.toggle('hidden');
            eyeIconOpen.classList.toggle('hidden');
        });
    </script>
</body>
</html>
