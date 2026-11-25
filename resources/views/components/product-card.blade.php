@props([
    'capacity',
    'price',
    'estimation',
    'image',
    'stockStatus' => 'Ready: 2 Unit (Bisa Custom)'
])

<div tabindex="0" 
     class="group relative bg-white border border-[#E8E4DE] hover:border-[#8C5E3C] rounded-2xl p-5 flex flex-col justify-between cursor-pointer select-none outline-none
            transition-all duration-300 ease-out shadow-xs
            hover:-translate-y-1.5 hover:shadow-xl
            focus:-translate-y-2 focus:border-[#8C5E3C] focus:ring-4 focus:ring-[#8C5E3C]/20
            active:scale-[0.99]">

    <div class="space-y-4">
        <!-- Foto Mesin Roasting -->
        <div class="h-44 rounded-xl bg-[#F7F5F0] border border-[#E8E4DE] flex items-center justify-center p-2 overflow-hidden relative mt-1">
            <img src="{{ $image }}" 
                 alt="Mesin Roasting {{ $capacity }}" 
                 class="w-full h-full object-cover rounded-lg transition-transform duration-500 ease-out group-hover:scale-105 group-focus:scale-110">
            <span class="absolute bottom-2 left-2 bg-white/90 backdrop-blur-xs text-[#8C5E3C] border border-[#E8E4DE] text-[10px] font-bold px-2.5 py-1 rounded-md shadow-xs">
                PO {{ $estimation }}
            </span>
        </div>

        <!-- Nama Brand & Judul Produk -->
        <div class="space-y-1 pt-1 transition-transform duration-300 group-focus:translate-x-1">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#8C5E3C]">RJ TEKNIK</span>
            <h3 class="font-extrabold text-base text-[#18181B] group-hover:text-[#8C5E3C] transition-colors line-clamp-1">
                Mesin Roasting Kopi - Kapasitas {{ $capacity }}
            </h3>
        </div>

        <!-- Keterangan Status Stok -->
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                {{ $stockStatus }}
            </span>
        </div>
    </div>

    <!-- Bagian Bawah: Harga & Tombol Pre-Order -->
    <div class="mt-6 pt-4 border-t border-[#E8E4DE] flex items-center justify-between">
        <div>
            <span class="text-[10px] text-[#78716C] block uppercase tracking-wider font-semibold">Uang Muka (DP 50%)</span>
            <span class="font-black text-[#8C5E3C] text-base">{{ $price }}</span>
        </div>
        <a href="{{ route('checkout.view', ['variant' => $capacity]) }}" 
           class="bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs px-4 py-2.5 rounded-xl font-bold shadow-xs
                  transition-all duration-200 active:scale-95">
            Checkout
        </a>
    </div>
</div>
