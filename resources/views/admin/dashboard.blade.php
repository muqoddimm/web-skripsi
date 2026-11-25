<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog & Produk PO - Admin RJ TEKNIK</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="h-full font-sans antialiased bg-[#F7F5F0] text-[#18181B] m-0 p-0 flex">

    @php
    $totalProducts = count($products ?? []);
    $totalOrders = count($orders ?? []);
    $pendingChatlogs = collect($logs ?? [])->where('status', 'pending_admin')->count();
    $totalChatlogs = count($logs ?? []);
    @endphp

    <!-- SIDEBAR -->
    @include('admin.partials.sidebar', ['activeMenu' => 'products'])

    <!-- KONTEN UTAMA -->
    <main class="flex-grow flex flex-col min-h-screen overflow-y-auto bg-[#F7F5F0]">
        @if(session('success'))
        <div class="max-w-7xl mx-auto px-8 pt-6 w-full">
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
        <div class="max-w-7xl mx-auto px-8 pt-6 w-full">
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm font-medium flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 font-bold cursor-pointer">✕</button>
            </div>
        </div>
        @endif

        <!-- SECTION: PRODUK KATALOG PRE-ORDER -->
        <div class="p-8 sm:p-12 space-y-8 max-w-7xl w-full mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E8E4DE] pb-6">
                <div>
                    <h2 class="text-3xl font-black text-[#18181B] tracking-wide uppercase">PRE-ORDER PRODUK (1–50 KG)</h2>
                    <p class="text-xs text-[#57534E] mt-1 leading-relaxed">Kelola data spesifikasi teknis, timeline pengerjaan, dokumentasi unit, dan rekomendasi<br>kebutuhan customer yang terhubung langsung ke basis data & AI CS.</p>
                </div>
                <button type="button" onclick="openAddModal()" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-bold px-5 py-3 rounded-xl transition cursor-pointer shadow-md flex items-center gap-2 shrink-0 whitespace-nowrap">
                    <span class="text-base leading-none">+</span>
                    <span>Tambah Produk Baru</span>
                </button>
            </div>

            <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto min-h-[260px]">
                    <table class="w-full text-left border-collapse min-w-[750px]">
                        <thead>
                            <tr class="bg-[#F3EFEA] border-b border-[#E8E4DE] text-[11px] font-bold text-[#18181B] uppercase tracking-wider">
                                <th class="py-4 px-6">Foto</th>
                                <th class="py-4 px-4">Unit Mesin & Rekomendasi</th>
                                <th class="py-4 px-4">Kapasitas</th>
                                <th class="py-4 px-4">Timeline</th>
                                <th class="py-4 px-4">Harga & DP 20%</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E4DE] text-sm font-medium text-[#18181B]">
                            @forelse($products ?? [] as $product)
                            @php
                            $pid = is_array($product) ? ($product['id'] ?? '') : ($product->id ?? '');
                            $pname = is_array($product) ? ($product['name'] ?? '') : ($product->name ?? '');
                            $pimg = is_array($product) ? ($product['image'] ?? '') : ($product->image ?? '');
                            if (empty($pimg)) {
                                $pimg = 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=200&auto=format&fit=crop';
                            }
                            $pcap = is_array($product) ? ($product['capacity_kg'] ?? 5) : ($product->capacity_kg ?? 5);
                            $pestDays = is_array($product) ? ($product['estimation_days'] ?? 14) : ($product->estimation_days ?? 14);
                            $pdrum = is_array($product) ? ($product['drum_material'] ?? 'Full SS Double Jacket') : ($product->drum_material ?? 'Full SS Double Jacket');
                            $pgearbox = is_array($product) ? ($product['gearbox_ratio'] ?? 'Dinamo & Gearbox') : ($product->gearbox_ratio ?? 'Dinamo & Gearbox');
                            $ppower = is_array($product) ? ($product['motor_power_watt'] ?? 450) : ($product->motor_power_watt ?? 450);
                            $pburner = is_array($product) ? ($product['burner_type'] ?? 'Kompor LPG Modifikasi') : ($product->burner_type ?? 'Kompor LPG Modifikasi');
                            $pairflow = is_array($product) ? ($product['airflow_system'] ?? 'Blower & Siklon') : ($product->airflow_system ?? 'Blower & Siklon');
                            $pcooling = is_array($product) ? ($product['cooling_bin_spec'] ?? 'Cooling Bin SS') : ($product->cooling_bin_spec ?? 'Cooling Bin SS');
                            $pdesc = is_array($product) ? ($product['description'] ?? '') : ($product->description ?? '');
                            $prec = is_array($product) ? ($product['recommendation'] ?? '') : ($product->recommendation ?? '');

                            $priceVal = is_array($product) ? ($product['price'] ?? '0') : ($product->price ?? '0');
                            $rawPrice = (int) preg_replace('/[^0-9]/', '', (string) $priceVal);
                            $displayPrice = 'Rp ' . number_format($rawPrice, 0, ',', '.');
                            $displayDp = 'Rp ' . number_format((int) round($rawPrice * 0.20), 0, ',', '.');
                            @endphp
                            <tr class="hover:bg-[#F7F5F0]/60 transition">
                                <td class="py-4 px-6 align-top">
                                    <img src="{{ $pimg }}" alt="{{ $pname }}" class="w-16 h-16 rounded-2xl object-cover border border-[#E8E4DE] bg-[#F7F5F0]">
                                </td>
                                <td class="py-4 px-4 align-top max-w-xs sm:max-w-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[#18181B]">{{ $pname }}</span>
                                        <span class="text-[10px] text-[#78716C] bg-[#FAF9F5] px-1.5 py-0.5 rounded border border-[#E8E4DE]">#{{ $pid }}</span>
                                    </div>
                                    @if(!empty($prec))
                                    <p class="text-[11px] text-[#57534E] mt-1.5 leading-snug line-clamp-2">
                                        <span class="font-bold text-[#8C5E3C]">💡 Rekomendasi:</span> {{ $prec }}
                                    </p>
                                    @endif
                                    <div class="mt-2 flex flex-wrap gap-1 text-[10px] text-[#78716C]">
                                        <span class="bg-[#FAF9F5] border border-[#E8E4DE] px-2 py-0.5 rounded-md">Drum: {{ Str::limit($pdrum, 25) }}</span>
                                        <span class="bg-[#FAF9F5] border border-[#E8E4DE] px-2 py-0.5 rounded-md">Listrik: {{ $ppower }}W</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <span class="inline-block text-[11px] font-extrabold text-[#8C5E3C] bg-[#F3EFEA] border border-[#E8E4DE] px-2.5 py-1 rounded-lg">
                                        {{ $pcap }} Kg / Batch
                                    </span>
                                </td>
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#57534E] bg-[#FAF9F5] border border-[#E8E4DE] px-2.5 py-1 rounded-lg">
                                        <span><svg class="w-3.5 h-3.5 inline text-[#8C5E3C] -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg></span>
                                        <span>{{ $pestDays }} Hari</span>
                                    </span>
                                </td>
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <span class="font-black text-[#18181B] block">{{ $displayPrice }}</span>
                                    <span class="text-[11px] font-extrabold text-emerald-700 block mt-0.5">DP: {{ $displayDp }}</span>
                                </td>
                                <td class="py-4 px-6 align-top text-right relative whitespace-nowrap">
                                    <div class="inline-block text-left">
                                        <button type="button"
                                            onclick="toggleActionDropdown(event, 'dropdown-product-{{ $loop->index }}')"
                                            class="w-8 h-8 rounded-xl hover:bg-[#F3EFEA] flex items-center justify-center font-bold text-[#78716C] hover:text-[#18181B] transition cursor-pointer ml-auto">
                                            ⋮
                                        </button>
                                        <div id="dropdown-product-{{ $loop->index }}"
                                            class="action-dropdown hidden absolute right-0 mt-1 w-44 bg-white border border-[#E8E4DE] rounded-2xl shadow-xl z-50 overflow-hidden py-1">
                                            
                                            <!-- Detail Produk -->
                                            <button type="button"
                                                data-id="{{ $pid }}"
                                                data-name="{{ $pname }}"
                                                data-capacity="{{ $pcap }}"
                                                data-price="{{ $rawPrice }}"
                                                data-display-price="{{ $displayPrice }}"
                                                data-display-dp="{{ $displayDp }}"
                                                data-days="{{ $pestDays }}"
                                                data-img="{{ $pimg }}"
                                                data-drum="{{ $pdrum }}"
                                                data-gearbox="{{ $pgearbox }}"
                                                data-power="{{ $ppower }}"
                                                data-burner="{{ $pburner }}"
                                                data-airflow="{{ $pairflow }}"
                                                data-cooling="{{ $pcooling }}"
                                                data-recommendation="{{ $prec }}"
                                                data-description="{{ $pdesc }}"
                                                onclick="openDetailModal(this); hideAllDropdowns();"
                                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#18181B] hover:bg-[#F3EFEA] transition flex items-center gap-2.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-[#57534E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Detail Produk</span>
                                            </button>

                                            <!-- Edit Produk -->
                                            <button type="button"
                                                data-url="{{ route('admin.products.update', $pid) }}"
                                                data-name="{{ $pname }}"
                                                data-capacity="{{ $pcap }}"
                                                data-price="{{ $rawPrice }}"
                                                data-days="{{ $pestDays }}"
                                                data-drum="{{ $pdrum }}"
                                                data-gearbox="{{ $pgearbox }}"
                                                data-power="{{ $ppower }}"
                                                data-burner="{{ $pburner }}"
                                                data-airflow="{{ $pairflow }}"
                                                data-cooling="{{ $pcooling }}"
                                                data-recommendation="{{ $prec }}"
                                                data-description="{{ $pdesc }}"
                                                onclick="openEditModal(this); hideAllDropdowns();"
                                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-[#8C5E3C] hover:bg-[#F3EFEA] transition flex items-center gap-2.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-[#8C5E3C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Edit Spesifikasi</span>
                                            </button>

                                            <div class="border-t border-[#E8E4DE]/60 my-0.5"></div>

                                            <!-- Hapus Produk -->
                                            <form action="{{ route('admin.products.delete', $pid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit {{ $pname }} ini dari database?')">
                                                @csrf
                                                <button type="submit" class="w-full text-left px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition flex items-center gap-2.5 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span>Hapus Produk</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-[#78716C] text-xs">Belum ada produk di database. Silakan klik tombol "Tambah Produk Baru" di atas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- ========================================== -->
    <!-- MODAL DETAIL SPESIFIKASI PRODUK            -->
    <!-- ========================================== -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="bg-white border border-[#E8E4DE] text-[#18181B] rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-6 my-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between border-b border-[#E8E4DE] pb-4">
                <div>
                    <span id="detail-id" class="text-[10px] text-[#78716C] font-bold uppercase tracking-wider block">ID: #</span>
                    <h3 id="detail-name" class="font-black text-xl text-[#18181B] mt-0.5">Nama Unit Mesin</h3>
                </div>
                <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-[#FAF9F5] hover:bg-[#F3EFEA] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] font-bold flex items-center justify-center cursor-pointer transition">
                    ✕
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Foto Unit Mesin -->
                <div class="space-y-3">
                    <img id="detail-img" src="" alt="Unit Mesin" class="w-full aspect-square rounded-2xl object-cover border border-[#E8E4DE] bg-[#FAF9F5]">
                    <div class="p-3 bg-[#FAF9F5] border border-[#E8E4DE] rounded-xl space-y-1">
                        <span class="text-[10px] text-[#78716C] uppercase font-bold block">Kapasitas Batch</span>
                        <span id="detail-cap" class="text-xs font-black text-[#8C5E3C] block">-</span>
                    </div>
                </div>

                <!-- Info Finansial & Timeline -->
                <div class="sm:col-span-2 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-[#FAF9F5] border border-[#E8E4DE] rounded-xl">
                            <span class="text-[10px] text-[#78716C] uppercase font-bold block">Harga Unit (100%)</span>
                            <span id="detail-price" class="text-sm font-black text-[#18181B] block">-</span>
                        </div>
                        <div class="p-3 bg-[#FAF9F5] border border-[#E8E4DE] rounded-xl">
                            <span class="text-[10px] text-emerald-800 uppercase font-bold block">Uang Muka (DP 20%)</span>
                            <span id="detail-dp" class="text-sm font-black text-emerald-700 block">-</span>
                        </div>
                    </div>

                    <div class="p-3 bg-[#FAF9F5] border border-[#E8E4DE] rounded-xl">
                        <span class="text-[10px] text-[#78716C] uppercase font-bold block">Timeline Perakitan</span>
                        <span id="detail-days" class="text-xs font-bold text-[#18181B] block mt-0.5">-</span>
                    </div>

                    <!-- Rekomendasi Customer -->
                    <div class="p-3.5 bg-[#F3EFEA] border border-[#E8E4DE] rounded-xl space-y-1">
                        <span class="text-[10px] text-[#8C5E3C] uppercase font-extrabold flex items-center gap-1">
                            <span>💡</span>
                            <span>Rekomendasi Kebutuhan Customer (Grounding AI CS)</span>
                        </span>
                        <p id="detail-recommendation" class="text-xs font-semibold text-[#18181B] leading-relaxed">-</p>
                    </div>
                </div>
            </div>

            <!-- Tabel Spesifikasi Teknis Manufaktur -->
            <div class="space-y-2 pt-2 border-t border-[#E8E4DE]">
                <h4 class="text-xs font-black uppercase tracking-wider text-[#18181B]">Rincian Spesifikasi Teknis Mesin</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">
                        <span class="text-[10px] text-[#78716C] font-bold block">Material Tabung Drum:</span>
                        <span id="detail-drum" class="font-semibold text-[#18181B] mt-0.5 block">-</span>
                    </div>
                    <div class="p-2.5 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">
                        <span class="text-[10px] text-[#78716C] font-bold block">Penggerak & Transmisi Gearbox:</span>
                        <span id="detail-gearbox" class="font-semibold text-[#18181B] mt-0.5 block">-</span>
                    </div>
                    <div class="p-2.5 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">
                        <span class="text-[10px] text-[#78716C] font-bold block">Konsumsi Daya Motor:</span>
                        <span id="detail-power" class="font-semibold text-[#18181B] mt-0.5 block">-</span>
                    </div>
                    <div class="p-2.5 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">
                        <span class="text-[10px] text-[#78716C] font-bold block">Sistem Kompor Burner LPG:</span>
                        <span id="detail-burner" class="font-semibold text-[#18181B] mt-0.5 block">-</span>
                    </div>
                    <div class="p-2.5 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">
                        <span class="text-[10px] text-[#78716C] font-bold block">Airflow & Cyclone Separator:</span>
                        <span id="detail-airflow" class="font-semibold text-[#18181B] mt-0.5 block">-</span>
                    </div>
                    <div class="p-2.5 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">
                        <span class="text-[10px] text-[#78716C] font-bold block">Spesifikasi Cooling Bin:</span>
                        <span id="detail-cooling" class="font-semibold text-[#18181B] mt-0.5 block">-</span>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Tambahan -->
            <div class="space-y-1">
                <span class="text-[10px] text-[#78716C] font-bold uppercase block">Deskripsi Unit:</span>
                <p id="detail-description" class="text-xs text-[#57534E] leading-relaxed p-3 bg-[#FAF9F5] rounded-xl border border-[#E8E4DE]">-</p>
            </div>

            <div class="flex justify-end pt-3 border-t border-[#E8E4DE]">
                <button type="button" onclick="closeDetailModal()" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- MODAL TAMBAH PRODUK BARU (1–50 KG)         -->
    <!-- ========================================== -->
    <div id="add-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="bg-white border border-[#E8E4DE] text-[#18181B] rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl space-y-6 my-8 max-h-[92vh] overflow-y-auto">
            <div class="flex items-start justify-between border-b border-[#E8E4DE] pb-4">
                <div>
                    <h3 class="font-black text-xl text-[#18181B]">Tambah Unit Mesin Roasting (Kapasitas 1–50 Kg)</h3>
                    <p class="text-xs text-[#57534E] mt-1">Lengkapi spesifikasi teknis mesin, timeline perakitan, dan rekomendasi kebutuhan customer untuk grounding AI CS.</p>
                </div>
                <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-full bg-[#FAF9F5] hover:bg-[#F3EFEA] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] font-bold flex items-center justify-center cursor-pointer transition">
                    ✕
                </button>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- BAGIAN 1: INFO DASAR & FINANSIAL -->
                <div class="space-y-4">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        1. Informasi Unit, Kapasitas & Finansial
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Nama Unit Mesin <span class="text-rose-600">*</span></label>
                            <input type="text" name="name" placeholder="Contoh: Mesin Roasting Kopi Kapasitas 15 Kg" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Kapasitas (Kg, 1–50) <span class="text-rose-600">*</span></label>
                            <input type="number" name="capacity_kg" min="1" max="50" placeholder="Contoh: 15" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Harga Unit Utuh 100% (Rp) <span class="text-rose-600">*</span></label>
                            <input type="number" id="add-price-input" name="price" placeholder="Contoh: 55000000" required oninput="updateAddDpPreview()"
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                            <span id="add-dp-preview" class="text-[11px] font-bold text-emerald-700 block mt-1">DP 20%: Rp 0</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Timeline Perakitan (Hari) <span class="text-rose-600">*</span></label>
                            <input type="number" name="estimation_days" min="1" placeholder="Contoh: 25" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                            <span class="text-[10px] text-[#78716C] block mt-1">Estimasi hari kerja selesai dirakit</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Daya Listrik (Watt) <span class="text-rose-600">*</span></label>
                            <input type="number" name="motor_power_watt" min="50" placeholder="Contoh: 750" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                            <span class="text-[10px] text-[#78716C] block mt-1">Kebutuhan daya dinamo & blower</span>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: SPESIFIKASI TEKNIS MESIN -->
                <div class="space-y-4 pt-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        2. Rincian Spesifikasi Rancang Bangun Manufaktur
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Material Tabung Drum</label>
                            <input type="text" name="drum_material" placeholder="Contoh: Tabung Full SS Double Jacket (Ã˜ 28 x 40 cm), Rangka Besi Siku" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Penggerak & Transmisi Gearbox</label>
                            <input type="text" name="gearbox_ratio" placeholder="Contoh: Dinamo Drum Gearbox 1/2 HP & Transmisi Rantai" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Kompor Burner LPG</label>
                            <input type="text" name="burner_type" placeholder="Contoh: Kompor Pipa LPG Modifikasi Khusus Sangrai + Regulator High Pressure" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Airflow & Cyclone Separator</label>
                            <input type="text" name="airflow_system" placeholder="Contoh: 1 Blower Hisap Kuat 2 Fungsi + Cyclone Separator Filter Sampah" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Spesifikasi Cooling Bin</label>
                            <input type="text" name="cooling_bin_spec" placeholder="Contoh: Cooling Bin SS Ã˜ 50 cm + Agitator & Termometer Suhu" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: REKOMENDASI KEBUTUHAN CUSTOMER (MANUAL SETTING ADMIN) -->
                <div class="space-y-4 pt-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        3. Rekomendasi Kebutuhan Customer (Bahan Rujukan AI CS)
                    </span>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">
                            Rekomendasi Manual untuk Pelanggan <span class="text-rose-600">*</span>
                        </label>
                        <textarea name="recommendation" rows="2" required placeholder="Contoh: Sangat direkomendasikan untuk coffee shop rintisan hingga menengah dengan target sangrai 20–50 kg per minggu, praktis dioperasikan dan hemat konsumsi LPG."
                            class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl p-3.5 text-xs font-medium text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition"></textarea>
                        <span class="text-[10px] text-[#78716C] block mt-1">Keterangan ini otomatis dipakai oleh Asisten AI CS saat merekomendasikan unit ke pelanggan.</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Deskripsi Fisik Mesin (Opsional)</label>
                        <textarea name="description" rows="2" placeholder="Deskripsi ringkas mengenai keunggulan, material, dan kelengkapan unit mesin..."
                            class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl p-3.5 text-xs font-medium text-[#18181B] placeholder-[#78716C]/50 focus:outline-none focus:border-[#8C5E3C] transition"></textarea>
                    </div>
                </div>

                <!-- BAGIAN 4: DOKUMENTASI FOTO UNIT MESIN -->
                <div class="space-y-4 pt-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        4. Dokumentasi Foto Unit Mesin
                    </span>
                    <div>
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-[#E8E4DE] hover:border-[#8C5E3C] bg-[#FAF9F5] hover:bg-[#F3EFEA] rounded-2xl p-5 cursor-pointer transition group">
                            <span id="add-file-label" class="text-xs font-bold text-[#57534E] group-hover:text-[#18181B]">Klik atau tarik foto dokumentasi unit ke sini</span>
                            <span class="text-[10px] text-[#78716C] mt-1">Format: JPG, PNG, WEBP (Maksimal 2MB)</span>
                            <input type="file" name="image_file" accept="image/*" class="hidden" onchange="updateFileLabel(this, 'add-file-label')">
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E8E4DE]">
                    <button type="button" onclick="closeAddModal()" class="bg-[#F3EFEA] hover:bg-[#E8E4DE] text-[#57534E] border border-[#E8E4DE] px-5 py-3 rounded-2xl text-xs font-bold transition cursor-pointer">Batal</button>
                    <button type="submit" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-6 py-3 rounded-2xl text-xs font-bold transition cursor-pointer shadow-md">Simpan ke Basis Data</button>
                </div>
            </form>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- MODAL EDIT PRODUK                          -->
    <!-- ========================================== -->
    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="bg-white border border-[#E8E4DE] text-[#18181B] rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl space-y-6 my-8 max-h-[92vh] overflow-y-auto">
            <div class="flex items-start justify-between border-b border-[#E8E4DE] pb-4">
                <div>
                    <h3 class="font-black text-xl text-[#18181B]">Edit Spesifikasi Unit Mesin Roasting</h3>
                    <p class="text-xs text-[#57534E] mt-1">Perbarui data spesifikasi teknis, waktu perakitan, dan rekomendasi kebutuhan pelanggan.</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-[#FAF9F5] hover:bg-[#F3EFEA] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] font-bold flex items-center justify-center cursor-pointer transition">
                    ✕
                </button>
            </div>

            <form id="edit-form" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- BAGIAN 1: INFO DASAR & FINANSIAL -->
                <div class="space-y-4">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        1. Informasi Unit, Kapasitas & Finansial
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Nama Unit Mesin <span class="text-rose-600">*</span></label>
                            <input type="text" id="edit-name" name="name" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Kapasitas (Kg, 1–50) <span class="text-rose-600">*</span></label>
                            <input type="number" id="edit-capacity" name="capacity_kg" min="1" max="50" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Harga Unit Utuh 100% (Rp) <span class="text-rose-600">*</span></label>
                            <input type="number" id="edit-price-input" name="price" required oninput="updateEditDpPreview()"
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                            <span id="edit-dp-preview" class="text-[11px] font-bold text-emerald-700 block mt-1">DP 20%: Rp 0</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Timeline Perakitan (Hari) <span class="text-rose-600">*</span></label>
                            <input type="number" id="edit-days" name="estimation_days" min="1" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                            <span class="text-[10px] text-[#78716C] block mt-1">Estimasi hari kerja selesai dirakit</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Daya Listrik (Watt) <span class="text-rose-600">*</span></label>
                            <input type="number" id="edit-power" name="motor_power_watt" min="50" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                            <span class="text-[10px] text-[#78716C] block mt-1">Kebutuhan daya dinamo & blower</span>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: SPESIFIKASI TEKNIS MESIN -->
                <div class="space-y-4 pt-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        2. Rincian Spesifikasi Rancang Bangun Manufaktur
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Material Tabung Drum</label>
                            <input type="text" id="edit-drum" name="drum_material" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Penggerak & Transmisi Gearbox</label>
                            <input type="text" id="edit-gearbox" name="gearbox_ratio" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Kompor Burner LPG</label>
                            <input type="text" id="edit-burner" name="burner_type" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Airflow & Cyclone Separator</label>
                            <input type="text" id="edit-airflow" name="airflow_system" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Spesifikasi Cooling Bin</label>
                            <input type="text" id="edit-cooling" name="cooling_bin_spec" required
                                class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl px-4 py-2.5 text-xs font-semibold text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: REKOMENDASI KEBUTUHAN CUSTOMER -->
                <div class="space-y-4 pt-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        3. Rekomendasi Kebutuhan Customer (Bahan Rujukan AI CS)
                    </span>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">
                            Rekomendasi Manual untuk Pelanggan <span class="text-rose-600">*</span>
                        </label>
                        <textarea id="edit-recommendation" name="recommendation" rows="2" required
                            class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl p-3.5 text-xs font-medium text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition"></textarea>
                        <span class="text-[10px] text-[#78716C] block mt-1">Keterangan ini otomatis dipakai oleh Asisten AI CS saat merekomendasikan unit ke pelanggan.</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#57534E] mb-1.5">Deskripsi Fisik Mesin (Opsional)</label>
                        <textarea id="edit-description" name="description" rows="2"
                            class="w-full bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl p-3.5 text-xs font-medium text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition"></textarea>
                    </div>
                </div>

                <!-- BAGIAN 4: DOKUMENTASI FOTO UNIT MESIN -->
                <div class="space-y-4 pt-2">
                    <span class="text-xs font-black uppercase tracking-wider text-[#8C5E3C] block border-b border-[#E8E4DE] pb-1.5">
                        4. Ganti Foto Dokumentasi Unit (Opsional)
                    </span>
                    <div>
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-[#E8E4DE] hover:border-[#8C5E3C] bg-[#FAF9F5] hover:bg-[#F3EFEA] rounded-2xl p-5 cursor-pointer transition group">
                            <span id="edit-file-label" class="text-xs font-bold text-[#57534E] group-hover:text-[#18181B]">Klik untuk mengganti foto dokumentasi unit</span>
                            <span class="text-[10px] text-[#78716C] mt-1">Biarkan kosong jika tetap menggunakan foto yang lama</span>
                            <input type="file" name="image_file" accept="image/*" class="hidden" onchange="updateFileLabel(this, 'edit-file-label')">
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E8E4DE]">
                    <button type="button" onclick="closeEditModal()" class="bg-[#F3EFEA] hover:bg-[#E8E4DE] text-[#57534E] border border-[#E8E4DE] px-5 py-3 rounded-2xl text-xs font-bold transition cursor-pointer">Batal</button>
                    <button type="submit" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-6 py-3 rounded-2xl text-xs font-bold transition cursor-pointer shadow-md">Perbarui Data Mesin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT INTERAKTIF PRODUK -->
    <script>
        function openAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            updateAddDpPreview();
        }

        function closeAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditModal(btn) {
            document.getElementById('edit-form').action = btn.getAttribute('data-url');
            document.getElementById('edit-name').value = btn.getAttribute('data-name') || '';
            document.getElementById('edit-capacity').value = btn.getAttribute('data-capacity') || '5';
            document.getElementById('edit-price-input').value = btn.getAttribute('data-price') || '15000000';
            document.getElementById('edit-days').value = btn.getAttribute('data-days') || '14';
            document.getElementById('edit-drum').value = btn.getAttribute('data-drum') || '';
            document.getElementById('edit-gearbox').value = btn.getAttribute('data-gearbox') || '';
            document.getElementById('edit-power').value = btn.getAttribute('data-power') || '450';
            document.getElementById('edit-burner').value = btn.getAttribute('data-burner') || '';
            document.getElementById('edit-airflow').value = btn.getAttribute('data-airflow') || '';
            document.getElementById('edit-cooling').value = btn.getAttribute('data-cooling') || '';
            document.getElementById('edit-recommendation').value = btn.getAttribute('data-recommendation') || '';
            document.getElementById('edit-description').value = btn.getAttribute('data-description') || '';
            
            updateEditDpPreview();

            const modal = document.getElementById('edit-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openDetailModal(btn) {
            document.getElementById('detail-id').textContent = 'ID: #' + (btn.getAttribute('data-id') || '-');
            document.getElementById('detail-name').textContent = btn.getAttribute('data-name') || '-';
            document.getElementById('detail-cap').textContent = (btn.getAttribute('data-capacity') || '-') + ' Kg / Batch';
            document.getElementById('detail-price').textContent = btn.getAttribute('data-display-price') || '-';
            document.getElementById('detail-dp').textContent = btn.getAttribute('data-display-dp') || '-';
            document.getElementById('detail-days').textContent = (btn.getAttribute('data-days') || '-') + ' Hari Kerja';
            document.getElementById('detail-img').src = btn.getAttribute('data-img') || '';
            document.getElementById('detail-drum').textContent = btn.getAttribute('data-drum') || '-';
            document.getElementById('detail-gearbox').textContent = btn.getAttribute('data-gearbox') || '-';
            document.getElementById('detail-power').textContent = (btn.getAttribute('data-power') || '-') + ' Watt';
            document.getElementById('detail-burner').textContent = btn.getAttribute('data-burner') || '-';
            document.getElementById('detail-airflow').textContent = btn.getAttribute('data-airflow') || '-';
            document.getElementById('detail-cooling').textContent = btn.getAttribute('data-cooling') || '-';
            document.getElementById('detail-recommendation').textContent = btn.getAttribute('data-recommendation') || 'Belum ada rekomendasi yang disetel.';
            document.getElementById('detail-description').textContent = btn.getAttribute('data-description') || 'Tidak ada deskripsi tambahan.';

            const modal = document.getElementById('detail-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetailModal() {
            const modal = document.getElementById('detail-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function updateAddDpPreview() {
            const input = document.getElementById('add-price-input');
            const preview = document.getElementById('add-dp-preview');
            const val = parseInt(input.value) || 0;
            const dp = Math.round(val * 0.20);
            preview.textContent = 'DP 20%: Rp ' + dp.toLocaleString('id-ID');
        }

        function updateEditDpPreview() {
            const input = document.getElementById('edit-price-input');
            const preview = document.getElementById('edit-dp-preview');
            const val = parseInt(input.value) || 0;
            const dp = Math.round(val * 0.20);
            preview.textContent = 'DP 20%: Rp ' + dp.toLocaleString('id-ID');
        }

        function updateFileLabel(input, labelId) {
            const label = document.getElementById(labelId);
            if (input.files && input.files[0]) {
                label.textContent = '✓ Foto Terpilih: ' + input.files[0].name;
                label.classList.add('text-emerald-600');
            }
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
    </script>
</body>

</html>
