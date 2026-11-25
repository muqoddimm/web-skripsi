<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($view ?? request()->query('view', 'dashboard')) === 'orders' ? 'STATUS PESANAN & PERAKITAN MESIN' : (($view ?? request()->query('view', 'dashboard')) === 'recap' ? 'REKAPITULASI PENJUALAN BULANAN' : 'DASHBOARD ADMIN') }} - Admin RJ TEKNIK</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .print\:hidden {
                display: none !important;
            }
            main {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .bg-\[\#ffffff\], .bg-\[\#F7F5F0\], .bg-\[\#FAF9F5\] {
                background: #ffffff !important;
                border-color: #cbd5e1 !important;
                color: #000000 !important;
                box-shadow: none !important;
            }
            .text-\[\#18181B\], .text-\[\#8C5E3C\] {
                color: #000000 !important;
            }
            .text-\[\#57534E\], .text-\[\#78716C\] {
                color: #475569 !important;
            }
            .border-\[\#E8E4DE\] {
                border-color: #cbd5e1 !important;
            }
        }
    </style>
</head>

<body class="h-full font-sans antialiased bg-[#F7F5F0] text-[#18181B] m-0 p-0 flex">

    @php
    $currentMenu = $view ?? request()->query('view', 'dashboard');
    if ($currentMenu === 'sales') {
        $currentMenu = 'dashboard';
    }
    $stepLabels = [
        1 => ['label' => '1. Menunggu DP 20%', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200'],
        2 => ['label' => '2. DP 20% Terverifikasi', 'badge' => 'bg-[#F3EFEA] text-[#18181B] border-[#E8E4DE]'],
        3 => ['label' => '3. Perakitan Mesin', 'badge' => 'bg-[#F3EFEA] text-[#8C5E3C] border-[#8C5E3C]/40'],
        4 => ['label' => '4. Siap Pelunasan 80%', 'badge' => 'bg-[#8C5E3C] text-white border-transparent'],
        5 => ['label' => '5. Lunas & Dikirim', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
    ];

    $selectedPeriod = $selectedPeriod ?? ($selectedMonth ?? 'all');
    $recap = $yearlyRecap ?? getYearlySalesRecap($selectedYear ?? date('Y'), $selectedPeriod);
    $maxOmzet = max(1, $recap['max_monthly_omzet'] ?? 1);

    $ordersCollection = collect($orders ?? []);
    $totalPesanan = $ordersCollection->count();
    $totalOmzet = $ordersCollection->sum(fn($o) => (int)($o['total_price'] ?? 0));
    $totalDpMasuk = $ordersCollection->where('step', '>=', 2)->sum(fn($o) => (int)($o['dp_price'] ?? 0));
    $totalPelunasanMasuk = $ordersCollection->where('step', 5)->sum(fn($o) => (int)(($o['total_price'] ?? 0) - ($o['dp_price'] ?? 0)));
    $totalKasMasuk = $totalDpMasuk + $totalPelunasanMasuk;
    $totalPiutang = max(0, $totalOmzet - $totalKasMasuk);
    $pesananSelesai = $ordersCollection->where('step', 5)->count();
    $pesananProses = $ordersCollection->whereIn('step', [2, 3, 4])->count();

    $pendingChatlogs = collect($logs ?? [])->where('status', 'pending_admin')->count();
    @endphp

    <!-- SIDEBAR -->
    <!-- START SIDEBAR -->
    @include('admin.partials.sidebar', ['activeMenu' => $currentMenu])

    <!-- KONTEN UTAMA -->
    <main class="flex-grow flex flex-col min-h-screen overflow-y-auto bg-[#F7F5F0]">
        @if(session('success'))
        <div class="max-w-7xl mx-auto px-8 pt-6 w-full print:hidden">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 font-bold cursor-pointer">✕</button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-7xl mx-auto px-8 pt-6 w-full print:hidden">
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm font-medium flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 font-bold cursor-pointer">✕</button>
            </div>
        </div>
        @endif

        <div class="p-8 sm:p-12 space-y-8 max-w-7xl w-full mx-auto">
            <!-- Header Judul Dinamis -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#E8E4DE] pb-6">
                <div>
                    <h2 class="text-3xl font-black text-[#18181B] tracking-tight">
                        @if($currentMenu === 'orders')
                            STATUS PESANAN & PERAKITAN MESIN
                        @elseif($currentMenu === 'recap')
                            REKAPITULASI PENJUALAN BULANAN
                        @else
                            DASHBOARD ADMIN
                        @endif
                    </h2>
                    <p class="text-xs text-[#57534E] mt-1">
                        @if($currentMenu === 'orders')
                            Pantau daftar pesanan dari seluruh pelanggan dan perbarui tahapan perakitan unit mesin.
                        @elseif($currentMenu === 'recap')
                            Rekapitulasi akumulasi omzet, unit terjual, penerimaan uang muka (DP 20%), dan tren performa penjualan dalam satu tahun penuh.
                        @else
                            Pusat kendali operasional, analisis calon pembeli katalog, dan rekapitulasi transaksi mesin roasting kopi RJ TEKNIK.
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 print:hidden">
                    @if($currentMenu === 'recap' || $currentMenu === 'dashboard')
                    @php
                    $filterAction = $currentMenu === 'recap' ? route('admin.recap') : route('admin.dashboard');
                    $activePeriod = $selectedPeriod ?? ($selectedMonth ?? 'all');
                    @endphp
                    <form method="GET" action="{{ $filterAction }}" id="form-admin-filter" class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 bg-white border border-[#E8E4DE] rounded-xl px-3 py-1.5 shadow-xs">
                            <label for="filter-year" class="text-xs font-bold text-[#57534E]">Tahun:</label>
                            <input type="number" 
                                   id="filter-year" 
                                   name="year" 
                                   value="{{ $selectedYear ?? date('Y') }}" 
                                   min="2000" 
                                   max="2099" 
                                   placeholder="YYYY"
                                   onchange="this.form.submit()"
                                   onkeydown="if(event.key === 'Enter'){ event.preventDefault(); this.form.submit(); }"
                                   class="w-20 bg-[#FAF9F5] border border-[#E8E4DE] rounded-lg px-2 py-1 text-xs font-black text-[#18181B] text-center focus:outline-none focus:border-[#8C5E3C]"
                                   title="Ketik tahun secara manual lalu tekan Enter">
                        </div>

                        <div class="flex items-center gap-1.5 bg-white border border-[#E8E4DE] rounded-xl px-3 py-1.5 shadow-xs">
                            <label for="filter-period" class="text-xs font-bold text-[#57534E]">Periode:</label>
                            <select id="filter-period" name="period" onchange="this.form.submit()" class="bg-[#FAF9F5] border border-[#E8E4DE] rounded-lg px-2.5 py-1 text-xs font-bold text-[#18181B] focus:outline-none focus:border-[#8C5E3C]">
                                <option value="all" {{ $activePeriod === 'all' ? 'selected' : '' }}>Selama 1 Tahun Penuh (Jan - Des)</option>
                                <option value="week" {{ $activePeriod === 'week' ? 'selected' : '' }}>Minggu Ini (7 Hari Terakhir)</option>
                                <option value="1" {{ $activePeriod == '1' ? 'selected' : '' }}>Bulan Januari</option>
                                <option value="2" {{ $activePeriod == '2' ? 'selected' : '' }}>Bulan Februari</option>
                                <option value="3" {{ $activePeriod == '3' ? 'selected' : '' }}>Bulan Maret</option>
                                <option value="4" {{ $activePeriod == '4' ? 'selected' : '' }}>Bulan April</option>
                                <option value="5" {{ $activePeriod == '5' ? 'selected' : '' }}>Bulan Mei</option>
                                <option value="6" {{ $activePeriod == '6' ? 'selected' : '' }}>Bulan Juni</option>
                                <option value="7" {{ $activePeriod == '7' ? 'selected' : '' }}>Bulan Juli</option>
                                <option value="8" {{ $activePeriod == '8' ? 'selected' : '' }}>Bulan Agustus</option>
                                <option value="9" {{ $activePeriod == '9' ? 'selected' : '' }}>Bulan September</option>
                                <option value="10" {{ $activePeriod == '10' ? 'selected' : '' }}>Bulan Oktober</option>
                                <option value="11" {{ $activePeriod == '11' ? 'selected' : '' }}>Bulan November</option>
                                <option value="12" {{ $activePeriod == '12' ? 'selected' : '' }}>Bulan Desember</option>
                            </select>
                        </div>

                        <button type="submit" class="bg-[#F3EFEA] hover:bg-[#E8E4DE] text-[#8C5E3C] border border-[#E8E4DE] px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-xs" title="Terapkan Filter">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Filter</span>
                        </button>
                    </form>
                    <button type="button" onclick="printRecapInvoiceReport()" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-black px-4 sm:px-5 py-2.5 rounded-2xl transition cursor-pointer shadow-md hover:shadow-xl flex items-center gap-2 hover:scale-[1.02] transform duration-150 shrink-0">
                        <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak Laporan PDF</span>
                    </button>
                    @else
                    <div class="flex items-center gap-2 text-xs font-bold text-[#57534E] bg-white border border-[#E8E4DE] px-4 py-2.5 rounded-2xl shadow-xs">
                        <span>Produk Aktif: <strong class="text-[#18181B]">{{ count($products ?? []) }} Unit</strong></span>
                        <span>•</span>
                        <span>Total Order: <strong class="text-[#8C5E3C]">{{ $totalPesanan }} Pesanan</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- TAMPILAN 1: DASHBOARD ADMIN (view = dashboard / sales) -->
            @if($currentMenu === 'dashboard')
            
            <!-- 1. Statistik Pengunjung Calon Pembeli Katalog E-Commerce RJ TEKNIK -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-[#18181B] flex items-center gap-2">
                            <span>Statistik Pengunjung Calon Pembeli Katalog E-Commerce</span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">Live Traffic</span>
                        </h3>
                        <p class="text-xs text-[#57534E] mt-0.5">Analisis calon pembeli yang mengakses katalog, menelusuri produk, dan berkonsultasi ke AI Customer Service.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Card 1: Total Pengunjung Katalog -->
                    <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-2 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Total Pengunjung Katalog</span>
                            <div class="w-8 h-8 rounded-xl bg-[#F7F5F0] border border-[#E8E4DE] flex items-center justify-center text-[#8C5E3C]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-[#18181B]">{{ number_format($visitorStats['total_visitors'] ?? 0, 0, ',', '.') }}</p>
                        <span class="text-[11px] text-[#78716C] block">Akumulasi pengunjung periode {{ $visitorStats['period_label'] ?? 'terpilih' }}</span>
                    </div>

                    <!-- Card 2: Pengunjung Periode & Hari Ini -->
                    <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-2 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Pengunjung Periode &amp; Hari Ini</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <p class="text-3xl font-black text-[#18181B]">{{ $visitorStats['period_visitors'] ?? 0 }}</p>
                            <span class="text-xs font-semibold text-[#78716C]">Periode Ini</span>
                            <span class="text-[#E8E4DE]">/</span>
                            <span class="text-lg font-black text-[#8C5E3C]">{{ $visitorStats['today_visitors'] ?? 0 }}</span>
                            <span class="text-[11px] font-semibold text-[#8C5E3C]">Hari Ini</span>
                        </div>
                        <span class="text-[11px] text-[#78716C] block">Lonjakan traffic pada {{ $visitorStats['period_label'] ?? 'periode terpilih' }}</span>
                    </div>

                    <!-- Card 3: Konversi Interaksi Calon Pembeli -->
                    <div class="bg-white border border-emerald-200 rounded-3xl p-6 shadow-xs space-y-2 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Konversi Interaksi Calon Pembeli</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-emerald-700">{{ $visitorStats['conversion_rate'] ?? 0 }}%</p>
                        <span class="text-[11px] text-emerald-800 font-semibold block">
                            {{ $visitorStats['interactive_count'] ?? 0 }} aktif chat AI CS & bertransaksi PO
                        </span>
                    </div>

                    <!-- Card 4: Pengunjung Sekadar Melihat Katalog -->
                    <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-2 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Sekadar Melihat Katalog</span>
                            <div class="w-8 h-8 rounded-xl bg-[#F7F5F0] border border-[#E8E4DE] flex items-center justify-center text-[#78716C]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-[#18181B]">{{ number_format($visitorStats['just_browsing_count'] ?? 0, 0, ',', '.') }}</p>
                        <span class="text-[11px] text-[#78716C] block">Calon pembeli browsing spesifikasi mesin</span>
                    </div>
                </div>

                <!-- Bar Rasio Konversi Visual -->
                <div class="bg-white border border-[#E8E4DE] rounded-3xl p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs font-bold text-[#18181B] mb-2.5 gap-2">
                        <span class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span>Calon Pembeli Interaktif (Konsultasi AI CS & PO): <strong class="text-emerald-700">{{ $visitorStats['interactive_count'] ?? 0 }} ({{ $visitorStats['conversion_rate'] ?? 0 }}%)</strong></span>
                        </span>
                        <span class="flex items-center gap-2 text-[#78716C]">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                            <span>Browsing Katalog Saja: <strong>{{ $visitorStats['just_browsing_count'] ?? 0 }} ({{ round(100 - ($visitorStats['conversion_rate'] ?? 0), 1) }}%)</strong></span>
                        </span>
                    </div>
                    <div class="w-full h-3 bg-[#E8E4DE] rounded-full overflow-hidden flex">
                        <div class="bg-emerald-600 h-full rounded-l-full" style="width: {{ $visitorStats['conversion_rate'] ?? 0 }}%"></div>
                        <div class="bg-[#CBD5E1] h-full rounded-r-full" style="width: {{ max(0, 100 - ($visitorStats['conversion_rate'] ?? 0)) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- 2. Ringkasan Keuangan (Cards) -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-[#18181B] flex items-center gap-2">
                        <span>Ringkasan Transaksi Penjualan Pre-Order</span>
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-[#F3EFEA] text-[#8C5E3C] font-bold border border-[#E8E4DE]">{{ $recap['period_label'] }}</span>
                    </h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-1.5">
                        <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Total Nilai Kontrak PO</span>
                        <p class="text-2xl font-black text-[#18181B]">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</p>
                        <span class="text-[11px] text-[#78716C] block">Dari {{ $totalPesanan }} transaksi masuk</span>
                    </div>

                    <div class="bg-white border border-emerald-200 rounded-3xl p-6 shadow-xs space-y-1.5">
                        <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Total Dana Masuk (DP/Lunas)</span>
                        <p class="text-2xl font-black text-emerald-700">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</p>
                        <span class="text-[11px] text-emerald-800 font-semibold block">✓ Terverifikasi Midtrans</span>
                    </div>

                    <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-1.5">
                        <span class="text-[11px] font-bold text-[#8C5E3C] uppercase tracking-wider block">Sisa Piutang Pelunasan (80%)</span>
                        <p class="text-2xl font-black text-[#8C5E3C]">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
                        <span class="text-[11px] text-[#78716C] block">Menunggu pelunasan akhir</span>
                    </div>

                    <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-1.5">
                        <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Progres Produksi</span>
                        <p class="text-2xl font-black text-[#18181B]">{{ $pesananProses }} <span class="text-sm font-bold text-[#78716C]">Dirakit</span> / {{ $pesananSelesai }} <span class="text-sm font-bold text-emerald-700">Selesai</span></p>
                        <span class="text-[11px] text-[#78716C] block">Status antrean workshop</span>
                    </div>
                </div>
            </div>

            <!-- 3. Tabel Detail Laporan Keuangan Penjualan -->
            <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto min-h-[260px]">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-[#F3EFEA] border-b border-[#E8E4DE] text-[11px] font-bold text-[#18181B] uppercase tracking-wider">
                                <th class="py-4 px-6">Tanggal & No. Order</th>
                                <th class="py-4 px-4">Pelanggan</th>
                                <th class="py-4 px-4">Unit Mesin</th>
                                <th class="py-4 px-4">Total Kontrak</th>
                                <th class="py-4 px-4">DP 20% Masuk</th>
                                <th class="py-4 px-4">Sisa Pelunasan</th>
                                <th class="py-4 px-6">Status & Resi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E4DE] text-sm font-medium text-[#18181B]">
                            @forelse($orders ?? [] as $order)
                            @php
                            $oid = $order['id'] ?? '';
                            $odate = $order['date'] ?? date('d M Y');
                            $cname = $order['customer_name'] ?? '';
                            $cemail = $order['customer_email'] ?? '';
                            $pname = $order['product_name'] ?? '';
                            $ostep = (int)($order['step'] ?? 1);
                            $totalAmount = (int)($order['total_price'] ?? 0);
                            $dpAmount = (int)($order['dp_price'] ?? round($totalAmount * 0.20));
                            $sisaAmount = $ostep === 5 ? 0 : max(0, $totalAmount - $dpAmount);
                            $dpRec = $order['dp_receipt'] ?? null;
                            $finRec = $order['final_receipt'] ?? null;
                            $stepMeta = $stepLabels[$ostep] ?? $stepLabels[1];
                            @endphp
                            <tr class="hover:bg-[#F7F5F0]/60 transition">
                                <td class="py-4 px-6 align-top whitespace-nowrap">
                                    <span class="font-mono text-xs font-black text-[#8C5E3C] block">{{ $oid }}</span>
                                    <span class="text-[11px] text-[#78716C]">{{ $odate }}</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-bold text-[#18181B] text-xs block">{{ $cname }}</span>
                                    <span class="text-[11px] text-[#78716C]">{{ $cemail }}</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-bold text-[#18181B] text-xs block">{{ $pname }}</span>
                                    <span class="text-[10px] text-[#78716C]">Qty: {{ $order['quantity'] ?? 1 }} Unit</span>
                                </td>
                                <td class="py-4 px-4 align-top text-xs font-bold text-[#18181B] whitespace-nowrap">
                                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 align-top text-xs font-extrabold text-emerald-700 whitespace-nowrap">
                                    Rp {{ number_format($dpAmount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 align-top text-xs font-extrabold text-[#8C5E3C] whitespace-nowrap">
                                    Rp {{ number_format($sisaAmount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 align-top space-y-1">
                                    <span class="inline-block text-[11px] font-bold px-2.5 py-0.5 rounded-lg border {{ $stepMeta['badge'] }}">
                                        {{ $stepMeta['label'] }}
                                    </span>
                                    @if($dpRec)
                                     <div class="text-[10px] font-mono text-emerald-700 truncate max-w-xs" title="{{ $dpRec }}">
                                        ✓ DP: {{ $dpRec }}
                                    </div>
                                    @endif
                                    @if($finRec)
                                    <div class="text-[10px] font-mono text-emerald-700 truncate max-w-xs" title="{{ $finRec }}">
                                        ✓ Lunas: {{ $finRec }}
                                    </div>
                                    @endif

                                    <button type="button"
                                        onclick='downloadOrderInvoice(@json($order))'
                                        class="text-[10.5px] font-bold text-[#8C5E3C] hover:text-[#744A29] inline-flex items-center gap-1 mt-1 cursor-pointer transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>Download Invoice</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#78716C] text-xs">Belum ada catatan transaksi penjualan pre-order pada periode {{ $recap['period_label'] }}.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- TAMPILAN 2: REKAPITULASI PENJUALAN BULANAN (MENU BARU: view = recap) -->
            @if($currentMenu === 'recap')
            @php
            $recap = $yearlyRecap ?? getYearlySalesRecap($selectedYear ?? date('Y'));
            $maxOmzet = max(1, $recap['max_monthly_omzet'] ?? 1);
            @endphp

            <!-- KPI Cards Tahunan & Bulanan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-1.5">
                    <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Total Omzet Kontrak</span>
                    <p class="text-2xl font-black text-[#18181B]">Rp {{ number_format($recap['total_omzet'], 0, ',', '.') }}</p>
                    <span class="text-[11px] text-[#78716C] block">{{ $recap['period_label'] }}</span>
                </div>

                <div class="bg-white border border-emerald-200 rounded-3xl p-6 shadow-xs space-y-1.5">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Total DP 20% Diterima</span>
                    <p class="text-2xl font-black text-emerald-700">Rp {{ number_format($recap['total_dp'], 0, ',', '.') }}</p>
                    <span class="text-[11px] text-emerald-800 font-semibold block">Uang muka produksi terverifikasi</span>
                </div>

                <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-1.5">
                    <span class="text-[11px] font-bold text-[#8C5E3C] uppercase tracking-wider block">Total Unit Mesin Terjual</span>
                    <p class="text-2xl font-black text-[#8C5E3C]">{{ $recap['total_units'] }} <span class="text-sm font-bold text-[#78716C]">Unit Mesin</span></p>
                    <span class="text-[11px] text-[#78716C] block">{{ $recap['transaction_count'] }} Transaksi Tercatat</span>
                </div>

                <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-xs space-y-1.5">
                    @if($recap['month_filter'])
                    <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Kontribusi Penjualan</span>
                    <p class="text-2xl font-black text-[#18181B]">{{ $recap['annual_total_omzet'] > 0 ? round(($recap['total_omzet'] / $recap['annual_total_omzet']) * 100, 1) : 0 }}%</p>
                    <span class="text-[11px] text-[#78716C] block">Dari total omzet tahun {{ $recap['year'] }}</span>
                    @else
                    <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Rata-Rata Penjualan / Bulan</span>
                    <p class="text-2xl font-black text-[#18181B]">Rp {{ number_format($recap['avg_monthly_omzet'], 0, ',', '.') }}</p>
                    <span class="text-[11px] text-[#78716C] block">Performa rata-rata 12 bulan</span>
                    @endif
                </div>
            </div>

            <!-- Visual Bar Chart: Tren Penjualan Bulanan (Januari - Desember) -->
            <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#E8E4DE] pb-4">
                    <div>
                        <h3 class="text-base font-black text-[#18181B]">Tren Penjualan Bulanan Mesin Roasting Kopi</h3>
                        <p class="text-xs text-[#57534E] mt-0.5">Grafik batang rekapitulasi nilai omzet per bulan periode Januari – Desember {{ $recap['year'] }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-bold text-[#78716C]">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-[#8C5E3C]"></span>
                            <span>Omzet Bulanan (Rp)</span>
                        </span>
                    </div>
                </div>

                <!-- Chart Container (12 Flex Columns) -->
                <div class="pt-6 pb-2">
                    <div class="h-64 flex items-end justify-between gap-2 sm:gap-4 px-2 border-b border-[#E8E4DE]">
                        @foreach($recap['monthly'] as $m)
                        @php
                        $barHeight = $maxOmzet > 0 ? max(4, round(($m['omzet'] / $maxOmzet) * 100)) : 4;
                        $isSelectedMonth = ($recap['month_filter'] && $m['month_num'] == $recap['month_filter']);
                        $isCurrentMonth = ($m['month_num'] == date('n'));
                        @endphp
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                            <!-- Tooltip Hover -->
                            <div class="absolute -top-12 z-20 hidden group-hover:flex flex-col items-center bg-[#18181B] text-white text-[10.5px] font-bold px-3 py-1.5 rounded-xl shadow-xl pointer-events-none whitespace-nowrap">
                                <span>{{ $m['month_name'] }}: Rp {{ number_format($m['omzet'], 0, ',', '.') }}</span>
                                <span class="text-[9px] text-[#C5A070] font-medium">{{ $m['units'] }} Unit Mesin ({{ $m['percentage'] }}%)</span>
                                <div class="w-2 h-2 bg-[#18181B] rotate-45 -mb-1"></div>
                            </div>

                            <!-- Value on top of bar on desktop -->
                            <span class="text-[10px] font-bold text-[#78716C] mb-1.5 hidden md:block">
                                {{ $m['omzet'] > 0 ? (round($m['omzet'] / 1000000) . 'jt') : '0' }}
                            </span>

                            <!-- Bar Graphic -->
                            <div class="w-full max-w-[42px] rounded-t-xl transition-all duration-300 {{ $isSelectedMonth ? 'bg-[#8C5E3C] ring-4 ring-[#8C5E3C]/30 shadow-md' : ($isCurrentMonth ? 'bg-[#8C5E3C] ring-2 ring-[#8C5E3C]/30' : 'bg-[#C5A070] hover:bg-[#8C5E3C]') }}"
                                 style="height: {{ $barHeight }}%;">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Month Labels -->
                    <div class="flex justify-between gap-2 sm:gap-4 px-2 pt-3 text-[11px] font-bold text-[#57534E]">
                        @foreach($recap['monthly'] as $m)
                        @php
                        $isSelectedMonth = ($recap['month_filter'] && $m['month_num'] == $recap['month_filter']);
                        @endphp
                        <div class="flex-1 text-center truncate {{ $isSelectedMonth ? 'text-[#8C5E3C] font-black underline decoration-2' : (($m['month_num'] == date('n')) ? 'text-[#8C5E3C] font-black' : '') }}">
                            {{ substr($m['month_name'], 0, 3) }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tabel Rekapitulasi 12 Bulan (Januari s/d Desember) -->
            <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#E8E4DE] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-black text-[#18181B]">Tabel Rekapitulasi Penjualan 12 Bulan (Tahun {{ $recap['year'] }})</h3>
                        <p class="text-xs text-[#57534E] mt-0.5">Rincian bulanan volume unit terjual, penerimaan DP 20%, omzet kontrak, dan kontribusi penjualan.</p>
                    </div>
                    <span class="text-xs font-bold text-[#8C5E3C] bg-[#F7F5F0] border border-[#E8E4DE] px-3.5 py-1.5 rounded-xl">
                        Total {{ $recap['annual_total_units'] }} Unit Terdistribusi ({{ $recap['year'] }})
                    </span>
                </div>
                <div class="overflow-x-auto min-h-[260px]">
                    <table class="w-full text-left border-collapse min-w-[750px]">
                        <thead>
                            <tr class="bg-[#F3EFEA] border-b border-[#E8E4DE] text-[11px] font-bold text-[#18181B] uppercase tracking-wider">
                                <th class="py-4 px-6">Bulan</th>
                                <th class="py-4 px-4 text-center">Unit Terjual</th>
                                <th class="py-4 px-4">DP 20% Diterima (Rp)</th>
                                <th class="py-4 px-4">Total Nilai Kontrak (Rp)</th>
                                <th class="py-4 px-4">Kontribusi Omzet</th>
                                <th class="py-4 px-6">Status Produksi / Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E4DE] text-sm font-medium text-[#18181B]">
                            @foreach($recap['monthly'] as $m)
                            @php
                            $isSelectedMonth = ($recap['month_filter'] && $m['month_num'] == $recap['month_filter']);
                            @endphp
                            <tr class="hover:bg-[#F7F5F0]/60 transition {{ $isSelectedMonth ? 'bg-amber-100/60 font-semibold ring-1 ring-amber-300' : (($m['month_num'] == date('n')) ? 'bg-amber-50/40' : '') }}">
                                <td class="py-4 px-6 font-bold text-[#18181B] flex items-center gap-2">
                                    <span>{{ $m['month_name'] }}</span>
                                    @if($isSelectedMonth)
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-[#8C5E3C] text-white font-extrabold uppercase">Dipilih</span>
                                    @elseif($m['month_num'] == date('n'))
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-[#8C5E3C] text-white font-extrabold uppercase">Bulan Ini</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-[#18181B]">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-[#FAF9F5] border border-[#E8E4DE]">
                                        {{ $m['units'] }} Unit
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-emerald-700 whitespace-nowrap">
                                    Rp {{ number_format($m['dp'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-black text-[#18181B] whitespace-nowrap">
                                    Rp {{ number_format($m['omzet'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-[#E8E4DE] h-2 rounded-full overflow-hidden">
                                            <div class="bg-[#8C5E3C] h-full" style="width: {{ $m['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-[#57534E]">{{ $m['percentage'] }}%</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-block text-[11px] font-bold px-2.5 py-1 rounded-lg border {{ $m['status'] === 'Selesai & Lunas' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($m['status'] === 'Aktif Berjalan' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-slate-50 text-slate-700 border-slate-200') }}">
                                        {{ $m['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#FAF9F5] border-t-2 border-[#8C5E3C] font-black text-[#18181B] text-sm">
                            <tr>
                                <td class="py-4 px-6 uppercase text-xs tracking-wider">Total Akumulasi 1 Tahun</td>
                                <td class="py-4 px-4 text-center text-[#8C5E3C] text-base">{{ $recap['annual_total_units'] }} Unit</td>
                                <td class="py-4 px-4 text-emerald-700 whitespace-nowrap text-base">Rp {{ number_format($recap['annual_total_dp'], 0, ',', '.') }}</td>
                                <td class="py-4 px-4 text-[#18181B] whitespace-nowrap text-base">Rp {{ number_format($recap['annual_total_omzet'], 0, ',', '.') }}</td>
                                <td class="py-4 px-4 text-[#8C5E3C]">100%</td>
                                <td class="py-4 px-6 text-xs text-[#78716C] font-semibold">Tahun Buku {{ $recap['year'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Tabel Rincian Transaksi Penjualan Mesin (Detail & Delete) -->
            <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#E8E4DE] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black text-[#18181B]">Rincian Transaksi Penjualan Mesin Roasting</h3>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-[#8C5E3C]/10 text-[#8C5E3C] border border-[#8C5E3C]/20">
                                {{ $recap['period_label'] }}
                            </span>
                        </div>
                        <p class="text-xs text-[#57534E] mt-0.5">Daftar transaksi aktual yang tercatat pada sistem. Anda dapat melihat detail laporan transaksi atau menghapus data jika terjadi kesalahan input.</p>
                    </div>
                    <span class="text-xs font-bold text-[#8C5E3C] bg-[#F7F5F0] border border-[#E8E4DE] px-3.5 py-1.5 rounded-xl whitespace-nowrap self-start sm:self-auto">
                        Total {{ count($recap['transactions'] ?? []) }} Transaksi
                    </span>
                </div>

                <div class="overflow-x-auto min-h-[200px]">
                    <table class="w-full text-left border-collapse min-w-[850px]">
                        <thead>
                            <tr class="bg-[#F3EFEA] border-b border-[#E8E4DE] text-[11px] font-bold text-[#18181B] uppercase tracking-wider">
                                <th class="py-4 px-6">ID & Tanggal</th>
                                <th class="py-4 px-4">Pelanggan</th>
                                <th class="py-4 px-4">Unit Mesin</th>
                                <th class="py-4 px-4">DP 20% Diterima</th>
                                <th class="py-4 px-4">Total Kontrak</th>
                                <th class="py-4 px-4">Status Produksi</th>
                                <th class="py-4 px-6 text-center print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E4DE] text-sm font-medium text-[#18181B]">
                            @forelse($recap['transactions'] ?? [] as $tx)
                            @php
                            $step = (int)($tx['step'] ?? 1);
                            $stepInfo = $stepLabels[$step] ?? ['label' => "Tahap $step", 'badge' => 'bg-slate-50 text-slate-700 border-slate-200'];
                            $dp = (int)($tx['dp_price'] ?? round(($tx['total_price'] ?? 0) * 0.20));
                            $total = (int)($tx['total_price'] ?? 0);
                            @endphp
                            <tr class="hover:bg-[#F7F5F0]/60 transition">
                                <td class="py-4 px-6 align-top">
                                    <span class="font-mono text-xs font-black text-[#8C5E3C] block">{{ $tx['id'] }}</span>
                                    <span class="text-[11px] text-[#78716C] block mt-0.5">{{ $tx['date'] ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-bold text-[#18181B] text-xs block">{{ $tx['customer_name'] ?? '-' }}</span>
                                    <span class="text-[11px] text-[#78716C] block">{{ $tx['customer_email'] ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-bold text-[#18181B] text-xs block">{{ $tx['product_name'] ?? '-' }}</span>
                                    <span class="text-[11px] text-[#57534E]">{{ $tx['quantity'] ?? 1 }} Unit</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-bold text-emerald-700 whitespace-nowrap block">Rp {{ number_format($dp, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-emerald-600 font-semibold block">DP 20% Terverifikasi</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-black text-[#18181B] whitespace-nowrap block">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="inline-block text-[11px] font-bold px-2.5 py-1 rounded-lg border {{ $stepInfo['badge'] }}">
                                        {{ $stepInfo['label'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-top text-center print:hidden">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Detail Transaksi -->
                                        <button type="button" 
                                                onclick='openTransactionDetailModal(@json($tx))'
                                                class="bg-white hover:bg-[#FAF9F5] text-[#8C5E3C] border border-[#8C5E3C]/30 hover:border-[#8C5E3C] px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>Detail</span>
                                        </button>

                                        <!-- Tombol Hapus Transaksi -->
                                        <form method="POST" action="{{ route('admin.orders.delete', $tx['id']) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi {{ $tx['id'] }} dari rekapitulasi penjualan?')">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="recap">
                                            <input type="hidden" name="year" value="{{ $recap['year'] }}">
                                            <input type="hidden" name="month" value="{{ $recap['selected_month'] }}">
                                            <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 hover:text-rose-900 border border-rose-200 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#78716C]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-[#C5A070]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-bold text-sm text-[#18181B]">Tidak ada data transaksi pada periode {{ $recap['period_label'] }}</p>
                                        <p class="text-xs text-[#78716C]">Data penjualan mesin kopi akan muncul secara otomatis saat pelanggan melakukan transaksi pre-order.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- TAMPILAN 3: STATUS ORDER & PRODUKSI (view = orders) -->
            @if($currentMenu === 'orders')
            <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto min-h-[260px]">
                    <table class="w-full text-left border-collapse min-w-[850px]">
                        <thead>
                            <tr class="bg-[#F3EFEA] border-b border-[#E8E4DE] text-[11px] font-bold text-[#18181B] uppercase tracking-wider">
                                <th class="py-4 px-6">Order ID & Pelanggan</th>
                                <th class="py-4 px-4">Unit Mesin & Catatan</th>
                                <th class="py-4 px-4">Bukti Midtrans</th>
                                <th class="py-4 px-4">Tahap Perakitan</th>
                                <th class="py-4 px-6 text-right print:hidden">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E4DE] text-sm font-medium text-[#18181B]">
                            @forelse($orders ?? [] as $order)
                            @php
                            $oid = $order['id'] ?? '';
                            $cname = $order['customer_name'] ?? '';
                            $cemail = $order['customer_email'] ?? '';
                            $pname = $order['product_name'] ?? '';
                            $ostep = (int)($order['step'] ?? 1);
                            $onote = $order['note'] ?? '';
                            $dpRec = $order['dp_receipt'] ?? null;
                            $finRec = $order['final_receipt'] ?? null;
                            $stepMeta = $stepLabels[$ostep] ?? $stepLabels[1];
                            @endphp
                            <tr class="hover:bg-[#F7F5F0]/60 transition">
                                <td class="py-4 px-6 align-top">
                                    <span class="font-mono text-xs font-black text-[#8C5E3C] block">{{ $oid }}</span>
                                    <span class="font-bold text-[#18181B] text-xs block mt-0.5">{{ $cname }}</span>
                                    <span class="text-[11px] text-[#78716C]">{{ $cemail }}</span>
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="font-bold text-[#18181B] text-xs block">{{ $pname }}</span>
                                    @if($onote)
                                    <p class="text-[11px] text-[#57534E] italic mt-1 max-w-xs leading-snug">"{{ $onote }}"</p>
                                    @endif
                                </td>
                                <td class="py-4 px-4 align-top space-y-1">
                                    @if($dpRec)
                                    <div class="text-xs text-emerald-700 font-medium flex items-center gap-1">
                                        <span>✓</span> <span class="font-mono text-[11px]">{{ $dpRec }}</span>
                                    </div>
                                    @else
                                    <span class="text-xs text-[#78716C] italic">DP: Belum Bayar</span>
                                    @endif

                                    @if($finRec)
                                    <div class="text-xs text-emerald-700 font-medium flex items-center gap-1 mt-1">
                                        <span>✓</span> <span class="font-mono text-[11px]">Lunas: {{ $finRec }}</span>
                                    </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 align-top">
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-lg border {{ $stepMeta['badge'] }}">
                                        {{ $stepMeta['label'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-top text-right relative print:hidden">
                                    <div class="inline-block text-left">
                                        <button type="button" onclick="toggleActionDropdown(event, 'dropdown-order-{{ $loop->index }}')" class="w-8 h-8 rounded-xl hover:bg-[#F3EFEA] flex items-center justify-center font-bold text-[#78716C] hover:text-[#18181B] transition cursor-pointer ml-auto">
                                            ⋮
                                        </button>
                                        <div id="dropdown-order-{{ $loop->index }}" class="action-dropdown hidden absolute right-0 mt-1 w-48 bg-white border border-[#E8E4DE] rounded-2xl shadow-xl z-50 overflow-hidden py-1">
                                            <button type="button"
                                                onclick='downloadOrderInvoice(@json($order)); hideAllDropdowns();'
                                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#18181B] hover:bg-[#F3EFEA] hover:text-[#8C5E3C] transition flex items-center gap-2.5 cursor-pointer">
                                                <svg class="w-4 h-4 text-[#8C5E3C] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>Download Invoice</span>
                                            </button>
                                            <button type="button"
                                                data-url="{{ route('admin.orders.update', $oid) }}"
                                                data-id="{{ $oid }}"
                                                data-customer="{{ $cname }}"
                                                data-product="{{ $pname }}"
                                                data-step="{{ $ostep }}"
                                                data-note="{{ $onote }}"
                                                onclick="openOrderModal(this); hideAllDropdowns();"
                                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#18181B] hover:bg-[#F3EFEA] transition flex items-center gap-2.5 cursor-pointer">
                                                <svg class="w-4 h-4 text-[#57534E] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Update Status</span>
                                            </button>
                                            <div class="border-t border-[#E8E4DE]/60 my-0.5"></div>
                                            <form action="{{ route('admin.orders.delete', $oid) }}" method="POST" onsubmit="return confirm('Hapus pesanan ini?')">
                                                @csrf
                                                <button type="submit" class="w-full text-left px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition flex items-center gap-2.5 cursor-pointer">
                                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span>Hapus Pesanan</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-[#78716C] text-xs">Belum ada data pesanan masuk.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </main>

    <!-- MODAL UPDATE ORDER STATUS -->
    <div id="order-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto print:hidden">
        <div class="bg-white border border-[#E8E4DE] text-[#18181B] rounded-3xl max-w-lg w-full p-8 shadow-2xl space-y-6 my-8">
            <div>
                <h3 class="font-black text-xl text-[#18181B]">Update Progres Pre-Order</h3>
                <p id="order-modal-subtitle" class="text-xs text-[#8C5E3C] font-bold mt-0.5"></p>
            </div>
            <form id="order-form" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Pilih Tahap Pengerjaan</label>
                    <select id="order-step" name="step" class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-3 text-xs font-bold text-[#18181B] focus:outline-none focus:border-[#8C5E3C]">
                        <option value="1">Tahap 1 — Pesanan Dibuat (Menunggu DP 20%)</option>
                        <option value="2">Tahap 2 — DP 20% Terverifikasi</option>
                        <option value="3">Tahap 3 — Proses Perakitan Mesin</option>
                        <option value="4">Tahap 4 — Siap Pelunasan (Tagihan 80% Akhir)</option>
                        <option value="5">Tahap 5 — Pengiriman & Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Catatan Log Progres untuk Pelanggan</label>
                    <textarea id="order-note" name="note" rows="3" required class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-3 text-xs text-[#18181B] focus:outline-none focus:border-[#8C5E3C]" placeholder="Tuliskan catatan progres..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E8E4DE]">
                    <button type="button" onclick="closeOrderModal()" class="bg-[#F3EFEA] hover:bg-[#E8E4DE] text-[#57534E] border border-[#E8E4DE] px-5 py-3 rounded-2xl text-xs font-bold transition cursor-pointer">Batal</button>
                    <button type="submit" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-6 py-3 rounded-2xl text-xs font-bold transition cursor-pointer shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openOrderModal(btn) {
            document.getElementById('order-modal-subtitle').textContent = btn.getAttribute('data-id') + ' • ' + btn.getAttribute('data-customer');
            document.getElementById('order-step').value = btn.getAttribute('data-step');
            document.getElementById('order-note').value = btn.getAttribute('data-note');
            document.getElementById('order-form').action = btn.getAttribute('data-url');
            const modal = document.getElementById('order-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeOrderModal() {
            const modal = document.getElementById('order-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function toggleActionDropdown(event, dropdownId) {
            event.stopPropagation();
            const dropdown = document.getElementById(dropdownId);
            const isHidden = dropdown.classList.contains('hidden');
            hideAllDropdowns();
            if (isHidden) {
                dropdown.classList.remove('hidden');
            }
        }

        function hideAllDropdowns() {
            document.querySelectorAll('.action-dropdown').forEach(el => el.classList.add('hidden'));
        }

        window.addEventListener('click', function() {
            hideAllDropdowns();
        });

        function downloadOrderInvoice(order) {
            if (!order) return;

            const oid = order.id || 'PO-2026';
            const cname = order.customer_name || 'Pelanggan RJ TEKNIK';
            const cemail = order.customer_email || '-';
            const pname = order.product_name || 'Mesin Roasting Kopi Komersial';
            const qty = parseInt(order.quantity) || 1;
            const totalAmount = parseInt(order.total_price) || 0;
            const dpAmount = parseInt(order.dp_price) || Math.round(totalAmount * 0.20);
            const remainingAmount = Math.max(0, totalAmount - dpAmount);
            const step = parseInt(order.step) || 2;
            const orderDate = order.date || '{{ date("d M Y") }}';
            const note = order.note || 'Unit mesin sedang dalam proses pengerjaan workshop.';
            const dpReceipt = order.dp_receipt || null;
            const finalReceipt = order.final_receipt || null;

            const formatRupiah = (num) => 'Rp ' + Number(num || 0).toLocaleString('id-ID');

            const stepLabels = [
                { num: 1, title: 'Pesanan Dibuat', desc: 'Menunggu DP 20%' },
                { num: 2, title: 'DP 20% Terverifikasi', desc: 'Masuk Antrean Fabrikasi' },
                { num: 3, title: 'Proses Perakitan', desc: 'Fabrikasi Drum & Mekanik' },
                { num: 4, title: 'Siap Pelunasan', desc: 'Perakitan Selesai • Bayar 80%' },
                { num: 5, title: 'Lunas & Dikirim', desc: 'Lunas 100% & Menuju Alamat' }
            ];

            let currentStatusBadge = 'DP 20% TERVERIFIKASI';
            if (step === 1) currentStatusBadge = 'MENUNGGU PEMBAYARAN DP 20%';
            else if (step === 2) currentStatusBadge = 'DP 20% TERVERIFIKASI • ANTREAN WORKSHOP';
            else if (step === 3) currentStatusBadge = 'SEDANG DIRAKIT DI WORKSHOP';
            else if (step === 4) currentStatusBadge = 'PERAKITAN SELESAI • MENUNGGU PELUNASAN 80%';
            else if (step === 5) currentStatusBadge = 'LUNAS 100% • DALAM PENGIRIMAN KARGO';

            const printDate = new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            let timelineRowsHtml = '';
            stepLabels.forEach(s => {
                const isDone = step >= s.num;
                const isCurrent = step === s.num;
                const numColor = isDone ? '#000000' : '#888888';
                const titleColor = isDone ? '#000000' : '#777777';
                const statusText = isCurrent ? '● TAHAP AKTIF' : (isDone ? '✓ SELESAI' : 'MENUNGGU');
                const statusColor = isDone ? '#000000' : '#888888';
                const rowBg = isCurrent ? 'background: #f8f8f8;' : '';
                timelineRowsHtml += `
                    <tr style="border-bottom: 1px solid #e5e5e5; ${rowBg}">
                        <td style="padding: 5px 8px; font-weight: 800; color: ${numColor}; width: 34px; text-align: center; border: 1px solid #e5e5e5;">${s.num}</td>
                        <td style="padding: 5px 8px; font-weight: 700; color: ${titleColor}; border: 1px solid #e5e5e5;">${s.title}</td>
                        <td style="padding: 5px 8px; color: #555555; font-size: 10px; border: 1px solid #e5e5e5;">${s.desc}</td>
                        <td style="padding: 5px 8px; text-align: right; font-weight: 800; font-size: 10px; color: ${statusColor}; border: 1px solid #e5e5e5;">${statusText}</td>
                    </tr>
                `;
            });

            const printWindow = window.open('', '_blank', 'width=880,height=940');
            if (!printWindow) {
                alert('Mohon izinkan pop-up browser untuk mencetak faktur riwayat pesanan.');
                return;
            }

            const htmlContent = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Faktur Riwayat Pre-Order - ${oid} - RJ TEKNIK</title>
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    body {
                        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
                        background-color: #f4f4f4;
                        color: #000000;
                        padding: 18px;
                        font-size: 11px;
                        line-height: 1.4;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .invoice-wrapper {
                        max-width: 800px;
                        margin: 0 auto;
                        background: #ffffff;
                        border: 1px solid #000000;
                        border-radius: 12px;
                        padding: 24px 28px;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
                    }
                    .no-print-bar {
                        max-width: 800px;
                        margin: 0 auto 12px auto;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        background: #000000;
                        color: #ffffff;
                        padding: 10px 18px;
                        border-radius: 8px;
                    }
                    .btn {
                        background: #ffffff;
                        color: #000000;
                        border: 1px solid #ffffff;
                        padding: 7px 16px;
                        border-radius: 6px;
                        font-weight: 800;
                        font-size: 11px;
                        cursor: pointer;
                        transition: opacity 0.2s;
                    }
                    .btn:hover { background: #e5e5e5; }
                    .btn-close {
                        background: #262626;
                        color: #ffffff;
                        border: 1px solid #404040;
                        margin-left: 8px;
                    }
                    .btn-close:hover { background: #404040; }
                    .kop {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        padding-bottom: 12px;
                        border-bottom: 2px solid #000000;
                    }
                    .kop-title {
                        font-size: 20px;
                        font-weight: 900;
                        letter-spacing: -0.5px;
                        color: #000000;
                    }
                    .kop-subtitle {
                        font-size: 9.5px;
                        font-weight: 800;
                        color: #000000;
                        letter-spacing: 1.5px;
                        text-transform: uppercase;
                        margin-top: 1px;
                    }
                    .kop-desc {
                        font-size: 10px;
                        color: #333333;
                        margin-top: 3px;
                        line-height: 1.35;
                    }
                    .doc-title-block {
                        margin: 12px 0 10px 0;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 12px;
                    }
                    .doc-title {
                        font-size: 14px;
                        font-weight: 900;
                        color: #000000;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                    }
                    .doc-badge {
                        display: inline-block;
                        background: #ffffff;
                        border: 1.5px solid #000000;
                        color: #000000;
                        font-weight: 900;
                        padding: 4px 10px;
                        border-radius: 9999px;
                        font-size: 10px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        white-space: nowrap;
                    }
                    .meta-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 12px;
                        background: #f9f9f9;
                        padding: 10px 14px;
                        border-radius: 8px;
                        border: 1px solid #d4d4d4;
                        margin-bottom: 12px;
                    }
                    .meta-item {
                        font-size: 10.5px;
                    }
                    .meta-label {
                        font-weight: 800;
                        color: #555555;
                        text-transform: uppercase;
                        font-size: 9px;
                        letter-spacing: 0.5px;
                    }
                    .meta-val {
                        font-weight: 800;
                        color: #000000;
                        font-size: 11.5px;
                        margin-top: 1px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 12px;
                    }
                    table th {
                        background: #000000;
                        color: #ffffff;
                        font-weight: 800;
                        font-size: 10px;
                        padding: 6px 10px;
                        text-align: left;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        border: 1px solid #000000;
                    }
                    table th.text-right { text-align: right; }
                    table td {
                        padding: 6px 10px;
                        border: 1px solid #e5e5e5;
                        font-size: 11px;
                    }
                    .total-box {
                        width: 290px;
                        background: #f9f9f9;
                        border: 1px solid #000000;
                        border-radius: 8px;
                        padding: 10px 14px;
                    }
                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        font-size: 10.5px;
                        padding: 2.5px 0;
                        color: #333333;
                    }
                    .total-row.strong {
                        font-weight: 900;
                        font-size: 12px;
                        color: #000000;
                        border-top: 1.5px solid #000000;
                        padding-top: 5px;
                        margin-top: 4px;
                    }
                    .section-header {
                        font-size: 10.5px;
                        font-weight: 900;
                        text-transform: uppercase;
                        color: #000000;
                        letter-spacing: 0.5px;
                        margin-bottom: 5px;
                    }
                    .receipt-box {
                        background: #f9f9f9;
                        border: 1px solid #d4d4d4;
                        border-radius: 8px;
                        padding: 10px 12px;
                        font-size: 10.5px;
                    }
                    .signature-area {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        margin-top: 14px;
                        padding-top: 10px;
                        border-top: 1px solid #000000;
                    }
                    .stamp-box {
                        border: 2px solid #000000;
                        border-radius: 8px;
                        padding: 7px 14px;
                        text-align: center;
                        color: #000000;
                        max-width: 260px;
                        background: #ffffff;
                    }
                    .stamp-title {
                        font-weight: 900;
                        font-size: 10px;
                        letter-spacing: 0.5px;
                    }
                    .stamp-sub {
                        font-size: 8.5px;
                        font-weight: 800;
                        margin-top: 1px;
                    }
                    .footer-note {
                        font-size: 8.5px;
                        color: #555555;
                        text-align: center;
                        margin-top: 12px;
                        padding-top: 8px;
                        border-top: 1px dashed #cccccc;
                        line-height: 1.35;
                    }
                    @media print {
                        body {
                            background-color: #ffffff !important;
                            padding: 0 !important;
                            color: #000000 !important;
                        }
                        .invoice-wrapper {
                            border: none !important;
                            box-shadow: none !important;
                            padding: 0 !important;
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                        .no-print-bar { display: none !important; }
                        table th {
                            background-color: #000000 !important;
                            color: #ffffff !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .total-box, .meta-grid, .receipt-box, .stamp-box {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        @page {
                            size: A4 portrait;
                            margin: 8mm 12mm 8mm 12mm;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="no-print-bar">
                    <span><strong>Faktur Riwayat Pre-Order (Hitam Putih)</strong> • ${oid}</span>
                    <div>
                        <button class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
                        <button class="btn btn-close" onclick="window.close()">Tutup</button>
                    </div>
                </div>

                <div class="invoice-wrapper">
                    <!-- Kop Resmi RJ TEKNIK (Hitam Putih) -->
                    <div class="kop">
                        <div>
                            <div class="kop-title">RJ TEKNIK</div>
                            <div class="kop-subtitle">COFFEE ROASTER ENGINEERING</div>
                            <div class="kop-desc">
                                Part of CV. Podo Moro Sumber Rezeki &amp; PT. Dimas Kreatif Media Mandiri<br>
                                <strong>Workshop:</strong> Desa Sedapurklagen, Kec. Benjeng, Kab. Gresik, Jawa Timur – 61172<br>
                                <strong>Layanan Konsultasi:</strong> 24 Jam
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 10px; color: #555555; font-weight: 800;">NO. PRE-ORDER</div>
                            <div style="font-family: monospace; font-size: 15px; font-weight: 900; color: #000000; background: #f0f0f0; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #000000; display: inline-block; margin-top: 3px;">
                                ${oid}
                            </div>
                            <div style="font-size: 9.5px; color: #555555; margin-top: 4px; font-weight: 600;">Tgl Pesan: ${orderDate}</div>
                        </div>
                    </div>

                    <!-- Judul Dokumen & Badge (Hitam Putih) -->
                    <div class="doc-title-block">
                        <div>
                            <h1 class="doc-title">Faktur Riwayat &amp; Status Produksi Mesin</h1>
                            <p style="font-size: 10px; color: #444444; margin-top: 1px;">Dokumen resmi konfirmasi spesifikasi, verifikasi pembayaran 2 tahap (DP 20% &amp; pelunasan), serta arsip fabrikasi.</p>
                        </div>
                        <div class="doc-badge">${currentStatusBadge}</div>
                    </div>

                    <!-- Meta Data Pemesan & Status (Hitam Putih) -->
                    <div class="meta-grid">
                        <div class="meta-item">
                            <div class="meta-label">Data Pemesan / Pelanggan</div>
                            <div class="meta-val">${cname}</div>
                            <div style="color: #444444; font-size: 10px;">${cemail}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Verifikasi &amp; Tanggal Cetak</div>
                            <div class="meta-val">${printDate}</div>
                            <div style="color: #000000; font-size: 10px; font-weight: 800;">● Terverifikasi Sistem RJ TEKNIK</div>
                        </div>
                    </div>

                    <!-- Tabel Produk Pre-Order (Hitam Putih) -->
                    <div class="section-header">Rincian Mesin Roasting Kopi</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 32px; text-align: center;">No</th>
                                <th>Deskripsi Unit Mesin</th>
                                <th style="width: 50px; text-align: center;">Qty</th>
                                <th class="text-right" style="width: 130px;">Harga Satuan</th>
                                <th class="text-right" style="width: 130px;">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center; font-weight: 800;">1</td>
                                <td>
                                    <strong style="color: #000000; font-size: 12px;">${pname}</strong>
                                    <div style="font-size: 10px; color: #444444; margin-top: 1px;">
                                        Material Full Stainless Steel Food Grade, kompor burner LPG modifikasi hemat bahan bakar, sistem cooling bin cepat.
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 800;">${qty}</td>
                                <td class="text-right" style="font-weight: 700;">${formatRupiah(totalAmount / qty)}</td>
                                <td class="text-right" style="font-weight: 900; color: #000000;">${formatRupiah(totalAmount)}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Rincian Finansial Sistem 2 Tahap & Bukti Midtrans (Hitam Putih) -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <div class="section-header">Bukti &amp; Riwayat Transaksi Midtrans</div>
                            <div class="receipt-box">
                                <div style="margin-bottom: 5px;">
                                    <strong>Bukti DP 20%:</strong><br>
                                    <span style="font-family: monospace; color: #000000; background: #ffffff; padding: 2px 6px; border-radius: 4px; border: 1px solid #000000; display: inline-block; margin-top: 2px; font-weight: 700;">
                                        ${dpReceipt ? dpReceipt : 'Terverifikasi (DP 20% Lunas)'}
                                    </span>
                                </div>
                                <div>
                                    <strong>Bukti Pelunasan 80%:</strong><br>
                                    <span style="font-family: monospace; color: #000000; background: #ffffff; padding: 2px 6px; border-radius: 4px; border: 1px solid #000000; display: inline-block; margin-top: 2px; font-weight: 700;">
                                        ${finalReceipt ? finalReceipt : (step === 5 ? 'Lunas 100% (Verifikasi Midtrans)' : 'Menunggu Pelunasan setelah Perakitan Selesai')}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="total-box">
                            <div class="total-row">
                                <span>Total Nilai Mesin:</span>
                                <strong style="color: #000000;">${formatRupiah(totalAmount)}</strong>
                            </div>
                            <div class="total-row">
                                <span>Uang Muka (DP 20%):</span>
                                <strong style="color: #000000;">${formatRupiah(dpAmount)} (Lunas)</strong>
                            </div>
                            <div class="total-row">
                                <span>Sisa Pelunasan (80%):</span>
                                <strong style="color: #000000;">${step === 5 ? 'Rp 0 (Lunas)' : formatRupiah(remainingAmount)}</strong>
                            </div>
                            <div class="total-row strong">
                                <span>Total Telah Dibayar:</span>
                                <span style="color: #000000;">${step === 5 ? formatRupiah(totalAmount) : formatRupiah(dpAmount)}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tahapan Alur Pengerjaan (5 Tahap) (Hitam Putih) -->
                    <div class="section-header">Progres Perakitan Workshop (5 Tahap)</div>
                    <table style="margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th style="width: 34px; text-align: center;">Tahap</th>
                                <th>Nama Alur Produksi</th>
                                <th>Keterangan Pengerjaan</th>
                                <th style="text-align: right; width: 140px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${timelineRowsHtml}
                        </tbody>
                    </table>

                    <!-- Catatan Workshop (Hitam Putih) -->
                    <div style="background: #f9f9f9; border: 1px solid #d4d4d4; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px;">
                        <span style="font-weight: 900; font-size: 9.5px; text-transform: uppercase; color: #000000; display: block; margin-bottom: 2px;">Catatan Produksi Terkini:</span>
                        <p style="font-size: 10.5px; color: #000000; line-height: 1.4;">${note}</p>
                    </div>

                    <!-- Area Tanda Tangan & Cap Validasi (Hitam Putih) -->
                    <div class="signature-area">
                        <div class="stamp-box">
                            <div class="stamp-title">RJ TEKNIK ENGINEERING</div>
                            <div class="stamp-sub">OFFICIAL VERIFIED WORKSHOP</div>
                            <div style="font-size: 9px; margin-top: 2px; font-weight: 700; color: #000000;">CV. PODO MORO SUMBER REZEKI</div>
                            <div style="font-size: 8px; color: #444444; margin-top: 1px;">Gresik, Jawa Timur – Indonesia</div>
                        </div>

                        <div style="text-align: right; width: 250px;">
                            <div style="font-size: 10px; color: #444444;">Gresik, ${printDate}</div>
                            <div style="font-size: 10.5px; font-weight: 800; color: #000000; margin-top: 1px;">Kepala Produksi &amp; Administrasi</div>
                            <div style="height: 42px; display: flex; align-items: center; justify-content: flex-end;">
                                <span style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 24px; color: #000000;">RJ Teknik Workshop</span>
                            </div>
                            <div style="font-weight: 800; font-size: 11px; color: #000000; text-decoration: underline;">Divisi Fabrikasi RJ TEKNIK</div>
                            <div style="font-size: 9px; color: #555555;">Workshop Sedapurklagen Gresik</div>
                        </div>
                    </div>

                    <div class="footer-note">
                        Dokumen faktur ini diterbitkan resmi oleh Sistem Informasi Pre-Order RJ TEKNIK. Dicetak secara otomatis tanpa tanda tangan basah fisik memiliki kekuatan hukum yang sah sebagai bukti pemesanan mesin sangrai kopi komersial.
                    </div>
                </div>

                <script>
                    window.addEventListener('load', function() {
                        setTimeout(function() {
                            window.print();
                        }, 400);
                    });
                <\/script>
            </body>
            </html>
            `;

            printWindow.document.open();
            printWindow.document.write(htmlContent);
            printWindow.document.close();
        }

        // ==========================================
        // CONTROLLER MODAL DETAIL TRANSAKSI REKAP
        // ==========================================
        let currentDetailOrderObj = null;

        function openTransactionDetailModal(order) {
            if (!order) return;
            currentDetailOrderObj = order;

            const formatRupiah = (num) => 'Rp ' + Number(num || 0).toLocaleString('id-ID');

            document.getElementById('td-modal-id').textContent = order.id || '-';
            document.getElementById('td-modal-date').textContent = 'Tanggal Transaksi: ' + (order.date || '-');
            document.getElementById('td-modal-customer').textContent = order.customer_name || '-';
            document.getElementById('td-modal-email').textContent = order.customer_email || '-';
            document.getElementById('td-modal-product').textContent = order.product_name || '-';
            document.getElementById('td-modal-qty').textContent = (order.quantity || 1) + ' Unit';
            document.getElementById('td-modal-note').textContent = order.note || 'Tidak ada catatan workshop tambahan.';

            const total = parseInt(order.total_price) || 0;
            const dp = parseInt(order.dp_price) || Math.round(total * 0.20);
            const step = parseInt(order.step) || 1;
            const remaining = step >= 5 ? 0 : Math.max(0, total - dp);

            document.getElementById('td-modal-total').textContent = formatRupiah(total);
            document.getElementById('td-modal-dp').textContent = formatRupiah(dp);
            document.getElementById('td-modal-remaining').textContent = formatRupiah(remaining);

            document.getElementById('td-modal-dp-receipt').textContent = order.dp_receipt || 'Terverifikasi (DP 20% Lunas)';
            document.getElementById('td-modal-final-receipt').textContent = order.final_receipt || (step >= 5 ? 'Lunas 100% (Verifikasi Midtrans)' : 'Menunggu Pelunasan setelah Perakitan Selesai');

            const stepLabelsMap = {
                1: '1. Menunggu Pembayaran DP 20%',
                2: '2. DP 20% Terverifikasi • Antrean Fabrikasi',
                3: '3. Sedang Dirakit di Workshop',
                4: '4. Perakitan Selesai • Siap Pelunasan 80%',
                5: '5. Lunas 100% • Selesai & Dikirim'
            };
            const badgeText = stepLabelsMap[step] || ('Tahap ' + step);
            const badgeClass = step >= 5 
                ? 'bg-emerald-50 text-emerald-800 border-emerald-200' 
                : (step >= 2 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-slate-50 text-slate-700 border-slate-200');

            document.getElementById('td-modal-status-badge').innerHTML = `<span class="inline-block text-[11px] font-bold px-3 py-1 rounded-lg border ${badgeClass}">${badgeText}</span>`;

            const modal = document.getElementById('modal-transaction-detail');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeTransactionDetailModal() {
            const modal = document.getElementById('modal-transaction-detail');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function printCurrentDetailOrder() {
            if (currentDetailOrderObj) {
                downloadOrderInvoice(currentDetailOrderObj);
            }
        }

        // ==========================================
        // GENERATOR INVOICE REKAPITULASI PENJUALAN PDF
        // ==========================================
        const globalRecapData = @json($recap ?? []);
        const globalVisitorStats = @json($visitorStats ?? []);

        function printRecapInvoiceReport() {
            const recap = globalRecapData;
            if (!recap) return;

            const printWindow = window.open('', '_blank', 'width=920,height=960');
            if (!printWindow) {
                alert('Mohon izinkan pop-up browser untuk mencetak faktur invoice rekapitulasi.');
                return;
            }

            const formatRupiah = (num) => 'Rp ' + Number(num || 0).toLocaleString('id-ID');
            const printDate = new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            const txList = recap.transactions || [];
            let txRowsHtml = '';

            if (txList.length === 0) {
                txRowsHtml = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 18px 10px; color: #666666; font-style: italic;">
                            Belum ada riwayat transaksi penjualan mesin roasting pada periode ${recap.period_label}.
                        </td>
                    </tr>
                `;
            } else {
                txList.forEach((tx, idx) => {
                    const total = parseInt(tx.total_price) || 0;
                    const dp = parseInt(tx.dp_price) || Math.round(total * 0.20);
                    const step = parseInt(tx.step) || 1;
                    const remaining = step >= 5 ? 0 : Math.max(0, total - dp);

                    let stageBadge = 'Tahap 1: Pesanan Dibuat';
                    if (step === 2) stageBadge = 'Tahap 2: DP 20% Terverifikasi';
                    else if (step === 3) stageBadge = 'Tahap 3: Perakitan Mesin';
                    else if (step === 4) stageBadge = 'Tahap 4: Siap Pelunasan 80%';
                    else if (step === 5) stageBadge = 'Tahap 5: Lunas & Dikirim';

                    const rowBg = idx % 2 === 1 ? 'background: #fbfbfb;' : '';

                    txRowsHtml += `
                        <tr style="${rowBg}">
                            <td style="text-align: center; font-weight: 700;">${idx + 1}</td>
                            <td>
                                <strong style="font-family: monospace; font-size: 11px;">${tx.id}</strong><br>
                                <span style="font-size: 9.5px; color: #555555;">${tx.date || '-'}</span>
                            </td>
                            <td>
                                <strong style="color: #000;">${tx.customer_name || '-'}</strong><br>
                                <span style="font-size: 9.5px; color: #555555;">${tx.customer_email || '-'}</span>
                            </td>
                            <td>
                                <span style="font-weight: 700;">${tx.product_name || '-'}</span><br>
                                <span style="font-size: 9.5px; color: #555555;">Volume: ${tx.quantity || 1} Unit</span>
                            </td>
                            <td class="text-right" style="color: #047857; font-weight: 800;">
                                ${formatRupiah(dp)}
                            </td>
                            <td class="text-right" style="color: #444444; font-weight: 700;">
                                ${step >= 5 ? 'Rp 0 (Lunas)' : formatRupiah(remaining)}
                            </td>
                            <td class="text-right" style="font-weight: 900; color: #000;">
                                ${formatRupiah(total)}
                            </td>
                            <td style="text-align: center;">
                                <span style="font-size: 9px; font-weight: 800; padding: 2px 6px; border: 1px solid #000; border-radius: 4px; display: inline-block;">
                                    ${stageBadge}
                                </span>
                            </td>
                        </tr>
                    `;
                });
            }

            // Tabel 12 Bulan (hanya ditampilkan jika filter tahun penuh)
            let monthlySectionHtml = '';
            if (!recap.month_filter && recap.monthly) {
                let monthlyRowsHtml = '';
                Object.values(recap.monthly).forEach(m => {
                    const rowBg = m.units > 0 ? 'background: #fdfbf7;' : '';
                    monthlyRowsHtml += `
                        <tr style="${rowBg}">
                            <td style="font-weight: 700;">${m.month_name}</td>
                            <td style="text-align: center; font-weight: 800;">${m.units} Unit</td>
                            <td class="text-right" style="font-weight: 700; color: #047857;">${formatRupiah(m.dp)}</td>
                            <td class="text-right" style="font-weight: 900; color: #000;">${formatRupiah(m.omzet)}</td>
                            <td style="text-align: center; font-weight: 700;">${m.percentage}%</td>
                            <td style="text-align: center; font-size: 9.5px;">${m.status}</td>
                        </tr>
                    `;
                });

                monthlySectionHtml = `
                    <div class="section-title" style="margin-top: 14px;">Tabel Akumulasi Distribusi 12 Bulan (Tahun Buku ${recap.year})</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th style="text-align: center; width: 85px;">Unit Terjual</th>
                                <th class="text-right" style="width: 140px;">DP 20% Diterima</th>
                                <th class="text-right" style="width: 150px;">Total Omzet Kontrak</th>
                                <th style="text-align: center; width: 95px;">Kontribusi</th>
                                <th style="text-align: center; width: 140px;">Status Produksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${monthlyRowsHtml}
                        </tbody>
                        <tfoot>
                            <tr style="background: #f0f0f0; font-weight: 900; border-top: 2px solid #000;">
                                <td style="text-transform: uppercase;">Total Akumulasi (${recap.year})</td>
                                <td style="text-align: center; font-size: 11px;">${recap.annual_total_units} Unit</td>
                                <td class="text-right" style="color: #047857; font-size: 11px;">${formatRupiah(recap.annual_total_dp)}</td>
                                <td class="text-right" style="color: #000; font-size: 11.5px;">${formatRupiah(recap.annual_total_omzet)}</td>
                                <td style="text-align: center;">100%</td>
                                <td style="text-align: center; font-size: 9px; text-transform: uppercase;">Tutup Buku ${recap.year}</td>
                            </tr>
                        </tfoot>
                    </table>
                `;
            }

            const docNumber = `INV-REKAP/${recap.year}/${recap.month_filter ? ('0' + recap.month_filter).slice(-2) : 'ALL'}/RJ-${new Date().getFullYear()}`;

            const htmlContent = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Invoice & Rekapitulasi Penjualan - ${recap.year} - RJ TEKNIK</title>
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    body {
                        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
                        background-color: #f4f4f4;
                        color: #000000;
                        padding: 18px;
                        font-size: 10.5px;
                        line-height: 1.4;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .invoice-wrapper {
                        max-width: 860px;
                        margin: 0 auto;
                        background: #ffffff;
                        border: 1px solid #000000;
                        border-radius: 12px;
                        padding: 24px 28px;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
                    }
                    .no-print-bar {
                        max-width: 860px;
                        margin: 0 auto 12px auto;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        background: #000000;
                        color: #ffffff;
                        padding: 10px 18px;
                        border-radius: 8px;
                    }
                    .btn {
                        background: #ffffff;
                        color: #000000;
                        border: 1px solid #ffffff;
                        padding: 7px 16px;
                        border-radius: 6px;
                        font-weight: 800;
                        font-size: 11px;
                        cursor: pointer;
                        transition: opacity 0.2s;
                    }
                    .btn:hover { background: #e5e5e5; }
                    .btn-close {
                        background: #262626;
                        color: #ffffff;
                        border: 1px solid #404040;
                        margin-left: 8px;
                    }
                    .btn-close:hover { background: #404040; }
                    .kop {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        padding-bottom: 12px;
                        border-bottom: 2px solid #000000;
                    }
                    .kop-title {
                        font-size: 20px;
                        font-weight: 900;
                        letter-spacing: -0.5px;
                        color: #000000;
                    }
                    .kop-subtitle {
                        font-size: 9.5px;
                        font-weight: 800;
                        color: #000000;
                        letter-spacing: 1.5px;
                        text-transform: uppercase;
                        margin-top: 1px;
                    }
                    .kop-desc {
                        font-size: 10px;
                        color: #333333;
                        margin-top: 3px;
                        line-height: 1.35;
                    }
                    .doc-title-block {
                        margin: 14px 0 12px 0;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 12px;
                    }
                    .doc-title {
                        font-size: 14px;
                        font-weight: 900;
                        color: #000000;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                    }
                    .doc-badge {
                        display: inline-block;
                        background: #ffffff;
                        border: 1.5px solid #000000;
                        color: #000000;
                        font-weight: 900;
                        padding: 4px 10px;
                        border-radius: 9999px;
                        font-size: 9.5px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        white-space: nowrap;
                    }
                    .kpi-grid {
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        gap: 10px;
                        margin-bottom: 14px;
                    }
                    .kpi-card {
                        background: #f9f9f9;
                        border: 1px solid #d4d4d4;
                        border-radius: 8px;
                        padding: 9px 12px;
                    }
                    .kpi-label {
                        font-size: 8.5px;
                        font-weight: 800;
                        color: #555555;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .kpi-val {
                        font-size: 14px;
                        font-weight: 900;
                        color: #000000;
                        margin-top: 2px;
                    }
                    .kpi-sub {
                        font-size: 8.5px;
                        color: #666666;
                        margin-top: 1px;
                    }
                    .section-title {
                        font-size: 10.5px;
                        font-weight: 900;
                        text-transform: uppercase;
                        color: #000000;
                        letter-spacing: 0.5px;
                        margin-bottom: 6px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 12px;
                    }
                    table th {
                        background: #000000;
                        color: #ffffff;
                        font-weight: 800;
                        font-size: 9.5px;
                        padding: 6px 8px;
                        text-align: left;
                        text-transform: uppercase;
                        letter-spacing: 0.4px;
                        border: 1px solid #000000;
                    }
                    table th.text-right { text-align: right; }
                    table td {
                        padding: 6px 8px;
                        border: 1px solid #e0e0e0;
                        font-size: 10px;
                    }
                    table td.text-right { text-align: right; }
                    .notice-box {
                        background: #f9f9f9;
                        border: 1px solid #d4d4d4;
                        border-radius: 8px;
                        padding: 9px 12px;
                        margin-bottom: 12px;
                    }
                    .notice-title {
                        font-size: 9px;
                        font-weight: 900;
                        text-transform: uppercase;
                        color: #000000;
                        margin-bottom: 3px;
                    }
                    .notice-list {
                        margin-left: 14px;
                        font-size: 9.5px;
                        color: #333333;
                        line-height: 1.4;
                    }
                    .signature-area {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        margin-top: 14px;
                        padding-top: 10px;
                        border-top: 1px solid #000000;
                    }
                    .stamp-box {
                        border: 2px solid #000000;
                        border-radius: 8px;
                        padding: 7px 14px;
                        text-align: center;
                        color: #000000;
                        max-width: 260px;
                        background: #ffffff;
                    }
                    .stamp-title {
                        font-weight: 900;
                        font-size: 10px;
                        letter-spacing: 0.5px;
                    }
                    .stamp-sub {
                        font-size: 8.5px;
                        font-weight: 800;
                        margin-top: 1px;
                    }
                    .footer-note {
                        font-size: 8.5px;
                        color: #555555;
                        text-align: center;
                        margin-top: 12px;
                        padding-top: 8px;
                        border-top: 1px dashed #cccccc;
                        line-height: 1.35;
                    }
                    @media print {
                        body {
                            background-color: #ffffff !important;
                            padding: 0 !important;
                            color: #000000 !important;
                        }
                        .invoice-wrapper {
                            border: none !important;
                            box-shadow: none !important;
                            padding: 0 !important;
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                        .no-print-bar { display: none !important; }
                        table th {
                            background-color: #000000 !important;
                            color: #ffffff !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .kpi-card, .notice-box, .stamp-box {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        @page {
                            size: A4 portrait;
                            margin: 8mm 10mm 8mm 10mm;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="no-print-bar">
                    <span><strong>Faktur &amp; Laporan Invoice Rekapitulasi Penjualan</strong> • ${recap.period_label}</span>
                    <div>
                        <button class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
                        <button class="btn btn-close" onclick="window.close()">✕ Tutup</button>
                    </div>
                </div>

                <div class="invoice-wrapper">
                    <!-- Kop Resmi RJ TEKNIK -->
                    <div class="kop">
                        <div>
                            <div class="kop-title">RJ TEKNIK</div>
                            <div class="kop-subtitle">COFFEE ROASTER ENGINEERING &amp; FABRICATION</div>
                            <div class="kop-desc">
                                Part of CV. Podo Moro Sumber Rezeki &amp; PT. Dimas Kreatif Media Mandiri<br>
                                <strong>Workshop:</strong> Desa Sedapurklagen, Kec. Benjeng, Kab. Gresik, Jawa Timur – 61172<br>
                                <strong>Layanan Konsultasi:</strong> WhatsApp 0857-3041-7135 • rjteknikgresik@gmail.com
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 9.5px; color: #555555; font-weight: 800;">NO. INVOICE REKAPITULASI</div>
                            <div style="font-family: monospace; font-size: 13.5px; font-weight: 900; color: #000000; background: #f0f0f0; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #000000; display: inline-block; margin-top: 2px;">
                                ${docNumber}
                            </div>
                            <div style="font-size: 9px; color: #555555; margin-top: 4px; font-weight: 600;">Tgl Terbit: ${printDate}</div>
                        </div>
                    </div>

                    <!-- Judul Dokumen -->
                    <div class="doc-title-block">
                        <div>
                            <h1 class="doc-title">Faktur &amp; Laporan Rekapitulasi Penjualan Mesin</h1>
                            <p style="font-size: 9.5px; color: #444444; margin-top: 1px;">Dokumen resmi ringkasan eksekutif penjualan mesin sangrai kopi, penerimaan DP 20% (Midtrans), dan status perakitan.</p>
                        </div>
                        <div class="doc-badge">${recap.period_label}</div>
                    </div>

                    <!-- Ringkasan Eksekutif 4 Pilar Finansial -->
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-label">Total Omzet Kontrak</div>
                            <div class="kpi-val">${formatRupiah(recap.total_omzet)}</div>
                            <div class="kpi-sub">${recap.period_label}</div>
                        </div>
                        <div class="kpi-card" style="border-color: #047857; background: #f4faf7;">
                            <div class="kpi-label" style="color: #047857;">Total DP 20% Diterima</div>
                            <div class="kpi-val" style="color: #047857;">${formatRupiah(recap.total_dp)}</div>
                            <div class="kpi-sub" style="color: #047857;">Kas Masuk Terverifikasi</div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">Sisa Pelunasan (80%)</div>
                            <div class="kpi-val">${formatRupiah(Math.max(0, recap.total_omzet - recap.total_dp))}</div>
                            <div class="kpi-sub">Piutang Produksi Berjalan</div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">Volume Mesin Terjual</div>
                            <div class="kpi-val">${recap.total_units} <span style="font-size: 10px; font-weight: 700;">Unit Mesin</span></div>
                            <div class="kpi-sub">${txList.length} Transaksi Tercatat</div>
                        </div>
                    </div>

                    <!-- Ringkasan Trafik Pengunjung & Konversi -->
                    ${globalVisitorStats && globalVisitorStats.total_visitors !== undefined ? `
                    <div style="background: #faf8f5; border: 1px solid #d4d0c8; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 9px;">
                        <div>
                            <strong style="color: #000; text-transform: uppercase;">Trafik Pengunjung E-Commerce:</strong>
                            <span style="color: #555555; margin-left: 5px;">${globalVisitorStats.period_label || recap.period_label}</span>
                        </div>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <span>Total Pengunjung: <strong>${globalVisitorStats.total_visitors || 0} Calon Pembeli</strong></span>
                            <span>•</span>
                            <span>Interaksi AI CS &amp; PO: <strong style="color: #047857;">${globalVisitorStats.interactive_count || 0} (${globalVisitorStats.conversion_rate || 0}%)</strong></span>
                            <span>•</span>
                            <span>Browsing Saja: <strong>${globalVisitorStats.just_browsing_count || 0}</strong></span>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Tabel 1: Rincian Transaksi Penjualan Mesin -->
                    <div class="section-title">Rincian Transaksi Penjualan Mesin Roasting (Itemized Orders)</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 26px; text-align: center;">No</th>
                                <th style="width: 120px;">No. Order &amp; Tgl</th>
                                <th>Pelanggan</th>
                                <th>Unit Mesin &amp; Qty</th>
                                <th class="text-right" style="width: 110px;">DP 20% Masuk</th>
                                <th class="text-right" style="width: 110px;">Pelunasan 80%</th>
                                <th class="text-right" style="width: 115px;">Total Kontrak</th>
                                <th style="text-align: center; width: 115px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${txRowsHtml}
                        </tbody>
                        <tfoot>
                            <tr style="background: #f0f0f0; font-weight: 900; border-top: 1.5px solid #000;">
                                <td colspan="4" style="text-transform: uppercase;">Total Transaksi (${txList.length} Transaksi)</td>
                                <td class="text-right" style="color: #047857;">${formatRupiah(recap.total_dp)}</td>
                                <td class="text-right" style="color: #444444;">${formatRupiah(Math.max(0, recap.total_omzet - recap.total_dp))}</td>
                                <td class="text-right" style="color: #000; font-size: 11px;">${formatRupiah(recap.total_omzet)}</td>
                                <td style="text-align: center; font-size: 9px;">${recap.total_units} Unit</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Tabel 2: Distribusi 12 Bulan (Optional untuk Filter Tahunan) -->
                    ${monthlySectionHtml}

                    <!-- Ketentuan & Legalitas Finansial -->
                    <div class="notice-box">
                        <div class="notice-title">Ketentuan Pembayaran &amp; Garansi Produksi RJ TEKNIK:</div>
                        <ol class="notice-list">
                            <li>Uang Muka (DP 20%) diverifikasi secara otomatis melalui Payment Gateway resmi Midtrans dan mengikat jadwal perakitan unit mesin.</li>
                            <li>Pelunasan 80% dibayarkan setelah perakitan mesin selesai, lulus uji coba operasional (QC), dan sebelum unit dikirimkan.</li>
                            <li>Setiap unit mesin roasting RJ TEKNIK memiliki garansi servis resmi serta dukungan suku cadang berkelanjutan.</li>
                        </ol>
                    </div>

                    <!-- Area Tanda Tangan & Cap Validasi -->
                    <div class="signature-area">
                        <div class="stamp-box">
                            <div class="stamp-title">RJ TEKNIK ENGINEERING</div>
                            <div class="stamp-sub">OFFICIAL VERIFIED WORKSHOP</div>
                            <div style="font-size: 9px; margin-top: 2px; font-weight: 700; color: #000000;">CV. PODO MORO SUMBER REZEKI</div>
                            <div style="font-size: 8px; color: #444444; margin-top: 1px;">Gresik, Jawa Timur – Indonesia</div>
                        </div>

                        <div style="text-align: right; width: 250px;">
                            <div style="font-size: 9.5px; color: #444444;">Gresik, ${printDate}</div>
                            <div style="font-size: 10px; font-weight: 800; color: #000000; margin-top: 1px;">Divisi Administrasi &amp; Finansial</div>
                            <div style="height: 38px; display: flex; align-items: center; justify-content: flex-end;">
                                <span style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 22px; color: #000000;">RJ Teknik Workshop</span>
                            </div>
                            <div style="font-weight: 800; font-size: 10.5px; color: #000000; text-decoration: underline;">CV. PODO MORO SUMBER REZEKI</div>
                            <div style="font-size: 8.5px; color: #555555;">Workshop Sedapurklagen Benjeng Gresik</div>
                        </div>
                    </div>

                    <div class="footer-note">
                        Faktur &amp; Laporan Rekapitulasi ini dicetak sah dari Sistem Informasi Operasional RJ TEKNIK. Dokumen ini dapat digunakan sebagai arsip keuangan dan lampiran bukti transaksi resmi.
                    </div>
                </div>

                <script>
                    window.addEventListener('load', function() {
                        setTimeout(function() {
                            window.print();
                        }, 400);
                    });
                <\/script>
            </body>
            </html>
            `;

            printWindow.document.open();
            printWindow.document.write(htmlContent);
            printWindow.document.close();
        }
    </script>

    <!-- Modal Detail Transaksi Rekapitulasi Penjualan -->
    <div id="modal-transaction-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="bg-white border border-[#E8E4DE] rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl relative my-8">
            <div class="flex items-center justify-between border-b border-[#E8E4DE] pb-4 mb-6">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-[#8C5E3C]/10 text-[#8C5E3C] border border-[#8C5E3C]/20">
                        Laporan Detail Transaksi
                    </span>
                    <h3 id="td-modal-id" class="text-xl font-black text-[#18181B] mt-1 font-mono">PO-XXXX</h3>
                    <p id="td-modal-date" class="text-xs text-[#78716C] mt-0.5">Tanggal Transaksi: -</p>
                </div>
                <button type="button" onclick="closeTransactionDetailModal()" class="w-8 h-8 rounded-full bg-[#FAF9F5] border border-[#E8E4DE] text-[#78716C] hover:text-[#18181B] hover:bg-[#E8E4DE] flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <!-- Info Pelanggan & Status Perakitan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-[#FAF9F5] border border-[#E8E4DE] p-4 rounded-2xl">
                    <div>
                        <span class="text-[10px] font-bold text-[#78716C] uppercase tracking-wider block">Pelanggan</span>
                        <p id="td-modal-customer" class="font-bold text-[#18181B] text-sm mt-0.5">-</p>
                        <p id="td-modal-email" class="text-[11px] text-[#57534E]">-</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-[#78716C] uppercase tracking-wider block">Tahap Perakitan Workshop</span>
                        <div id="td-modal-status-badge" class="mt-1">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                </div>

                <!-- Info Produk & Catatan -->
                <div class="border border-[#E8E4DE] rounded-2xl p-4 space-y-2">
                    <span class="text-[10px] font-bold text-[#78716C] uppercase tracking-wider block">Spesifikasi Unit Mesin Roasting Kopi</span>
                    <div class="flex items-center justify-between">
                        <strong id="td-modal-product" class="text-sm font-bold text-[#18181B]">-</strong>
                        <span id="td-modal-qty" class="px-2.5 py-1 rounded-lg bg-[#FAF9F5] border border-[#E8E4DE] font-bold text-[#18181B]">1 Unit</span>
                    </div>
                    <div id="td-modal-note-box" class="bg-amber-50/70 border border-amber-200/70 rounded-xl p-3 text-[11px] text-amber-900 mt-2">
                        <strong class="block mb-0.5 text-[10px] uppercase font-bold text-amber-800">Catatan Workshop:</strong>
                        <span id="td-modal-note">-</span>
                    </div>
                </div>

                <!-- Rincian Finansial & Midtrans -->
                <div class="border border-[#E8E4DE] rounded-2xl p-4 space-y-3">
                    <span class="text-[10px] font-bold text-[#78716C] uppercase tracking-wider block">Rincian Finansial Transaksi & Bukti Midtrans</span>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-[#FAF9F5] p-3 rounded-xl border border-[#E8E4DE]">
                            <span class="text-[10px] text-[#78716C] block font-semibold">Total Kontrak</span>
                            <strong id="td-modal-total" class="text-sm font-black text-[#18181B] block mt-0.5">-</strong>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                            <span class="text-[10px] text-emerald-800 block font-semibold">DP 20% Diterima</span>
                            <strong id="td-modal-dp" class="text-sm font-black text-emerald-700 block mt-0.5">-</strong>
                        </div>
                        <div class="bg-[#FAF9F5] p-3 rounded-xl border border-[#E8E4DE]">
                            <span class="text-[10px] text-[#78716C] block font-semibold">Sisa Pelunasan (80%)</span>
                            <strong id="td-modal-remaining" class="text-sm font-black text-[#8C5E3C] block mt-0.5">-</strong>
                        </div>
                    </div>

                    <div class="bg-[#FAF9F5] border border-[#E8E4DE] rounded-xl p-3 text-[11px] space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#57534E]">Bukti Midtrans (DP 20%):</span>
                            <span id="td-modal-dp-receipt" class="font-mono text-[#18181B] font-semibold text-right">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#57534E]">Bukti Pelunasan (80%):</span>
                            <span id="td-modal-final-receipt" class="font-mono text-[#18181B] font-semibold text-right">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-5 mt-6 border-t border-[#E8E4DE]">
                <button type="button" onclick="closeTransactionDetailModal()" class="bg-[#F3EFEA] hover:bg-[#E8E4DE] text-[#57534E] border border-[#E8E4DE] px-5 py-2.5 rounded-2xl text-xs font-bold transition cursor-pointer">
                    Tutup
                </button>
                <button type="button" id="td-modal-print-btn" onclick="printCurrentDetailOrder()" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-5 py-2.5 rounded-2xl text-xs font-bold transition cursor-pointer shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak Faktur PDF</span>
                </button>
            </div>
        </div>
    </div>
</body>

</html>
