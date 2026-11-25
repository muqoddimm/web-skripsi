<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RJ TEKNIK - Commercial Coffee Roaster Engineering')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F7F5F0;
            color: #18181B;
        }
        .font-serif-coffee {
            font-family: 'Playfair Display', serif;
        }
        /* Custom scrollbar elegan coffee brown */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #F7F5F0;
        }
        ::-webkit-scrollbar-thumb {
            background: #D6CEBE;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #8C5E3C;
        }
        /* Animasi Maskot Robot 3D Menyapa (Waving & Floating) */
        @keyframes mascotGreeting {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-5px) rotate(-2.5deg);
            }
            50% {
                transform: translateY(-9px) rotate(2deg);
            }
            75% {
                transform: translateY(-3px) rotate(-1deg);
            }
        }
        @keyframes mascotWave {
            0%, 100% {
                transform: rotate(0deg);
            }
            20% {
                transform: rotate(-7deg) translateY(-3px);
            }
            40% {
                transform: rotate(7deg) translateY(-6px);
            }
            60% {
                transform: rotate(-5deg) translateY(-3px);
            }
            80% {
                transform: rotate(5deg) translateY(-5px);
            }
        }
        .mascot-greeting-anim {
            animation: mascotGreeting 3.5s ease-in-out infinite;
            transform-origin: bottom center;
        }
        .mascot-greeting-anim:hover {
            animation: mascotWave 1.2s ease-in-out infinite;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col antialiased bg-[#F7F5F0] text-[#18181B] selection:bg-[#8C5E3C] selection:text-white overflow-x-hidden overflow-y-auto">

    <!-- Navbar Atas: Putih Bersih dengan Aksen Hitam & Coklat (Sesuai Desain Canva) -->
    <header class="bg-white text-[#18181B] border-b border-[#E8E4DE] sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            <!-- Logo & Brand RJ TEKNIK -->
            <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('img/logo.png') }}" alt="Logo RJ TEKNIK" class="w-11 h-11 object-contain rounded-full shadow-xs group-hover:scale-105 transition-transform duration-300">
                <div>
                    <span class="text-xl font-black tracking-tight text-[#18181B] block group-hover:text-[#8C5E3C] transition-colors">RJ TEKNIK</span>
                    <span class="text-[10px] font-semibold text-[#8C5E3C] tracking-widest uppercase block -mt-1">Coffee Roaster Engineering</span>
                </div>
            </a>

            <!-- Menu Navigasi -->
            <div class="flex items-center space-x-2 sm:space-x-5">
                <nav class="hidden md:flex items-center space-x-1.5 text-sm font-medium">
                    <a href="{{ route('home') }}"
                        class="px-4 py-2 rounded-xl transition {{ request()->routeIs('home') && !request()->has('kontak') ? 'bg-[#F3EFEA] text-[#18181B] font-extrabold shadow-2xs' : 'text-[#57534E] hover:text-[#18181B] hover:bg-[#F3EFEA]/60' }}">
                        Beranda
                    </a>
                    <a href="{{ route('catalog') }}"
                        class="px-4 py-2 rounded-xl transition {{ request()->routeIs('catalog') ? 'bg-[#F3EFEA] text-[#18181B] font-extrabold shadow-2xs' : 'text-[#57534E] hover:text-[#18181B] hover:bg-[#F3EFEA]/60' }}">
                        Katalog
                    </a>
                    <a href="{{ route('chatbot.view') }}"
                        class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('chatbot.view') ? 'bg-[#F3EFEA] text-[#8C5E3C] font-extrabold shadow-2xs' : 'text-[#57534E] hover:text-[#18181B] hover:bg-[#F3EFEA]/60' }}">
                        <span class="w-2 h-2 rounded-full bg-[#8C5E3C] animate-pulse"></span>
                        AI CS
                    </a>
                    <a href="{{ route('home') }}#kontak"
                        class="px-4 py-2 rounded-xl transition text-[#57534E] hover:text-[#18181B] hover:bg-[#F3EFEA]/60">
                        Tentang Kami
                    </a>

                    @if(session('user_name') || Auth::check())
                    <a href="{{ route('order.track') }}"
                        class="px-4 py-2 rounded-xl transition {{ request()->routeIs('order.track') || request()->routeIs('order.status') ? 'bg-[#F3EFEA] text-[#18181B] font-extrabold shadow-2xs' : 'text-[#57534E] hover:text-[#18181B] hover:bg-[#F3EFEA]/60' }}">
                        Status Pesanan
                    </a>
                    @endif

                    @if((session('user_role') === 'admin') || (Auth::check() && Auth::user()->role === 'admin'))
                    <a href="{{ route('admin.dashboard') }}"
                        class="px-4 py-2 rounded-xl bg-[#8C5E3C]/10 text-[#8C5E3C] border border-[#8C5E3C]/30 hover:bg-[#8C5E3C]/20 font-bold transition">
                        Dashboard Admin
                    </a>
                    @endif
                </nav>

                @if(session('user_name') || Auth::check())
                <!-- Profil & Logout (Desktop) -->
                <div class="hidden md:flex items-center gap-3 pl-3 border-l border-[#E8E4DE]">
                    <a href="{{ route('profile') }}"
                        class="text-[#57534E] hover:text-[#18181B] transition flex items-center gap-2 bg-[#F3EFEA] px-3.5 py-1.5 rounded-full border border-[#E8E4DE]"
                        title="Profil: {{ session('user_name', Auth::user()->name ?? 'User') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#8C5E3C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-xs font-bold max-w-[120px] truncate text-[#18181B]">{{ session('user_name', Auth::user()->name ?? 'User') }}</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-3.5 py-1.5 rounded-full text-xs font-bold transition cursor-pointer" title="Logout">
                            Logout
                        </button>
                    </form>
                </div>
                @else
                <div class="hidden md:flex items-center gap-2">
                    <a href="{{ route('login') }}" class="bg-[#18181B] hover:bg-[#2A2B2E] text-white font-bold px-5 py-2.5 rounded-full text-xs transition shadow-sm cursor-pointer">
                        Login
                    </a>
                </div>
                @endif

                <!-- Hamburger Button untuk Mode Ponsel / Android (Muncul Hanya di Layar HP) -->
                <button type="button" onclick="toggleMobileNavDrawer()" id="btn-mobile-nav"
                    class="md:hidden p-2 rounded-xl text-[#57534E] hover:text-[#18181B] hover:bg-[#F3EFEA] border border-[#E8E4DE] transition cursor-pointer flex items-center justify-center focus:outline-none"
                    aria-label="Menu Navigasi Mobile">
                    <svg id="icon-nav-open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="icon-nav-close" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>

        <!-- Drawer Menu Navigasi Interaktif Mode Android / Ponsel (Sesuai Desain Gambar 2 dengan Latar Putih) -->
        <div id="mobile-nav-drawer" class="hidden md:hidden border-t border-[#E8E4DE] bg-white px-5 pt-3.5 pb-6 shadow-xl space-y-2">
            <p class="px-3 text-[10px] font-bold text-[#78716C] tracking-wider uppercase mb-1">MENU NAVIGASI</p>

            <div class="space-y-1">
                <!-- 1. Beranda -->
                @if(request()->routeIs('home') && !request()->has('kontak'))
                <a href="{{ route('home') }}" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                    <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                    <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </a>
                @else
                <a href="{{ route('home') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </a>
                @endif

                <!-- 2. Katalog Mesin -->
                @if(request()->routeIs('catalog'))
                <a href="{{ route('catalog') }}" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                    <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                    <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Katalog Mesin</span>
                </a>
                @else
                <a href="{{ route('catalog') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Katalog Mesin</span>
                </a>
                @endif

                <!-- 3. AI CS (Konsultasi Mesin) -->
                @if(request()->routeIs('chatbot.view'))
                <a href="{{ route('chatbot.view') }}" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                    <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                    <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    <span>AI CS (Konsultasi Mesin)</span>
                </a>
                @else
                <a href="{{ route('chatbot.view') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    <span>AI CS (Konsultasi Mesin)</span>
                </a>
                @endif

                <!-- 4. Tentang Kami -->
                <a href="{{ route('home') }}#kontak"
                    onclick="toggleMobileNavDrawer()"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Tentang Kami</span>
                </a>

                <!-- 5. Status Pesanan (Jika Login) -->
                @if(session('user_name') || Auth::check())
                @if(request()->routeIs('order.track') || request()->routeIs('order.status'))
                <a href="{{ route('order.track') }}" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                    <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                    <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Status Pesanan & Perakitan</span>
                </a>
                @else
                <a href="{{ route('order.track') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Status Pesanan & Perakitan</span>
                </a>
                @endif
                @endif

                <!-- 6. Dashboard Admin (Jika Admin) -->
                @if((session('user_role') === 'admin') || (Auth::check() && Auth::user()->role === 'admin'))
                <a href="{{ route('admin.dashboard') }}"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold bg-[#8C5E3C]/10 text-[#8C5E3C] border border-[#8C5E3C]/30 hover:bg-[#8C5E3C]/20 transition mt-1">
                    <svg class="w-4 h-4 shrink-0 text-[#8C5E3C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                    </svg>
                    <span>Dashboard Admin</span>
                </a>
                @endif
            </div>

            <!-- Auth & Tombol Login Pengguna (Gambar 2 dengan Latar Putih) -->
            <div class="pt-3 mt-2 border-t border-[#E8E4DE] space-y-2">
                @if(session('user_name') || Auth::check())
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('profile') }}"
                        class="flex-1 flex items-center gap-2 text-xs font-bold text-[#18181B] bg-[#F7F5F0] hover:bg-[#E8E4DE] px-3.5 py-2.5 rounded-2xl border border-[#E8E4DE] transition truncate">
                        <span>👤</span>
                        <span class="truncate">{{ session('user_name', Auth::user()->name ?? 'Akun Saya') }}</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit"
                            class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-4 py-2.5 rounded-2xl text-xs font-bold transition cursor-pointer">
                            Logout
                        </button>
                    </form>
                </div>
                @else
                <a href="{{ route('login') }}"
                    class="w-full flex items-center justify-center gap-2.5 bg-[#FF3B30] hover:bg-[#E02E24] text-white font-bold py-3 px-5 rounded-2xl text-xs sm:text-sm transition shadow-sm cursor-pointer active:scale-98">
                    <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l4-4-4-4m4 4H3m14-8v16" />
                    </svg>
                    <span>Login Pengguna</span>
                </a>
                @endif

                <p class="text-center text-[10.5px] font-medium text-[#78716C] pt-1">
                    RJ TEKNIK • Coffee Roaster Engineering
                </p>
            </div>
        </div>
    </header>

    <!-- Script Interaktif Navigasi Mobile (Android / Layar Ponsel) -->
    <script>
        window.toggleMobileNavDrawer = function() {
            var drawer = document.getElementById('mobile-nav-drawer');
            var openIcon = document.getElementById('icon-nav-open');
            var closeIcon = document.getElementById('icon-nav-close');
            if (!drawer) {
                console.error("mobile-nav-drawer element not found");
                return;
            }
            var isHidden = drawer.classList.contains('hidden');
            if (isHidden) {
                drawer.classList.remove('hidden');
                if (openIcon) openIcon.classList.add('hidden');
                if (closeIcon) closeIcon.classList.remove('hidden');
            } else {
                drawer.classList.add('hidden');
                if (openIcon) openIcon.classList.remove('hidden');
                if (closeIcon) closeIcon.classList.add('hidden');
            }
        };
    </script>

    <!-- Notifikasi Pesan Sukses / Error -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full">
        <div class="bg-white border border-emerald-300 text-emerald-900 px-5 py-3.5 rounded-2xl text-sm font-medium flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-emerald-600 text-lg">✓</span>
                <span class="text-[#18181B]">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-[#57534E] hover:text-[#18181B] text-xs font-bold cursor-pointer">✕</button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full">
        <div class="bg-white border border-rose-300 text-rose-900 px-5 py-3.5 rounded-2xl text-sm font-medium flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-rose-600 text-lg">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-xs font-bold cursor-pointer">✕</button>
        </div>
    </div>
    @endif

    <!-- Konten Halaman -->
    <main class="flex-grow">
        @yield('content')
    </main>

    @if(!request()->routeIs('chatbot.view'))
    <!-- Floating Chatbot Action Button (Sudut Kanan Bawah - Ditampilkan Hanya di Luar Halaman AI CS) -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-2">
        <!-- Balon Sapaan Proaktif AI CS dengan Maskot 3D Bergerak (Sesuai Desain Mockup) -->
        <div id="proactive-chat-bubble" class="relative bg-white border-2 border-[#8C5E3C] text-[#18181B] rounded-[28px] p-4 sm:p-5 shadow-2xl max-w-sm sm:max-w-md w-[calc(100vw-3rem)] sm:w-[420px] transition-all duration-300 select-none">
            <!-- Titik Coklat Kiri Atas Sesuai Mockup -->
            <div class="absolute top-4 left-4 w-3.5 h-3.5 rounded-full bg-[#8C5E3C] shadow-xs"></div>

            <!-- Ekor Balon Chat Menunjuk ke Tombol Tanya Kami -->
            <div class="absolute -bottom-2 right-14 w-4 h-4 bg-white border-r-2 border-b-2 border-[#8C5E3C] rotate-45"></div>

            <!-- Tombol Tutup Silang Kanan Atas Sesuai Mockup -->
            <button type="button" onclick="document.getElementById('proactive-chat-bubble').remove()" class="absolute top-3 right-3 text-[#78716C] hover:text-[#18181B] hover:bg-[#F3EFEA] w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition cursor-pointer z-20" title="Tutup Sapaan">✕</button>

            <a href="{{ route('chatbot.view') }}" class="flex items-center gap-3 sm:gap-4 group relative z-10">
                <!-- Maskot Robot 3D Animasi Bergerak Menyapa -->
                <div class="shrink-0 relative w-24 sm:w-28 self-center">
                    <img src="{{ asset('img/robot-mascot.png') }}?v=2" 
                         alt="Maskot AI CS RJ TEKNIK" 
                         class="w-full h-auto object-contain drop-shadow-md mascot-greeting-anim group-hover:scale-105 transition-transform duration-300">
                </div>

                <!-- Konten Sapaan Teks Sesuai Desain Mockup -->
                <div class="flex-1 space-y-1 pt-1 pr-3">
                    <h4 class="font-black text-[#8C5E3C] text-sm sm:text-base leading-tight">
                        Halo, Sobat Kopi......
                    </h4>
                    <p class="text-xs font-semibold text-[#18181B] leading-snug">
                        Senang bertemu dengan Anda 🤗
                    </p>
                    <p class="text-[11px] sm:text-xs text-[#3F3F46] leading-relaxed">
                        Sedang mencari Mesin Roasting Kopi untuk usaha Anda? Yuk, konsultasikan kebutuhan mesin roasting Anda bersama AI CS RJ Teknik. Kami siap membantu menemukan pilihan mesin yang tepat untuk bisnis kopi Anda! 🤝
                    </p>
                </div>
            </a>
        </div>

        <a href="{{ route('chatbot.view') }}"
           class="group flex items-center gap-3 bg-[#18181B] hover:bg-[#2A2B2E] text-white pl-3.5 pr-4 py-2 rounded-full shadow-2xl border-2 border-[#8C5E3C] transition-all duration-300 hover:scale-105 select-none"
           title="Tanya Kami Seputar Mesin Roasting Kopi">
            <!-- Avatar Robot Unik Sesuai Mockup Desain -->
            <div class="w-9 h-9 shrink-0 flex items-center justify-center">
                <svg viewBox="0 0 100 100" class="w-9 h-9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Antena -->
                    <rect x="47" y="6" width="6" height="15" rx="3" fill="#D1D5DB" />
                    <circle cx="50" cy="7" r="6" fill="#8C5E3C" />
                    <circle cx="48" cy="5" r="2" fill="#C5A070" />

                    <!-- Kuping / Headphone Bulat Coklat Kiri & Kanan -->
                    <rect x="8" y="36" width="14" height="26" rx="7" fill="#8C5E3C" />
                    <rect x="78" y="36" width="14" height="26" rx="7" fill="#8C5E3C" />

                    <!-- Kepala Robot Putih Bulat -->
                    <rect x="18" y="18" width="64" height="56" rx="22" fill="#FFFFFF" stroke="#E5E7EB" stroke-width="1.5" />
                    <path d="M24 24 C30 20 40 19 50 19 C60 19 70 20 76 24" stroke="#F3F4F6" stroke-width="2" stroke-linecap="round" />

                    <!-- Layar Visor Hitam -->
                    <rect x="25" y="27" width="50" height="38" rx="14" fill="#18181B" />

                    <!-- Mata Lengkung Tersenyum (^^) -->
                    <path d="M35 45 C37 40 43 40 45 45" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" />
                    <path d="M55 45 C57 40 63 40 65 45" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" />

                    <!-- Mulut Senyum Coklat -->
                    <path d="M46 54 C48 57 52 57 54 54" stroke="#8C5E3C" stroke-width="2.5" stroke-linecap="round" fill="none" />

                    <!-- Pundak / Badan Bawah -->
                    <path d="M30 76 C30 71 38 69 50 69 C62 69 70 71 70 76 L75 88 C75 92 70 93 50 93 C30 93 25 92 25 88 Z" fill="#E5E7EB" />
                    <circle cx="50" cy="79" r="3.5" fill="#8C5E3C" />
                </svg>
            </div>
            <!-- Teks "Tanya Kami / Seputar Mesin" -->
            <div class="text-left flex flex-col justify-center">
                <span class="text-xs sm:text-[13px] font-bold text-white tracking-wide leading-tight">Tanya Kami</span>
                <span class="text-[10px] sm:text-[11px] text-[#C5A070] leading-tight font-medium">Seputar Mesin</span>
            </div>
            <!-- Panah Chevron Kanan -->
            <svg class="w-4 h-4 text-white stroke-[2.5] ml-1 shrink-0 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
    @endif

    <!-- Footer Nuansa Hitam, Putih & Coklat -->
    <footer class="bg-[#161719] text-[#A8A29E] border-t border-[#2C2D30] pt-14 pb-10 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="space-y-4 md:col-span-2">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo RJ TEKNIK" class="w-11 h-11 object-contain rounded-full shadow-sm">
                    <div>
                        <h3 class="text-white font-black text-lg tracking-tight">RJ TEKNIK</h3>
                        <p class="text-xs text-[#C5A070] font-medium">Custom Commercial Coffee Roaster Engineering</p>
                    </div>
                </div>
                <p class="text-xs text-[#A8A29E] leading-relaxed max-w-md">
                    Produsen mesin pemanggang (roasting) kopi komersial presisi berbasis pesanan (pre-order). Kami merakit mesin dengan material drum stainless steel food grade, transmisi gearbox bertenaga, dan kompor LPG modifikasi untuk kebutuhan UMKM hingga roastery profesional nasional.
                </p>
                <div class="text-xs text-[#A8A29E] space-y-2 pt-1">
                    <p><strong class="text-white">Workshop:</strong> Desa Sedapurklagen Kabupaten Gresik, Provinsi Jawa Timur – 61172</p>
                    <div class="space-y-1 text-[#A8A29E]">
                        <p class="flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#C5A070]"></span>
                            Part of CV.Podo Moro Sumber Rezeki.
                        </p>
                        <p class="flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#C5A070]"></span>
                            Dikelola oleh PT.Dimas Kreatif Media Mandiri
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider border-b border-[#2C2D30] pb-2">Katalog Mesin PO</h4>
                <ul class="text-xs space-y-2 text-[#A8A29E]">
                    <li><a href="{{ route('catalog') }}" class="hover:text-[#C5A070] transition">Kapasitas 1 Kg (UMKM / Sample)</a></li>
                    <li><a href="{{ route('catalog') }}" class="hover:text-[#C5A070] transition">Kapasitas 3 Kg (Kedai Kopi)</a></li>
                    <li><a href="{{ route('catalog') }}" class="hover:text-[#C5A070] transition">Kapasitas 5–10 Kg (Commercial Roastery)</a></li>
                    <li><a href="{{ route('catalog') }}" class="hover:text-[#C5A070] transition">Kapasitas 20–50 Kg (Skala Industri)</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider border-b border-[#2C2D30] pb-2">IKUTI KAMI</h4>
                <ul class="text-xs space-y-3 pt-1">
                    <li>
                        <a href="https://www.instagram.com/rjgresik?obrf=MWd1NmducnYwZTB2ag==" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="group flex items-center gap-2.5 text-[#A8A29E] hover:text-white transition">
                            <span class="w-8 h-8 rounded-lg bg-[#242528] group-hover:bg-[#E1306C]/20 border border-[#333] flex items-center justify-center transition">
                                <svg class="w-4 h-4 fill-current text-[#E1306C]" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </span>
                            <div>
                                <span class="font-bold text-white block">Instagram</span>
                                <span class="text-[11px] text-[#A8A29E] group-hover:text-[#C5A070] transition">RJ GRESIK</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.facebook.com/share/19iqdy1d5s/" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="group flex items-center gap-2.5 text-[#A8A29E] hover:text-white transition">
                            <span class="w-8 h-8 rounded-lg bg-[#242528] group-hover:bg-[#1877F2]/20 border border-[#333] flex items-center justify-center transition">
                                <svg class="w-4 h-4 fill-current text-[#1877F2]" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </span>
                            <div>
                                <span class="font-bold text-white block">Facebook</span>
                                <span class="text-[11px] text-[#A8A29E] group-hover:text-[#C5A070] transition">RJ TEKNIK</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/6285730417135?text=Halo%20RJ%20TEKNIK,%20saya%20tertarik%20dengan%20mesin%20roasting%20kopi." 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="group flex items-center gap-2.5 text-[#A8A29E] hover:text-white transition">
                            <span class="w-8 h-8 rounded-lg bg-[#242528] group-hover:bg-[#25D366]/20 border border-[#333] flex items-center justify-center transition">
                                <svg class="w-4 h-4 fill-current text-[#25D366]" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </span>
                            <div>
                                <span class="font-bold text-white block">WhatsApp</span>
                                <span class="text-[11px] text-[#A8A29E] group-hover:text-[#25D366] transition">0857-3041-7135</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-[#2C2D30] mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-[#A8A29E]/60">
            <p>© {{ date('Y') }} RJ TEKNIK.</p>
        </div>
    </footer>

</body>

</html>