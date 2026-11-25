@extends('layouts.app')

@section('title', 'Profil Pengguna - RJ TEKNIK')

@section('content')
@php
    $defaultTab = request('tab', session('preorder_intent_product_id') ? 'settings' : 'profile');
    if (!in_array($defaultTab, ['profile', 'transactions', 'settings'])) {
        $defaultTab = 'profile';
    }
@endphp
<div class="bg-[#F7F5F0] py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Kartu Utama Profil -->
        <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[600px]">

            <!-- Sidebar Menu Navigasi Profil (Kiri - 4 Kolom) -->
            <div class="md:col-span-4 bg-[#FAF9F6] border-r border-[#E8E4DE] p-8 sm:p-10 flex flex-col justify-between space-y-6">
                <div class="space-y-8">
                    <!-- Info Ringkas Pengguna -->
                    <div class="flex items-center gap-4 border-b border-[#E8E4DE] pb-6">
                        <div class="relative shrink-0">
                            @if(session('user_avatar'))
                            <img src="{{ session('user_avatar') }}" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover border-2 border-[#E8E4DE] shadow-xs">
                            @else
                            <div class="w-16 h-16 rounded-full bg-[#8C5E3C] text-white text-2xl font-extrabold flex items-center justify-center shadow-xs uppercase">
                                {{ strtoupper(substr(session('user_name', 'U'), 0, 1)) }}
                            </div>
                            @endif
                            <span class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></span>
                        </div>
                        <div class="overflow-hidden">
                            <h2 class="font-bold text-[#18181B] text-base truncate">{{ session('user_name', 'Tamu Pengguna') }}</h2>
                            <p class="text-[#57534E] text-xs truncate">{{ session('user_email', 'pelanggan@rjgresik.com') }}</p>
                        </div>
                    </div>

                    <!-- Tombol Menu Tab -->
                    <nav class="space-y-2 text-sm font-semibold">
                        <button onclick="switchTab('profile')" id="btn-profile" class="w-full flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition text-left cursor-pointer {{ $defaultTab === 'profile' ? 'bg-[#8C5E3C] text-white shadow-xs' : 'text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profil Saya</span>
                        </button>

                        <button onclick="switchTab('transactions')" id="btn-transactions" class="w-full flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition text-left cursor-pointer {{ $defaultTab === 'transactions' ? 'bg-[#8C5E3C] text-white shadow-xs' : 'text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Riwayat Transaksi</span>
                        </button>

                        <button onclick="switchTab('settings')" id="btn-settings" class="w-full flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition text-left cursor-pointer {{ $defaultTab === 'settings' ? 'bg-[#8C5E3C] text-white shadow-xs' : 'text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Pengaturan</span>
                        </button>
                    </nav>
                </div>

                <!-- Tombol Logout -->
                <form action="{{ route('logout') }}" method="POST" class="pt-6 border-t border-[#E8E4DE]">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 py-3.5 rounded-2xl text-xs font-bold transition cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>

            <!-- Area Konten Utama (Kanan - 8 Kolom) -->
            <div class="md:col-span-8 p-8 sm:p-12 bg-white flex flex-col justify-between">

                <div class="space-y-6">
                    <!-- TAB 1: MY PROFILE -->
                    <div id="tab-profile" class="tab-content {{ $defaultTab === 'profile' ? '' : 'hidden' }} space-y-6">
                        <div class="border-b border-[#E8E4DE] pb-4">
                            <h2 class="text-2xl font-extrabold text-[#18181B]">Informasi Profil Saya</h2>
                            <p class="text-xs text-[#57534E]">Kelola informasi data diri dan status akun terverifikasi Anda.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                            <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-1.5">
                                <span class="text-[10px] font-bold text-[#57534E] uppercase tracking-wider block">Nama Lengkap</span>
                                <span class="font-bold text-[#18181B] text-base">{{ session('user_name', Auth::user()->name ?? 'Tamu Pengguna') }}</span>
                            </div>
                            <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-1.5">
                                <span class="text-[10px] font-bold text-[#57534E] uppercase tracking-wider block">Alamat Email</span>
                                <span class="font-bold text-[#18181B] text-base truncate block">{{ session('user_email', Auth::user()->email ?? 'pelanggan@rjgresik.com') }}</span>
                            </div>
                            <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-1.5">
                                <span class="text-[10px] font-bold text-[#57534E] uppercase tracking-wider block">Peran Sistem</span>
                                <span class="font-bold text-[#8C5E3C] text-base uppercase">{{ session('user_role', Auth::user()->role ?? 'Customer') }}</span>
                            </div>
                            <div class="bg-[#F7F5F0] p-5 rounded-2xl border border-[#E8E4DE] space-y-1.5">
                                <span class="text-[10px] font-bold text-[#57534E] uppercase tracking-wider block">Status Akun</span>
                                <span class="font-bold text-emerald-800 text-base flex items-center gap-2 pt-0.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Google Verified Active
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: TRANSACTION HISTORY -->
                    <div id="tab-transactions" class="tab-content {{ $defaultTab === 'transactions' ? '' : 'hidden' }} space-y-6">
                        <div class="border-b border-[#E8E4DE] pb-4">
                            <h2 class="text-2xl font-extrabold text-[#18181B]">Riwayat Transaksi &amp; Pre-Order</h2>
                            <p class="text-xs text-[#57534E]">Daftar riwayat pemesanan mesin roasting kopi di RJ TEKNIK.</p>
                        </div>

                        <div class="space-y-4">
                            @php
                            $stepBadges = [
                                1 => ['badge' => 'Menunggu Pembayaran DP', 'class' => 'bg-amber-50 text-amber-800 border-amber-200'],
                                2 => ['badge' => 'DP Lunas • Antrean Workshop', 'class' => 'bg-[#F3EFEA] text-[#18181B] border-[#E8E4DE]'],
                                3 => ['badge' => 'Sedang Dirakit di Workshop', 'class' => 'bg-[#F3EFEA] text-[#8C5E3C] border-[#8C5E3C]/40'],
                                4 => ['badge' => 'Perakitan Selesai • Menunggu Pelunasan', 'class' => 'bg-[#8C5E3C] text-white border-transparent'],
                                5 => ['badge' => 'Lunas 100% • Dalam Pengiriman Kargo', 'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                            ];
                            @endphp

                            @forelse($orders ?? [] as $order)
                            @php
                                $step = max(1, min(5, (int)($order['step'] ?? 2)));
                                $badgeInfo = $stepBadges[$step] ?? $stepBadges[2];
                            @endphp
                            <div class="p-6 rounded-2xl border border-[#E8E4DE] bg-[#F7F5F0] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono font-bold text-[#18181B] text-sm">{{ $order['id'] }}</span>
                                        <span class="text-[10px] font-bold px-3 py-0.5 rounded-full border {{ $badgeInfo['class'] }}">
                                            {{ $badgeInfo['badge'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-[#18181B] font-medium">{{ $order['product_name'] ?? 'Mesin Roasting Kopi' }}</p>
                                    <p class="text-xs text-[#57534E]">Tanggal Pemesanan: {{ $order['date'] ?? date('d M Y') }}</p>
                                </div>
                                <a href="{{ route('order.track') }}" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-bold px-5 py-3 rounded-xl transition shadow-2xs whitespace-nowrap">
                                    Lacak Status &rarr;
                                </a>
                            </div>
                            @empty
                            <div class="p-8 rounded-2xl border border-dashed border-[#E8E4DE] bg-[#F7F5F0] text-center space-y-2">
                                <div class="w-12 h-12 mx-auto rounded-full bg-[#FAF9F5] border border-[#E8E4DE] flex items-center justify-center text-[#8C5E3C] text-xl font-bold">
                                    📦
                                </div>
                                <h3 class="font-bold text-[#18181B] text-sm">Belum Ada Transaksi Pre-Order</h3>
                                <p class="text-xs text-[#57534E]">Anda belum memiliki pesanan pre-order aktif atau seluruh pesanan telah selesai/dihapus oleh Admin.</p>
                                <div class="pt-2">
                                    <a href="{{ route('catalog') }}" class="inline-flex items-center gap-1.5 bg-[#8C5E3C] hover:bg-[#744A29] text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow-2xs">
                                        <span>Lihat Katalog Mesin</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB 3: SETTINGS -->
                    <div id="tab-settings" class="tab-content {{ $defaultTab === 'settings' ? '' : 'hidden' }} space-y-6">
                        <div class="border-b border-[#E8E4DE] pb-4">
                            <h2 class="text-2xl font-extrabold text-[#18181B]">Pengaturan Akun</h2>
                            <p class="text-xs text-[#57534E]">Lengkapi informasi alamat dan kontak untuk verifikasi pesanan dan pengiriman kargo.</p>
                        </div>

                        <!-- Pesan Flash / Pemberitahuan Alur Pre-Order -->
                        @if(session('info'))
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-xs">
                                <svg class="w-5 h-5 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <strong class="font-bold block mb-1">Langkah Wajib Pre-Order Mesin:</strong>
                                    <span>{{ session('info') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3 shadow-xs">
                                <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if(isset($errors) && $errors->any())
                            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-xs">
                                @foreach($errors->all() as $error)
                                    <p>• {{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-5 text-sm">
                            @csrf
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-[#57534E]">Nama Profil <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', session('user_name', Auth::user()->name ?? '')) }}" required
                                    placeholder="Nama Lengkap Pemilik Akun"
                                    class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3.5 text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-[#57534E]">Alamat Lengkap <span class="text-rose-500">*</span></label>
                                <textarea name="address" rows="3" required
                                    placeholder="Contoh: Dusun Kesamben, RT 02 / RW 04, Mojowarno, Jombang, Jawa Timur"
                                    class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3.5 text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">{{ old('address', session('user_address', Auth::user()->address ?? '')) }}</textarea>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-[#57534E]">Nomor HP <span class="text-rose-500">*</span></label>
                                <input type="tel" name="phone" value="{{ old('phone', session('user_phone', Auth::user()->phone ?? '')) }}" required
                                    placeholder="Contoh: 081234567890"
                                    class="w-full bg-[#F7F5F0] border border-[#E8E4DE] rounded-xl px-4 py-3.5 text-[#18181B] focus:outline-none focus:border-[#8C5E3C] transition">
                            </div>

                            <div class="pt-3">
                                <button type="submit" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white font-bold px-8 py-3.5 rounded-xl text-xs transition shadow-xs cursor-pointer inline-flex items-center gap-2">
                                    @if(session('preorder_intent_product_id'))
                                        <span>Simpan Data Diri &amp; Lanjutkan Pre-Order</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    @else
                                        <span>Simpan Perubahan</span>
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

        ['profile', 'transactions', 'settings'].forEach(name => {
            const btn = document.getElementById('btn-' + name);
            if (btn) {
                btn.className = "w-full flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition text-left cursor-pointer text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B]";
            }
        });

        const activeTab = document.getElementById('tab-' + tabName);
        if (activeTab) {
            activeTab.classList.remove('hidden');
        }

        const activeBtn = document.getElementById('btn-' + tabName);
        if (activeBtn) {
            activeBtn.className = "w-full flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition text-left cursor-pointer bg-[#8C5E3C] text-white shadow-xs";
        }

        // Sinkronisasi URL query string tanpa reload halaman
        try {
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        } catch (e) {}
    }

    document.addEventListener('DOMContentLoaded', function() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab') || "{{ $defaultTab }}";
            if (['profile', 'transactions', 'settings'].includes(tabParam)) {
                switchTab(tabParam);
            }
        } catch (e) {}
    });
</script>
@endsection