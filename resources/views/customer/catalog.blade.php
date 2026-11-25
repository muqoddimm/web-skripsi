@extends('layouts.app')

@section('title', 'Katalog Mesin Roasting Kopi Pre-Order - RJ TEKNIK')

@section('content')
<div class="bg-[#F7F5F0] py-8 sm:py-12 min-h-screen pb-28 sm:pb-36">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Header Katalog Nuansa Hitam, Putih & Coklat -->
        <div class="border-b border-[#E8E4DE] pb-8">
            <div class="space-y-3 max-w-4xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight uppercase">
                    KATALOG MESIN ROASTING KOPI
                </h1>
                <p class="text-sm sm:text-base text-[#57534E] leading-relaxed">
                    Setiap unit diproduksi dan dirakit presisi menggunakan material <em class="text-[#18181B] font-semibold">drum stainless steel food grade</em>, kompor burner LPG modifikasi hemat bahan bakar, dan sistem pendingin cepat <em class="text-[#18181B] font-semibold">cooling bin</em> untuk menjaga profil cita rasa kopi terbaik Anda.
                </p>
            </div>
        </div>

        <!-- Grid 4 Varian Mesin Roasting (Sesuai Bab 3 Skripsi RJ TEKNIK) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
            @forelse($products ?? [] as $product)
            @php
                $pid = $product['id'] ?? 1;
                $rawPrice = preg_replace('/[^0-9]/', '', (string)($product['price'] ?? '15000000'));
                $fullPrice = (int)$rawPrice;
                $dpPrice = round($fullPrice * 0.20);
                $cap = $product['capacity_kg'] ?? 1;
                $estDays = $product['estimation_days'] ?? 14;
                $imgUrl = $product['image'] ?? asset('img/Kap 1.jpeg');
            @endphp

            <div class="group bg-white border border-[#E8E4DE] hover:border-[#8C5E3C] rounded-3xl p-5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                
                <div class="space-y-4 flex-grow flex flex-col">
                    <!-- Foto Mesin dengan Badge Kapasitas -->
                    <div class="relative overflow-hidden rounded-2xl aspect-[4/3] bg-[#F3EFEA] border border-[#E8E4DE] shrink-0">
                        <img src="{{ $imgUrl }}"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=600&auto=format&fit=crop';"
                             alt="{{ $product['name'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        
                        <div class="absolute top-2.5 left-2.5 bg-white/95 backdrop-blur-xs text-[#8C5E3C] text-[11px] font-bold uppercase px-3 py-1 rounded-full border border-[#E8E4DE] shadow-xs">
                            Kapasitas {{ $cap }} Kg / Batch
                        </div>

                        <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-[11px] text-[#18181B] font-semibold bg-white/95 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-[#E8E4DE]/60 shadow-xs">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#8C5E3C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                                </svg>
                                Estimasi: {{ $estDays }} Hari
                            </span>
                            <span class="bg-[#8C5E3C] text-white px-2 py-0.5 rounded-md font-bold text-[10px]">DP 20%</span>
                        </div>
                    </div>

                    <!-- Judul Mesin & Rekomendasi Kebutuhan -->
                    <div class="flex-grow flex flex-col justify-between space-y-2">
                        <div>
                            <span class="text-[10px] font-bold tracking-widest text-[#8C5E3C] uppercase block">RJ TEKNIK ENGINEERING</span>
                            <h3 class="font-extrabold text-[#18181B] text-base sm:text-lg mt-0.5 group-hover:text-[#8C5E3C] transition-colors leading-snug">
                                {{ $product['name'] }}
                            </h3>
                        </div>
                        @if(!empty($product['recommendation']))
                        <div class="text-[11px] text-[#57534E] leading-relaxed bg-[#FAF9F5] p-2.5 rounded-xl border border-[#E8E4DE]/60 min-h-[58px] flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#C5A070] shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.477.859h4z"/>
                            </svg>
                            <p class="line-clamp-2">
                                <strong class="text-[#8C5E3C] font-bold">Rekomendasi:</strong> {{ $product['recommendation'] }}
                            </p>
                        </div>
                        @else
                        <div class="min-h-[58px] hidden lg:block"></div>
                        @endif
                    </div>
                </div>

                <!-- Bagian Harga & Aksi Pre-Order -->
                <div class="pt-4 border-t border-[#E8E4DE] space-y-3 mt-4">
                    <div class="flex items-baseline justify-between">
                        <div>
                            <span class="text-[10px] text-[#57534E] font-bold uppercase tracking-wider block">Harga Unit</span>
                            <span class="font-black text-[#18181B] text-lg sm:text-xl">Rp {{ number_format($fullPrice, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-[#8C5E3C] font-bold uppercase tracking-wider block">Uang Muka (DP 20%)</span>
                            <span class="font-extrabold text-[#8C5E3C] text-xs sm:text-sm">Rp {{ number_format($dpPrice, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button"
                                onclick="openDetailModal({{ json_encode($product) }})"
                                class="text-center bg-[#F3EFEA] hover:bg-[#EAE4DC] text-[#18181B] text-xs font-bold py-2.5 px-2 rounded-xl border border-[#E8E4DE] transition flex items-center justify-center cursor-pointer">
                            Detail
                        </button>
                        <a href="{{ route('checkout.view', ['product_id' => $pid]) }}"
                           class="text-center bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-bold py-2.5 px-2 rounded-xl transition shadow-xs flex items-center justify-center">
                            Pre-Order
                        </a>
                    </div>
                </div>

            </div>
            @empty
            <div class="col-span-full py-16 text-center text-[#57534E] bg-white border border-[#E8E4DE] rounded-3xl">
                <span class="text-4xl block mb-2">☕</span>
                <p class="text-sm font-bold text-[#18181B]">Katalog mesin sedang dimuat dari basis data.</p>
            </div>
            @endforelse
        </div>

        <!-- Box Panduan Pre-Order & Pembayaran 2 Tahap -->
        <div class="bg-white text-[#18181B] rounded-3xl p-8 sm:p-10 border border-[#E8E4DE] shadow-sm">
            <div class="max-w-4xl mx-auto space-y-6">
                <div class="text-center space-y-2">
                    <span class="text-[#8C5E3C] text-xs font-black uppercase tracking-widest">SISTEM TRANSAKSI RESMI</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-[#18181B]">
                        Alur Pemesanan Mesin Roasting Pre-Order (PO)
                    </h2>
                    <p class="text-xs sm:text-sm text-[#57534E]">
                        Transparan, aman, dan terverifikasi otomatis menggunakan Payment Gateway Midtrans.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 pt-4 text-center">
                    <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-2">
                        <div class="w-10 h-10 rounded-full bg-[#8C5E3C] text-white flex items-center justify-center font-black mx-auto">1</div>
                        <h4 class="text-xs font-black uppercase text-[#18181B]">Pilih & Checkout</h4>
                        <p class="text-[11px] text-[#57534E]">Tentukan varian kapasitas, isi alamat kirim & catatan kustom spesifikasi.</p>
                    </div>

                    <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-2">
                        <div class="w-10 h-10 rounded-full bg-[#8C5E3C] text-white flex items-center justify-center font-black mx-auto">2</div>
                        <h4 class="text-xs font-black uppercase text-[#18181B]">Bayar DP 20%</h4>
                        <p class="text-[11px] text-[#57534E]">Bayar uang muka via Midtrans Snap (VA / QRIS / E-Wallet). Status terverifikasi instan.</p>
                    </div>

                    <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-2">
                        <div class="w-10 h-10 rounded-full bg-[#8C5E3C] text-white flex items-center justify-center font-black mx-auto">3</div>
                        <h4 class="text-xs font-black uppercase text-[#18181B]">Proses Perakitan</h4>
                        <p class="text-[11px] text-[#57534E]">Teknisi RJ TEKNIK merakit unit mesin sesuai estimasi hari kerja.</p>
                    </div>

                    <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-2">
                        <div class="w-10 h-10 rounded-full bg-[#8C5E3C] text-white flex items-center justify-center font-black mx-auto">4</div>
                        <h4 class="text-xs font-black uppercase text-[#18181B]">Pelunasan & Kirim</h4>
                        <p class="text-[11px] text-[#57534E]">Saat mesin selesai, lunasi sisa 80% via sistem lalu mesin dikirim kargo.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL DETAIL & REKOMENDASI MESIN LENGKAP -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white border border-[#E8E4DE] text-[#18181B] rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-6 my-8 max-h-[90vh] overflow-y-auto">
        <!-- Header Modal -->
        <div class="flex items-start justify-between border-b border-[#E8E4DE] pb-4">
            <div>
                <span class="text-[10px] font-black tracking-widest text-[#8C5E3C] uppercase block">RJ TEKNIK ENGINEERING &bull; DETAIL UNIT</span>
                <h3 id="modal-product-name" class="text-xl sm:text-2xl font-black text-[#18181B] mt-0.5"></h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-[#57534E] hover:text-[#18181B] text-xl font-bold p-1 cursor-pointer transition">✕</button>
        </div>

        <!-- Foto & Info Ringkas Harga -->
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-5 items-center">
            <div class="sm:col-span-5 relative aspect-[4/3] rounded-2xl overflow-hidden bg-[#F3EFEA] border border-[#E8E4DE]">
                <img id="modal-product-image" src="" alt="Mesin Roasting" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('img/Kap 1.jpeg') }}';">
                <div id="modal-product-cap" class="absolute top-2.5 left-2.5 bg-white/95 backdrop-blur-xs text-[#8C5E3C] text-[11px] font-bold uppercase px-3 py-1 rounded-full border border-[#E8E4DE] shadow-xs"></div>
            </div>

            <div class="sm:col-span-7 space-y-3">
                <div class="bg-[#FAF9F5] p-4 rounded-2xl border border-[#E8E4DE] space-y-2.5">
                    <div class="flex items-baseline justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#57534E] block">Harga Unit</span>
                            <span id="modal-product-price" class="text-xl font-black text-[#18181B]"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C5E3C] block">Uang Muka (DP 20%)</span>
                            <span id="modal-product-dp" class="text-sm font-extrabold text-[#8C5E3C]"></span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-[#E8E4DE] text-xs text-[#57534E] flex items-center justify-between">
                        <span>Estimasi Pengerjaan:</span>
                        <strong id="modal-product-est" class="text-[#18181B] font-bold"></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- REKOMENDASI KEBUTUHAN USAHA (LENGKAP TANPA TERPOTONG) -->
        <div class="bg-[#FBF8F3] border-2 border-[#C5A070]/50 rounded-2xl p-5 space-y-2">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-[#8C5E3C] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.477.859h4z"/>
                </svg>
                <h4 class="font-black text-xs uppercase tracking-wider text-[#8C5E3C]">Rekomendasi Kebutuhan Usaha Lengkap</h4>
            </div>
            <p id="modal-product-rec" class="text-sm text-[#18181B] leading-relaxed font-medium"></p>
        </div>

        <!-- SPESIFIKASI TEKNIS FABRIKASI -->
        <div class="space-y-2.5">
            <h4 class="font-black text-xs uppercase tracking-wider text-[#57534E]">Spesifikasi Teknis Fabrikasi RJ TEKNIK:</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                <div class="bg-[#FAF9F5] p-3 rounded-xl border border-[#E8E4DE]">
                    <span class="text-[10px] text-[#57534E] font-bold block uppercase">Material Drum</span>
                    <span id="modal-spec-drum" class="font-semibold text-[#18181B] mt-0.5 block"></span>
                </div>
                <div class="bg-[#FAF9F5] p-3 rounded-xl border border-[#E8E4DE]">
                    <span class="text-[10px] text-[#57534E] font-bold block uppercase">Transmisi & Penggerak</span>
                    <span id="modal-spec-gearbox" class="font-semibold text-[#18181B] mt-0.5 block"></span>
                </div>
                <div class="bg-[#FAF9F5] p-3 rounded-xl border border-[#E8E4DE]">
                    <span class="text-[10px] text-[#57534E] font-bold block uppercase">Sistem Burner (Pengapian)</span>
                    <span id="modal-spec-burner" class="font-semibold text-[#18181B] mt-0.5 block"></span>
                </div>
                <div class="bg-[#FAF9F5] p-3 rounded-xl border border-[#E8E4DE]">
                    <span class="text-[10px] text-[#57534E] font-bold block uppercase">Sistem Pendingin (Cooling Bin)</span>
                    <span id="modal-spec-cooling" class="font-semibold text-[#18181B] mt-0.5 block"></span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Modal -->
        <div class="pt-4 border-t border-[#E8E4DE] flex items-center justify-end gap-3">
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 rounded-xl border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] font-bold text-xs transition cursor-pointer">
                Tutup
            </button>
            <a id="modal-checkout-btn" href="#" class="px-6 py-2.5 rounded-xl bg-[#8C5E3C] hover:bg-[#744A29] text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                <span>Pre-Order Sekarang</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</div>

<script>
function openDetailModal(product) {
    const modal = document.getElementById('detail-modal');
    if (!modal || !product) return;

    const rawPrice = parseInt(String(product.price || 0).replace(/[^0-9]/g, '')) || 0;
    const dpPrice = Math.round(rawPrice * 0.20);
    const formattedPrice = 'Rp ' + rawPrice.toLocaleString('id-ID');
    const formattedDp = 'Rp ' + dpPrice.toLocaleString('id-ID');
    const estDays = (product.estimation_days || 14) + ' Hari Kerja';

    let recText = (product.recommendation && product.recommendation.trim() !== '') ? product.recommendation.trim() : '';
    if (!recText && product.description && product.description.trim() !== '') {
        recText = product.description.trim();
    }
    if (!recText) {
        recText = 'Sangat direkomendasikan untuk kebutuhan sangrai kopi komersial presisi, efisien dalam konsumsi bahan bakar, dan teruji handal untuk operasional harian RJ TEKNIK.';
    }

    document.getElementById('modal-product-name').textContent = product.name || 'Mesin Roasting Kopi';
        let imgSrc = product.image || '{{ asset('img/Kap 1.jpeg') }}';
    if (typeof imgSrc === 'string' && (imgSrc.includes('127.0.0.1:8000/uploads/') || imgSrc.includes('localhost:8000/uploads/'))) {
        imgSrc = imgSrc.replace(/^https?:\/\/[^\/]+/, '');
    }
    const modalImg = document.getElementById('modal-product-image');
    if (modalImg) {
        modalImg.onerror = function() {
            this.onerror = null;
            this.src = '{{ asset('img/Kap 1.jpeg') }}';
        };
        modalImg.src = imgSrc;
    }
    document.getElementById('modal-product-cap').textContent = 'Kapasitas ' + (product.capacity_kg || 1) + ' Kg / Batch';
    document.getElementById('modal-product-price').textContent = formattedPrice;
    document.getElementById('modal-product-dp').textContent = formattedDp;
    document.getElementById('modal-product-est').textContent = estDays;
    document.getElementById('modal-product-rec').textContent = recText;

    document.getElementById('modal-spec-drum').textContent = product.drum_material || 'Full Stainless Steel Food Grade';
    document.getElementById('modal-spec-gearbox').textContent = product.gearbox_ratio || 'Dinamo & Transmisi Gearbox';
    document.getElementById('modal-spec-burner').textContent = product.burner_type || 'Kompor LPG Modifikasi Khusus Sangrai';
    document.getElementById('modal-spec-cooling').textContent = product.cooling_bin_spec || 'Cooling Bin Stainless Steel';

    const checkoutBaseUrl = "{{ route('checkout.view') }}";
    document.getElementById('modal-checkout-btn').href = checkoutBaseUrl + '?product_id=' + (product.id || 1);

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    const modal = document.getElementById('detail-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailModal();
    }
});

document.getElementById('detail-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailModal();
    }
});
</script>
@endsection
