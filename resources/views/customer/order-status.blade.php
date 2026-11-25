@extends('layouts.app')

@section('title', 'Status Pesanan - RJ TEKNIK')

@section('content')
<div class="bg-[#F7F5F0] py-12 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header Halaman -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-[#E8E4DE] pb-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-[#18181B] tracking-tight">
                    Status Pesanan & Produksi Mesin
                </h1>
                <p class="text-sm text-[#57534E] mt-1">
                    Riwayat pesanan resmi akun <strong class="text-[#18181B]">{{ session('user_email', session('user_name', Auth::user()->name ?? 'Pelanggan')) }}</strong>.
                </p>
            </div>
            <a href="{{ route('catalog') }}" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-bold px-5 py-3 rounded-xl transition shadow-sm self-start md:self-auto">
                + Buat Pre-Order Baru
            </a>
        </div>

        <div class="space-y-8">
            @php
            $stepLabels = [
                1 => ['title' => 'Pesanan Dibuat', 'desc' => 'Menunggu Pembayaran DP 20%', 'badge' => 'Menunggu Pembayaran DP', 'color' => 'bg-amber-50 text-amber-800 border-amber-200'],
                2 => ['title' => 'DP 20% Terverifikasi', 'desc' => 'Masuk Antrean Fabrikasi', 'badge' => 'DP Lunas • Antrean Workshop', 'color' => 'bg-[#F3EFEA] text-[#18181B] border-[#E8E4DE]'],
                3 => ['title' => 'Proses Perakitan', 'desc' => 'Fabrikasi Drum & Mekanik', 'badge' => 'Sedang Dirakit di Workshop', 'color' => 'bg-[#F3EFEA] text-[#8C5E3C] border-[#8C5E3C]/40'],
                4 => ['title' => 'Siap Pelunasan', 'desc' => 'Perakitan Selesai • Bayar 80%', 'badge' => 'Perakitan Selesai • Menunggu Pelunasan', 'color' => 'bg-[#8C5E3C] text-white border-transparent'],
                5 => ['title' => 'Lunas & Dikirim', 'desc' => 'Lunas 100% & Menuju Alamat', 'badge' => 'Lunas 100% • Dalam Pengiriman Kargo', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
            ];
            @endphp

            @forelse($orders ?? [] as $order)
            @php
                $oid = $order['id'] ?? 'PO-20261027-01';
                $cname = $order['customer_name'] ?? session('user_name', Auth::user()->name ?? 'Pelanggan');
                $pname = $order['product_name'] ?? 'Mesin Roasting Kopi Kapasitas 3 Kg';
                $dpAmount = (int)($order['dp_price'] ?? 14000000);
                $totalAmount = (int)($order['total_price'] ?? 28000000);
                $remainingAmount = $totalAmount - $dpAmount;
                $currentStep = max(1, min(5, (int)($order['step'] ?? 2)));
                $odate = $order['date'] ?? date('d M Y');
                $onote = $order['note'] ?? 'Pesanan sedang dalam proses produksi di workshop RJ TEKNIK.';
                $dpReceipt = $order['dp_receipt'] ?? null;
                $finalReceipt = $order['final_receipt'] ?? null;
                $currentMeta = $stepLabels[$currentStep] ?? $stepLabels[2];
                $payUrl = route('order.pay', $oid);

                $formattedDp = 'Rp ' . number_format($dpAmount, 0, ',', '.');
                $formattedTotal = 'Rp ' . number_format($totalAmount, 0, ',', '.');
                $formattedRemaining = 'Rp ' . number_format($remainingAmount, 0, ',', '.');

                $progressPercent = (($currentStep - 1) / 4) * 100;
            @endphp

            <div class="bg-white border border-[#E8E4DE] hover:border-[#8C5E3C] rounded-3xl p-6 sm:p-8 shadow-sm hover:shadow-md space-y-8 transition duration-200">

                <!-- Header Kartu Order -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#E8E4DE] pb-6">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2.5 text-xs">
                            <span class="font-mono font-black text-[#18181B] bg-[#F7F5F0] px-3 py-1 rounded-lg border border-[#E8E4DE]">
                                {{ $oid }}
                            </span>
                            <span class="text-[#E8E4DE]">•</span>
                            <span class="text-[#57534E] font-medium">Tanggal: {{ $odate }}</span>
                            <span class="text-[#E8E4DE]">•</span>
                            <span class="text-[#18181B] font-bold">Pemesan: {{ $cname }}</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-[#18181B] pt-1">
                            {{ $pname }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-3 self-start lg:self-center">
                        <span class="text-xs font-black px-4 py-2 rounded-full border shadow-2xs {{ $currentMeta['color'] }}">
                            {{ $currentMeta['badge'] }}
                        </span>
                    </div>
                </div>

                <!-- Stepper Tahapan Produksi (5 Langkah) -->
                <div class="py-4 px-2">
                    <div class="relative">
                        <!-- Garis Penghubung Horizontal -->
                        <div class="hidden sm:block absolute top-5 left-[10%] right-[10%] h-1.5 bg-[#E8E4DE] rounded-full z-0 overflow-hidden">
                            <div class="h-full bg-[#8C5E3C] rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%;"></div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 sm:gap-2 relative z-10">
                            @foreach($stepLabels as $stepNumber => $stepInfo)
                            @php
                                $isCompleted = $currentStep > $stepNumber;
                                $isCurrent = $currentStep === $stepNumber;
                            @endphp
                            <div class="relative flex sm:flex-col items-center sm:text-center gap-4 sm:gap-3 p-3 sm:p-0 rounded-2xl {{ $isCurrent ? 'bg-[#F7F5F0] sm:bg-transparent border border-[#8C5E3C]/40 sm:border-none' : '' }}">
                                <!-- Lingkaran Step -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs shrink-0 transition-all duration-300 shadow-2xs
                                    {{ $isCompleted ? 'bg-white text-[#8C5E3C] border-2 border-[#8C5E3C]' : ($isCurrent ? 'bg-[#8C5E3C] text-white ring-4 ring-[#8C5E3C]/20' : 'bg-[#F7F5F0] text-[#A8A29E] border-2 border-[#E8E4DE]') }}">
                                    @if($isCompleted)
                                        ✓
                                    @else
                                        {{ $stepNumber }}
                                    @endif
                                </div>
                                <div class="space-y-0.5">
                                    <h4 class="text-xs font-black text-[#18181B]">{{ $stepInfo['title'] }}</h4>
                                    <p class="text-[10px] text-[#57534E] hidden sm:block">{{ $stepInfo['desc'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Rincian Finansial & Catatan Perakitan -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-[#F7F5F0] p-6 rounded-2xl border border-[#E8E4DE] text-xs">
                    <div class="space-y-1">
                        <span class="text-[#57534E] uppercase font-bold tracking-wider text-[10px] block">Rincian Finansial Mesin</span>
                        <p class="text-[#18181B]"><strong class="text-[#57534E]">Total Nilai:</strong> {{ $formattedTotal }}</p>
                        <p class="text-[#18181B]"><strong class="text-[#57534E]">DP 20% (Awal):</strong> {{ $formattedDp }}</p>
                        <p class="text-[#8C5E3C] font-extrabold"><strong class="text-[#57534E]">Sisa Pelunasan:</strong> {{ $formattedRemaining }}</p>
                    </div>

                    <div class="space-y-1 md:col-span-2">
                        <span class="text-[#57534E] uppercase font-bold tracking-wider text-[10px] block">Status Pengerjaan Workshop</span>
                        <p class="text-[#18181B] leading-relaxed">{{ $onote }}</p>
                        @if($dpReceipt)
                        <p class="text-[11px] text-[#57534E] mt-2"><strong>Bukti DP:</strong> <span class="font-mono bg-white text-[#18181B] px-2 py-0.5 rounded border border-[#E8E4DE]">{{ $dpReceipt }}</span></p>
                        @endif
                        @if($finalReceipt)
                        <p class="text-[11px] text-emerald-800 mt-1"><strong>Bukti Pelunasan:</strong> <span class="font-mono bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded border border-emerald-200">{{ $finalReceipt }}</span></p>
                        @endif
                    </div>

                    <!-- Tombol Download Riwayat Invoice (Pojok Bawah Kanan Kotak) -->
                    <div class="pt-4 border-t border-[#E8E4DE] md:col-span-3 flex items-center justify-between gap-4">
                        <span class="text-[11px] text-[#78716C] hidden sm:inline">
                            Faktur resmi & arsip riwayat pengerjaan mesin roasting RJ TEKNIK.
                        </span>
                        <button type="button"
                                onclick="downloadOrderInvoice({{ json_encode($order) }})"
                                class="ml-auto inline-flex items-center gap-2 bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-xs cursor-pointer select-none">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Download Riwayat</span>
                        </button>
                    </div>
                </div>

                <!-- Tombol Aksi Pembayaran Pelunasan (Aktif jika Step 4 / Siap Pelunasan) -->
                @if($currentStep === 4)
                <div class="bg-[#F3EFEA] text-[#18181B] p-6 rounded-2xl border border-[#8C5E3C] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-[#8C5E3C] text-xs font-black uppercase tracking-wider block">MESIN SELESAI DIRAKIT</span>
                        <h4 class="text-lg font-black text-[#18181B] mt-1">Lakukan Pembayaran Pelunasan (80%)</h4>
                        <p class="text-xs text-[#57534E]">Sisa tagihan: <strong class="text-[#18181B]">{{ $formattedRemaining }}</strong> via Midtrans Multi-Payment.</p>
                    </div>
                    <form action="{{ $payUrl }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_type" value="final">
                        <button type="submit"
                                class="bg-[#8C5E3C] hover:bg-[#744A29] text-white font-black px-6 py-3.5 rounded-xl text-xs uppercase tracking-wider transition shadow-md cursor-pointer">
                            Bayar Pelunasan Sekarang ({{ $formattedRemaining }})
                        </button>
                    </form>
                </div>
                @elseif($currentStep === 1)
                <div class="bg-[#F3EFEA] border border-amber-300 p-5 rounded-2xl flex items-center justify-between">
                    <p class="text-xs text-amber-900 font-bold">Menunggu penyelesaian pembayaran DP 20% ({{ $formattedDp }}).</p>
                    <form action="{{ $payUrl }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_type" value="dp">
                        <button type="submit" class="bg-[#8C5E3C] text-white hover:bg-[#744A29] px-4 py-2 rounded-xl text-xs font-bold">
                            Bayar DP Sekarang
                        </button>
                    </form>
                </div>
                @endif

            </div>
            @empty
            <div class="py-20 text-center bg-white border border-[#E8E4DE] rounded-3xl p-8 space-y-4">
                <span class="text-5xl block">☕</span>
                <h3 class="text-lg font-black text-[#18181B]">Belum Ada Pre-Order yang Tercatat</h3>
                <p class="text-xs text-[#57534E] max-w-md mx-auto">
                    Anda belum memiliki pesanan mesin roasting kopi aktif. Silakan jelajahi katalog kami untuk memulai pemesanan.
                </p>
                <a href="{{ route('catalog') }}" class="inline-block bg-[#8C5E3C] text-white hover:bg-[#744A29] px-6 py-3 rounded-xl text-xs font-bold transition">
                    Buka Katalog Pre-Order
                </a>
            </div>
            @endforelse
        </div>

    </div>
</div>

<script>
function downloadOrderInvoice(order) {
    if (!order) return;

    const oid = order.id || 'PO-2026';
    const cname = order.customer_name || 'Pelanggan RJ TEKNIK';
    const cemail = order.customer_email || '{{ session("user_email", "") }}' || '-';
    const pname = order.product_name || 'Mesin Roasting Kopi Komersial';
    const qty = order.quantity || 1;
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
</script>
@endsection