<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F7F5F0] text-[#18181B]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin - RJ TEKNIK')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-full flex font-sans antialiased bg-[#F7F5F0] text-[#18181B] selection:bg-[#8C5E3C] selection:text-white">
    <!-- Sidebar Admin -->
    @include('admin.partials.sidebar', ['activeMenu' => 'dashboard'])

    <!-- Konten Utama Dashboard -->
    <div class="flex-grow flex flex-col min-h-screen">
        <header class="bg-white border-b border-[#E8E4DE] h-20 px-8 flex items-center justify-between">
            <h1 class="text-lg font-bold text-[#18181B]">Panel Kontrol Sistem Informasi Penjualan</h1>
            <div class="flex items-center gap-3">
                <span class="text-xs bg-[#F3EFEA] text-[#18181B] px-3 py-1.5 rounded-xl border border-[#E8E4DE] font-bold">Admin RJ TEKNIK</span>
            </div>
        </header>

        <main class="flex-grow p-8 bg-[#F7F5F0]">
            @yield('admin_content')
        </main>
    </div>
</body>

</html>
