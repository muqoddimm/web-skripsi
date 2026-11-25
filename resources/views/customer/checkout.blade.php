@extends('layouts.app')

@section('title', 'Checkout Pre-Order Mesin Roasting - RJ TEKNIK')

@section('content')
@php
    $productId = $selectedProduct['id'] ?? '1';
    $productName = $selectedProduct['name'] ?? 'Mesin Roasting Kopi Kapasitas 1 Kg';
    $productImage = $selectedProduct['image'] ?? asset('img/Kap 1.jpeg');

    $unitPrice = (int) preg_replace('/[^0-9]/', '', (string)($selectedProduct['price'] ?? '15000000'));
    if ($unitPrice <= 0) {
        $unitPrice = 15000000;
    }
    $fullPrice = $unitPrice;
    $dpPrice = (int) round($fullPrice * 0.20); // DP 20% sesuai Bab 3 Skripsi RJ TEKNIK
    $remainingPrice = $fullPrice - $dpPrice;
@endphp

<div class="bg-[#F7F5F0] py-12 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div>
            <span class="inline-block text-xs font-bold tracking-widest text-[#8C5E3C] uppercase bg-white border border-[#E8E4DE] px-4 py-1.5 rounded-full shadow-2xs">
                ☕ CHECKOUT PRE-ORDER
            </span>
            <h1 class="text-2xl sm:text-4xl font-black text-[#18181B] mt-3">
                Konfirmasi Pre-Order & Pembayaran Uang Muka (DP 20%)
            </h1>
            <p class="text-[#57534E] text-sm mt-1">
                Selesaikan pembayaran Down Payment (DP 20%) melalui layanan Midtrans Payment Gateway untuk memulai proses perakitan unit mesin roasting kopi Anda.
            </p>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Pengiriman & Data Pemesan -->
            <div class="lg:col-span-2 bg-white border border-[#E8E4DE] rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                <h2 class="text-lg font-black text-[#18181B] border-b border-[#E8E4DE] pb-4 flex items-center gap-2">
                    <span class="text-[#8C5E3C]">1.</span> Data Pengiriman & Kustomisasi Spesifikasi Mesin
                </h2>

                <form id="checkout-form"
                    data-unit-price="{{ $unitPrice }}"
                    data-base-name="{{ $productName }}"
                    data-store-url="{{ route('preorder.store') }}"
                    data-success-url="{{ route('preorder.success') }}"
                    data-csrf="{{ csrf_token() }}"
                    class="space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $productId }}">
                    <input type="hidden" name="product_name" id="input-product-name" value="{{ $productName }}">
                    <input type="hidden" name="dp_price" id="input-dp-price" value="{{ $dpPrice }}">
                    <input type="hidden" name="total_price" id="input-total-price" value="{{ $fullPrice }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#57534E] uppercase mb-2">Nama Lengkap Pemesan / Roastery</label>
                            <input type="text" name="name" value="{{ session('user_name', Auth::user()->name ?? '') }}" required
                                placeholder="Nama Lengkap Pemesan"
                                class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3 text-sm text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#57534E] uppercase mb-2">Nomor Telepon / Kontak Pemesan</label>
                            <input type="text" name="phone" value="{{ session('user_phone', Auth::user()->phone ?? '') }}" required
                                placeholder="Nomor HP Pemesan"
                                class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3 text-sm text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#57534E] uppercase mb-2">Alamat Lengkap Pengiriman Kargo Unit Mesin</label>
                        <textarea name="shipping_address" rows="3" required
                            placeholder="Alamat lengkap tujuan kargo mesin"
                            class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3 text-sm text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">{{ session('user_address', Auth::user()->address ?? '') }}</textarea>
                    </div>

                    <!-- Jumlah Unit & Catatan Spesifikasi Kustom -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#57534E] uppercase mb-2">Jumlah Unit (Unit)</label>
                            <div class="relative flex items-center bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl overflow-hidden focus-within:border-[#8C5E3C] transition">
                                <input type="number" id="quantity-input" name="quantity" value="1" min="1" max="99" required
                                    oninput="hitungUlangHarga()" onchange="hitungUlangHarga()"
                                    class="w-full bg-transparent px-4 py-3 text-sm font-black text-[#18181B] focus:outline-none">
                                <div class="flex flex-col border-l border-[#E8E4DE] bg-[#F3EFEA] select-none">
                                    <button type="button" onclick="ubahJumlah(1)" title="Tambah"
                                        class="px-3 py-1 hover:bg-[#8C5E3C] hover:text-white text-[#18181B] transition border-b border-[#E8E4DE] cursor-pointer text-xs font-bold">
                                        ▲
                                    </button>
                                    <button type="button" onclick="ubahJumlah(-1)" title="Kurang"
                                        class="px-3 py-1 hover:bg-[#8C5E3C] hover:text-white text-[#18181B] transition cursor-pointer text-xs font-bold">
                                        ▼
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-[#57534E] uppercase mb-2">Catatan Kustom Spesifikasi (Opsional)</label>
                            <input type="text" name="customer_notes"
                                placeholder="Contoh: Warna bodi hitam doff, regulator SNI tekanan tinggi..."
                                class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3 text-sm text-[#18181B] placeholder-[#57534E]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                    </div>

                    <!-- Aksi Tombol Pembayaran -->
                    <div class="pt-4 border-t border-[#E8E4DE] flex flex-col sm:flex-row items-center gap-4">
                        <button type="submit" id="pay-button"
                            class="w-full sm:flex-1 bg-[#8C5E3C] hover:bg-[#744A29] text-white font-bold py-4 px-6 rounded-2xl text-sm transition shadow-md cursor-pointer flex items-center justify-center gap-2">
                            <span>💳</span>
                            <span id="pay-button-text">Buat Pesanan Pre-Order & Bayar DP 20% (Rp {{ number_format($dpPrice, 0, ',', '.') }})</span>
                        </button>
                    </div>

                    <!-- Sandbox Offline Simulator Button (Untuk Pengujian & Demo) -->
                    <div class="bg-[#F7F5F0] p-3.5 rounded-xl border border-[#E8E4DE] flex items-center justify-between text-xs">
                        <span class="text-[#57534E] font-semibold">
                            🧪 Mode Pengujian Sandbox:
                        </span>
                        <button type="button" onclick="simulasikanBayarBerhasil()"
                            class="bg-[#F3EFEA] hover:bg-[#8C5E3C] text-[#18181B] hover:text-white border border-[#E8E4DE] font-bold px-4 py-2 rounded-lg transition cursor-pointer shadow-2xs">
                            Simulasikan Bayar DP Berhasil (Demo)
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ringkasan Pesanan (Sidebar Kanan) -->
            <div class="bg-white border border-[#E8E4DE] rounded-3xl p-6 shadow-sm space-y-6 self-start">
                <h3 class="font-black text-[#18181B] text-base border-b border-[#E8E4DE] pb-3">
                    Ringkasan Tagihan Pre-Order
                </h3>

                <div class="flex gap-4 items-center">
                    <img src="{{ $productImage }}"
                        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=200&auto=format&fit=crop';"
                        alt="{{ $productName }}"
                        class="w-20 h-20 rounded-2xl object-cover border border-[#E8E4DE] bg-[#F7F5F0] shrink-0">
                    <div>
                        <h4 class="font-extrabold text-[#18181B] text-sm leading-snug">{{ $productName }}</h4>
                        <span class="text-xs text-[#8C5E3C] block mt-1 font-semibold">Pre-Order (Perakitan Custom)</span>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-[#57534E] pt-3 border-t border-[#E8E4DE]">
                    <div class="flex justify-between">
                        <span>Jumlah Unit</span>
                        <span id="summary-qty" class="font-extrabold text-[#18181B]">1 Unit</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Harga Mesin (100%)</span>
                        <span id="summary-total" class="font-black text-[#18181B]">Rp {{ number_format($fullPrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between p-3 bg-[#F3EFEA] rounded-xl border border-[#8C5E3C]/40 text-[#18181B] font-black">
                        <span>Tagihan DP 20% (Sekarang)</span>
                        <span id="summary-dp" class="text-[#8C5E3C] text-sm font-black">Rp {{ number_format($dpPrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[#57534E]">
                        <span>Sisa Pelunasan 80% (Saat Mesin Selesai)</span>
                        <span id="summary-remaining" class="font-bold text-[#18181B]">Rp {{ number_format($remainingPrice, 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- SDK JavaScript Snap Midtrans Sandbox -->
<script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('services.midtrans.client_key') }}"></script>

<script>
    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
    }

    function ubahJumlah(delta) {
        const qtyInput = document.getElementById('quantity-input');
        let currentQty = parseInt(qtyInput.value, 10) || 1;
        let newQty = currentQty + delta;

        if (newQty < 1) newQty = 1;
        if (newQty > 99) newQty = 99;

        qtyInput.value = newQty;
        hitungUlangHarga();
    }

    function hitungUlangHarga() {
        const form = document.getElementById('checkout-form');
        const qtyInput = document.getElementById('quantity-input');

        const unitPrice = parseInt(form.getAttribute('data-unit-price'), 10) || 15000000;
        const baseName = form.getAttribute('data-base-name') || 'Mesin Roasting Kopi';

        let qty = parseInt(qtyInput.value, 10);
        if (isNaN(qty) || qty < 1) qty = 1;

        // DP 20% sesuai Bab 3 Skripsi
        const totalPrice = unitPrice * qty;
        const dpPrice = Math.round(totalPrice * 0.20);
        const remainingPrice = totalPrice - dpPrice;

        document.getElementById('summary-qty').textContent = qty + ' Unit';
        document.getElementById('summary-total').textContent = formatRupiah(totalPrice);
        document.getElementById('summary-dp').textContent = formatRupiah(dpPrice);
        document.getElementById('summary-remaining').textContent = formatRupiah(remainingPrice);

        const payBtnText = document.getElementById('pay-button-text');
        if (payBtnText) {
            payBtnText.textContent = 'Buat Pesanan Pre-Order & Bayar DP 20% (' + formatRupiah(dpPrice) + ')';
        }

        document.getElementById('input-total-price').value = totalPrice;
        document.getElementById('input-dp-price').value = dpPrice;
        document.getElementById('input-product-name').value = qty > 1 ? (baseName + ' (' + qty + ' Unit)') : baseName;
    }

    // Submit ke Midtrans Snap
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const storeUrl = form.getAttribute('data-store-url');
        const successUrl = form.getAttribute('data-success-url');
        const csrfToken = form.getAttribute('data-csrf');

        const btn = document.getElementById('pay-button');
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span>Menghubungkan ke Server Midtrans...</span>';

        const formData = new FormData(form);

        fetch(storeUrl, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrfToken },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.snap_token) {
                if (typeof snap !== 'undefined') {
                    snap.pay(data.snap_token, {
                        onSuccess: function() { window.location.href = successUrl; },
                        onPending: function() { window.location.href = successUrl; },
                        onError: function() {
                            alert("Gagal memproses pembayaran Midtrans. Mengalihkan ke demo sukses...");
                            window.location.href = successUrl;
                        },
                        onClose: function() {
                            alert('Anda menutup jendela pembayaran.');
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    });
                } else {
                    window.location.href = successUrl;
                }
            } else {
                alert(data.message || 'Gagal memproses pesanan ke Midtrans. Silakan coba kembali.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            console.error(err);
            simulasikanBayarBerhasil();
        });
    });

    function simulasikanBayarBerhasil() {
        const form = document.getElementById('checkout-form');
        const storeUrl = form.getAttribute('data-store-url');
        const successUrl = form.getAttribute('data-success-url');
        const csrfToken = form.getAttribute('data-csrf');
        const formData = new FormData(form);

        fetch(storeUrl, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrfToken },
            body: formData
        })
        .finally(() => {
            window.location.href = successUrl;
        });
    }
</script>

<style>
    #quantity-input::-webkit-outer-spin-button,
    #quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #quantity-input {
        -moz-appearance: textfield;
    }
</style>
@endsection