@extends('layouts.app')

@section('title', 'Beranda - RJ TEKNIK Commercial Coffee Roaster Engineering')

@section('content')
<div class="space-y-16 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

    <!-- Hero Card: Dark Charcoal Card di atas Latar Terang (Sesuai Desain Canva) -->
    <section class="relative bg-[#161719] text-white overflow-hidden rounded-[32px] p-8 sm:p-14 lg:p-16 shadow-2xl border border-[#2C2D30]">
        <!-- Background Overlay Mesin Roasting -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('img/bg.png') }}"
                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=1200&auto=format&fit=crop';"
                alt="Mesin Roasting RJ TEKNIK"
                class="w-full h-full object-cover opacity-20 filter saturate-150">
            <div class="absolute inset-0 bg-gradient-to-r from-[#161719] via-[#161719]/90 to-transparent"></div>
        </div>

        <!-- Konten Hero Banner -->
        <div class="relative z-10 max-w-3xl space-y-6">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white">
                Teknologi Presisi untuk Setiap Proses Roasting.
            </h1>
            <p class="text-[#D1D5DB] text-sm sm:text-base leading-relaxed max-w-2xl font-normal">
                Hadirkan kualitas sangrai kopi terbaik dengan mesin roasting profesional dari RJ TEKNIK. Dirancang dengan konstruksi tangguh, material stainless steel berkualitas, dan teknologi pengendalian suhu untuk menghasilkan proses roasting yang lebih konsisten, efisien, dan optimal.
            </p>
            <div class="pt-2 flex flex-wrap gap-4 items-center">
                <a href="{{ route('catalog') }}"
                   class="bg-[#8C5E3C] hover:bg-[#744A29] text-white font-bold px-7 py-3.5 rounded-xl text-sm transition shadow-lg hover:scale-105 transform duration-200">
                    Jelajahi Katalog
                </a>
                <a href="{{ route('chatbot.view') }}"
                   class="bg-white/10 hover:bg-white/15 text-white font-bold px-7 py-3.5 rounded-xl text-sm border border-white/20 hover:border-white/40 transition backdrop-blur-xs flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-[#C5A070] animate-pulse"></span>
                    <span>AI Customer Service</span>
                </a>
            </div>

            <!-- Stats Bar Minimalis -->
            <div class="pt-8 flex flex-wrap gap-8 sm:gap-14 border-t border-white/10 max-w-xl text-xs sm:text-sm">
                <div>
                    <span class="block text-2xl sm:text-3xl font-black text-white">20% DP</span>
                    <span class="text-[#A8A29E] font-medium text-xs">PRE-ORDER</span>
                </div>
                <div>
                    <span class="block text-2xl sm:text-3xl font-black text-[#C5A070]">24/7</span>
                    <span class="text-[#A8A29E] font-medium text-xs">CUSTOMER SERVICE</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Showcase Lini Mesin: THE COLLECTION (Bento Grid Putih Bersih - Terhubung Otomatis ke Database) -->
    <section class="space-y-10 pt-4">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E8E4DE] pb-6">
            <div class="space-y-2 max-w-2xl">
                <span class="text-xs font-black tracking-widest text-[#8C5E3C] uppercase block">
                    THE COLLECTION
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-[#18181B] tracking-tight">
                    Pilihan Kapasitas Mesin Sangrai Kopi
                </h2>
            </div>
            <p class="text-[#57534E] text-sm max-w-md leading-relaxed">
                Mulai dari kedai kopi specialty rintisan hingga pabrik pengolahan kopi skala besar, setiap unit dirakit presisi oleh Tim Profesional RJ TEKNIK GRESIK di Kec. Dawar Blandong, Kab. Mojokerto, Jawa Timur.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            @php
            $prod1 = $products[0] ?? [
                'id' => 1,
                'name' => 'Mesin Roasting Kopi Kapasitas 1 Kg',
                'capacity_kg' => 1,
                'price' => '15000000',
                'estimation_days' => 14,
                'image' => asset('img/Kap 1.jpeg'),
                'description' => 'Pilihan tepat untuk kedai kopi mandiri atau uji profil sangrai (*sample roasting*). Tabung full stainless steel double jacket, 2 dinamo listrik, dan kompor LPG modifikasi efisien.',
                'recommendation' => 'Pilihan tepat untuk kedai kopi mandiri atau uji profil sangrai (*sample roasting*).',
            ];

            $prod2 = $products[1] ?? [
                'id' => 2,
                'name' => 'Mesin Roasting Kopi Kapasitas 3 Kg',
                'capacity_kg' => 3,
                'price' => '28000000',
                'estimation_days' => 21,
                'image' => asset('img/kap 5.jpeg'),
                'description' => 'Plat SS 2 mm (Ã˜ 25 x 35 cm), rangka beroda 4, dinamo drum 1/2 HP & cooler 1/4 HP, plus cyclone separator filter.',
            ];

            $prod3 = $products[2] ?? [
                'id' => 3,
                'name' => 'Lini Mesin Roasting Komersial & Industri',
                'capacity_kg' => 10,
                'price' => '48000000',
                'estimation_days' => 30,
                'image' => asset('img/kap 10.jpeg'),
                'description' => 'Dilengkapi blower hisap kuat, siklon penampung kotoran ari, bak pendingin diameter besar, serta potensiometer *speed control*.',
            ];

            $lastProd = !empty($products) ? end($products) : $prod3;
            $maxPrice = (int) preg_replace('/[^0-9]/', '', (string)($lastProd['price'] ?? 85000000));
            $minCommercialPrice = (int) preg_replace('/[^0-9]/', '', (string)($prod3['price'] ?? 48000000));
            $hasMultipleCommercial = count($products ?? []) > 3 || $maxPrice > $minCommercialPrice;
            @endphp

            <!-- Card 1: Kapasitas 1 Kg (5 Kolom) -->
            <div class="lg:col-span-5 bg-white border border-[#E8E4DE] hover:border-[#8C5E3C] rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group">
                <div class="relative h-64 overflow-hidden bg-[#F3EFEA]">
                    <img src="{{ $prod1['image'] ?? asset('img/Kap 1.jpeg') }}"
                        onerror="this.onerror=null; this.src='{{ asset('img/Kap 1.jpeg') }}';"
                        alt="{{ $prod1['name'] }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-xs text-[#8C5E3C] text-[11px] font-bold uppercase px-3.5 py-1 rounded-full border border-[#E8E4DE] shadow-xs">
                        {{ ($prod1['capacity_kg'] ?? 1) <= 2 ? 'Skala UMKM & Sample' : 'Kapasitas ' . ($prod1['capacity_kg'] ?? 1) . ' Kg' }}
                    </span>
                    <span class="absolute bottom-4 right-4 text-white font-extrabold text-sm bg-[#18181B] px-3.5 py-1.5 rounded-xl shadow-md">
                        Rp {{ number_format((int)preg_replace('/[^0-9]/', '', (string)$prod1['price']), 0, ',', '.') }}
                    </span>
                </div>

                <div class="p-6 sm:p-8 space-y-4 flex-1 flex flex-col justify-between">
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C5E3C]">Kapasitas {{ $prod1['capacity_kg'] ?? 1 }} Kg / Batch</span>
                        <h3 class="text-xl font-black text-[#18181B] leading-snug">
                            {{ $prod1['name'] }}
                        </h3>
                        <p class="text-[#57534E] text-xs sm:text-sm leading-relaxed">
                            {{ !empty($prod1['description']) ? $prod1['description'] : ($prod1['recommendation'] ?? 'Pilihan tepat untuk kedai kopi mandiri atau uji profil sangrai (*sample roasting*). Tabung full stainless steel double jacket, 2 dinamo listrik, dan kompor LPG modifikasi efisien.') }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#E8E4DE] flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#57534E]"><svg class="w-3.5 h-3.5 inline text-[#8C5E3C] -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg> Estimasi: {{ $prod1['estimation_days'] ?? 14 }} Hari</span>
                        <a href="{{ route('checkout.view', ['product_id' => $prod1['id'] ?? 1]) }}" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-xs">
                            Pre-Order DP 20%
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 2 & 3 (7 Kolom) -->
            <div class="lg:col-span-7 flex flex-col gap-6">
                <!-- Card 2: Kapasitas 3 Kg -->
                <div class="bg-white border border-[#E8E4DE] hover:border-[#8C5E3C] rounded-3xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col sm:flex-row gap-6 items-center group">
                    <div class="w-full sm:w-48 h-44 rounded-2xl overflow-hidden bg-[#F3EFEA] shrink-0 relative">
                        <img src="{{ $prod2['image'] ?? asset('img/kap 5.jpeg') }}"
                             onerror="this.onerror=null; this.src='{{ asset('img/kap 5.jpeg') }}';"
                             alt="{{ $prod2['name'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <span class="absolute top-2 left-2 bg-white/90 backdrop-blur-xs text-[#8C5E3C] text-[10px] font-bold px-2 py-0.5 rounded-md border border-[#E8E4DE]">
                            {{ $prod2['capacity_kg'] ?? 3 }} Kg / Batch
                        </span>
                    </div>
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-[#8C5E3C] uppercase">Specialty Coffee Shop</span>
                            <span class="font-black text-[#18181B] text-base">
                                Rp {{ number_format((int)preg_replace('/[^0-9]/', '', (string)$prod2['price']), 0, ',', '.') }}
                            </span>
                        </div>
                        <h4 class="font-extrabold text-[#18181B] text-lg">{{ $prod2['name'] }}</h4>
                        <p class="text-xs text-[#57534E] leading-relaxed">
                            {{ !empty($prod2['description']) ? $prod2['description'] : ($prod2['drum_material'] . ', ' . $prod2['gearbox_ratio'] . ', plus cyclone separator filter.') }}
                        </p>
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-xs font-semibold text-[#57534E]"><svg class="w-3.5 h-3.5 inline text-[#8C5E3C] -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg> Estimasi: {{ $prod2['estimation_days'] ?? 21 }} Hari</span>
                            <a href="{{ route('checkout.view', ['product_id' => $prod2['id'] ?? 2]) }}" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-xs">
                                Pre-Order DP 20%
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card 3: 5-10 Kg & 20-25 Kg / Lini Komersial -->
                <div class="bg-white border border-[#E8E4DE] hover:border-[#8C5E3C] rounded-3xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col sm:flex-row gap-6 items-center group">
                    <div class="w-full sm:w-48 h-44 rounded-2xl overflow-hidden bg-[#F3EFEA] shrink-0 relative">
                        <img src="{{ $prod3['image'] ?? asset('img/kap 10.jpeg') }}"
                             onerror="this.onerror=null; this.src='{{ asset('img/kap 10.jpeg') }}';"
                             alt="{{ $prod3['name'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <span class="absolute top-2 left-2 bg-white/90 backdrop-blur-xs text-[#8C5E3C] text-[10px] font-bold px-2 py-0.5 rounded-md border border-[#E8E4DE]">
                            {{ $hasMultipleCommercial ? (($prod3['capacity_kg'] ?? 10) . '–' . ($lastProd['capacity_kg'] ?? 25) . ' Kg') : (($prod3['capacity_kg'] ?? 10) . ' Kg / Batch') }}
                        </span>
                    </div>
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-[#8C5E3C] uppercase">Commercial Roastery</span>
                            <span class="font-black text-[#18181B] text-base">
                                @if($hasMultipleCommercial)
                                Rp {{ number_format($minCommercialPrice, 0, ',', '.') }} – Rp {{ number_format($maxPrice, 0, ',', '.') }}
                                @else
                                Rp {{ number_format($minCommercialPrice, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        <h4 class="font-extrabold text-[#18181B] text-lg">
                            {{ $hasMultipleCommercial ? 'Lini Mesin Roasting Komersial & Industri' : $prod3['name'] }}
                        </h4>
                        <p class="text-xs text-[#57534E] leading-relaxed">
                            {{ !empty($prod3['description']) ? $prod3['description'] : ($prod3['airflow_system'] . ', ' . $prod3['cooling_bin_spec'] . ', serta potensiometer *speed control*.') }}
                        </p>
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-xs font-semibold text-[#57534E]">
                                <svg class="w-3.5 h-3.5 inline text-[#8C5E3C] -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg> Estimasi: {{ $prod3['estimation_days'] ?? 30 }}{{ $hasMultipleCommercial ? '–' . ($lastProd['estimation_days'] ?? 45) : '' }} Hari
                            </span>
                            <a href="{{ route('catalog') }}" class="bg-[#F3EFEA] hover:bg-[#8C5E3C] text-[#18181B] hover:text-white border border-[#E8E4DE] px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-2xs">
                                Lihat Semua Varian
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        </div>
    </section>

    <!-- Section Workshop & Kontak RJ TEKNIK & Daftar Harga -->
    <section id="kontak" class="py-6">
        <div class="bg-white text-[#18181B] rounded-3xl p-8 sm:p-12 border border-[#E8E4DE] shadow-sm grid grid-cols-1 lg:grid-cols-2 gap-10 items-stretch">
            
            <!-- BAGIAN KIRI: PROFIL PABRIK & WORKSHOP -->
            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <span class="text-xs font-black tracking-widest text-[#8C5E3C] uppercase block">
                        PROFIL BENGKEL & WORKSHOP
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-[#18181B] leading-tight tracking-tight uppercase">
                        PABRIKASI MESIN ROASTING KOPI RJ TEKNIK
                    </h3>
                    <p class="text-xs sm:text-sm text-[#57534E] leading-relaxed">
                        Part of CV.Podo Moro Sumber Rezeki dan dikelola oleh PT.Dimas Kreatif Media Mandiri. Kami melayani perakitan custom mesin pemanggang biji kopi komersial dengan jangkauan pengiriman kargo ke seluruh Indonesia.
                    </p>
                </div>

                <!-- Foto Showcase Lineup Mesin Pabrikasi RJ TEKNIK -->
                <div class="relative overflow-hidden rounded-2xl border border-[#E8E4DE] shadow-xs group my-1">
                    <img src="{{ asset('img/workshop-lineup.jpg') }}" 
                         alt="Lineup Mesin Roasting Kopi RJ TEKNIK" 
                         class="w-full h-44 sm:h-52 object-cover object-center transition-transform duration-500 group-hover:scale-103">
                </div>

                <div class="space-y-3 pt-4 border-t border-[#E8E4DE]/70 text-xs sm:text-sm text-[#57534E]">
                    <div>
                        <strong class="text-[#18181B] block font-bold text-sm">Alamat</strong>
                        <p class="leading-relaxed text-[#57534E] mt-1">
                            Desa Sedapurklagen<br>
                            Kabupaten Gresik, Provinsi Jawa Timur – 61172
                        </p>
                    </div>
                    <div class="pt-2 space-y-2 text-xs text-[#57534E]">
                        <p><strong class="text-[#18181B]">Jam Operasional:</strong> Senin – Sabtu: 08.00 – 17.00 WIB</p>
                        <p><strong class="text-[#18181B]">Layanan Konsultasi:</strong> 24 Jam</p>
                        <div class="pt-1">
                            <a href="https://wa.me/6285730417135?text=Halo%20RJ%20TEKNIK,%20saya%20tertarik%20dengan%20mesin%20roasting%20kopi." 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3.5 py-1.5 rounded-xl transition shadow-xs">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                <span>WhatsApp: 0857-3041-7135</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN KANAN: DAFTAR HARGA PRE-ORDER TERINTEGRASI ADMIN -->
            <div class="bg-[#F7F5F0] p-6 sm:p-8 rounded-3xl border border-[#E8E4DE] flex flex-col justify-between shadow-2xs">
                <div>
                    <!-- Header Judul & Subjudul -->
                    <div class="border-b border-[#E8E4DE] pb-4 mb-4 flex items-start justify-between">
                        <div>
                            <span class="text-xs font-black tracking-widest text-[#8C5E3C] uppercase block mb-1">
                                PRICELIST RESMI
                            </span>
                            <h4 class="text-2xl sm:text-3xl font-black text-[#18181B] tracking-tight uppercase">
                                DAFTAR HARGA
                            </h4>
                            <p class="text-xs sm:text-sm font-semibold text-[#8C5E3C] mt-1">
                                Mesin Roasting Kopi Lengkap Dengan Pendingin.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold text-[#78716C] bg-white border border-[#E8E4DE] px-3 py-1 rounded-full shrink-0 shadow-2xs hidden sm:inline-flex items-center gap-1">
                            <span>Scroll untuk lihat</span>
                            <span>↓</span>
                        </span>
                    </div>

                    <!-- Rincian Harga Sesuai Input Admin (Scrollable agar seimbang dengan profil bengkel) -->
                    <div class="max-h-[350px] sm:max-h-[370px] overflow-y-auto pr-1.5 space-y-2.5 scroll-smooth">
                        @forelse($products as $prod)
                            @php
                                $prodPrice = (int) ($prod['price'] ?? 0);
                                $prodCap = $prod['capacity_kg'] ?? null;
                                $prodName = $prod['name'] ?? 'Mesin Roasting Kopi';
                            @endphp
                            <div class="flex items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-[#E8E4DE] hover:border-[#8C5E3C]/40 transition shadow-2xs">
                                <div class="min-w-0 pr-2">
                                    <h5 class="font-bold text-[#18181B] text-xs sm:text-sm truncate">
                                        {{ $prodName }}
                                    </h5>
                                    <span class="text-[11px] text-[#57534E] flex items-center gap-1.5 mt-0.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#8C5E3C]"></span>
                                        @if($prodCap) Kapasitas {{ $prodCap }} Kg &bull; @endif Termasuk Pendingin (Cooling Bin)
                                    </span>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-[#18181B] text-sm sm:text-base tracking-tight">
                                        Rp {{ number_format($prodPrice, 0, ',', '.') }}
                                    </div>
                                    <span class="text-[10px] text-[#8C5E3C] font-semibold block">
                                        DP 20%: Rp {{ number_format($prodPrice * 0.2, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white p-4 rounded-2xl border border-[#E8E4DE] text-center text-xs text-[#57534E]">
                                Data produk pre-order sedang disiapkan oleh Admin.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Penekanan Kalimat Penutup "PESAN SEKARANG" -->
                <div class="pt-6 mt-5 border-t border-[#E8E4DE]">
                    <a href="{{ route('catalog') }}"
                       class="group w-full bg-[#18181B] hover:bg-[#8C5E3C] text-white text-center font-black py-4 px-6 rounded-2xl text-sm uppercase tracking-widest transition duration-200 shadow-md flex items-center justify-center gap-2">
                        <span>PESAN SEKARANG</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>

            </div>

        </div>
    </section>

</div>
@endsection