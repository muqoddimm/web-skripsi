@php
$active = $activeMenu ?? ($currentMenu ?? 'dashboard');

// Ambil jumlah pending chats (chat yang butuh balasan admin)
$serverPendingChats = 0;
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('chat_logs')) {
        $serverPendingChats = \App\Models\ChatLog::where('status', 'pending_admin')->count();
    }
} catch (\Throwable $e) {
    $serverPendingChats = 0;
}

// Ambil pesanan baru/aktif
$serverNewOrders = 0;
try {
    if (function_exists('getOrdersList')) {
        $allOrders = getOrdersList();
        $serverNewOrders = count(collect($allOrders)->filter(fn($o) => in_array((int)($o['step'] ?? 2), [1, 2, 3]))->values());
    }
} catch (\Throwable $e) {
    $serverNewOrders = 0;
}

$seenOrdersCount = (int) session('admin_orders_seen_count', 0);
$serverUnseenOrders = max(0, $serverNewOrders - $seenOrdersCount);
if ($active === 'orders') {
    $serverUnseenOrders = 0;
}
@endphp

<!-- SIDEBAR ADMIN RJ TEKNIK -->
<aside class="w-72 bg-white border-r border-[#E8E4DE] hidden md:flex flex-col justify-between shrink-0 min-h-screen print:hidden">
    <div class="p-6 space-y-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('img/logo.png') }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=200&auto=format&fit=crop';" alt="Logo" class="h-10 w-10 object-contain rounded-full border border-[#E8E4DE]">
            </a>
            <div>
                <h1 class="font-black text-[#18181B] text-sm tracking-wide">RJ TEKNIK</h1>
                <span class="text-[10px] text-[#78716C]">Admin Control Panel</span>
            </div>
        </div>

        <div class="space-y-1.5">
            <p class="px-3 text-[10px] font-bold text-[#78716C] tracking-wider uppercase pb-2">MENU UTAMA</p>

            <!-- 1. Dashboard Admin -->
            @if($active === 'dashboard')
            <a href="{{ route('admin.dashboard') }}" class="w-full flex items-center gap-2.5 px-2.5 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                </svg>
                <span>Dashboard Admin</span>
            </a>
            @else
            <a href="{{ route('admin.dashboard') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                </svg>
                <span>Dashboard Admin</span>
            </a>
            @endif

            <!-- 2. Katalog & Produk PO -->
            @if($active === 'products')
            <a href="{{ route('admin.products.index') }}" class="w-full flex items-center gap-2.5 px-2.5 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Katalog & Produk PO</span>
            </a>
            @else
            <a href="{{ route('admin.products.index') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Katalog & Produk PO</span>
            </a>
            @endif

            <!-- 3. Status Pesanan & Perakitan -->
            @if($active === 'orders')
            <a href="{{ route('admin.orders.index', ['view' => 'orders']) }}" onclick="handleOrderMenuClick()" class="w-full flex items-center justify-between gap-2 px-2.5 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                    <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="truncate">Status Pesanan & Perakitan</span>
                </div>
                <span id="sidebar-order-badge-active" class="order-notif-badge hidden bg-amber-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs shrink-0">0</span>
            </a>
            @else
            <a href="{{ route('admin.orders.index', ['view' => 'orders']) }}" onclick="handleOrderMenuClick()" class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="truncate">Status Pesanan & Perakitan</span>
                </div>
                <span id="sidebar-order-badge-inactive" class="order-notif-badge {{ $serverUnseenOrders > 0 ? '' : 'hidden' }} bg-amber-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs shrink-0">{{ $serverUnseenOrders }}</span>
            </a>
            @endif

            <!-- 4. Rekapitulasi Penjualan -->
            @if($active === 'recap')
            <a href="{{ route('admin.recap') }}" class="w-full flex items-center gap-2.5 px-2.5 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l4-4 4 4 6-6M3 21h18M5 21v-7m4 7v-3m4 7v-5m4 7V9m4 12V5" />
                </svg>
                <span>Rekapitulasi Penjualan</span>
            </a>
            @else
            <a href="{{ route('admin.recap') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l4-4 4 4 6-6M3 21h18M5 21v-7m4 7v-3m4 7v-5m4 7V9m4 12V5" />
                </svg>
                <span>Rekapitulasi Penjualan</span>
            </a>
            @endif

            <!-- 5. Konsultasi Pelanggan & Log AI -->
            @if($active === 'chatlogs')
            <a href="{{ route('admin.chatlogs.index') }}" onclick="handleChatMenuClick()" class="w-full flex items-center justify-between gap-2 px-2.5 py-2.5 rounded-2xl text-xs font-bold transition bg-[#FAF2EC] text-[#74221A] shadow-2xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-1 h-5 bg-[#74221A] rounded-full shrink-0"></span>
                    <svg class="w-4 h-4 shrink-0 text-[#74221A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    <span class="truncate">Konsultasi Pelanggan & Log AI</span>
                </div>
                <span id="sidebar-chat-badge-active" class="chat-notif-badge hidden bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs shrink-0 animate-pulse">0</span>
            </a>
            @else
            <a href="{{ route('admin.chatlogs.index') }}" onclick="handleChatMenuClick()" class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-2xl text-xs font-medium text-[#57534E] hover:bg-[#F3EFEA] hover:text-[#18181B] transition">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-[#57534E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    <span class="truncate">Konsultasi Pelanggan & Log AI</span>
                </div>
                <span id="sidebar-chat-badge-inactive" class="chat-notif-badge {{ $serverPendingChats > 0 ? '' : 'hidden' }} bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs shrink-0 animate-pulse">{{ $serverPendingChats }}</span>
            </a>
            @endif
        </div>
    </div>

    <!-- FOOTER SIDEBAR -->
    <div class="p-6 border-t border-[#E8E4DE] space-y-4">
        <!-- RJ TEKNIK Administration & Management System -->
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-[#74221A] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div>
                <p class="text-xs font-bold text-[#18181B] leading-none">RJ TEKNIK Administration</p>
                <p class="text-[10px] text-[#78716C] mt-1 leading-none">Management System</p>
            </div>
        </div>

        <div class="space-y-2 pt-1">
            <a href="{{ route('home') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold text-[#57534E] hover:text-[#18181B] bg-[#F7F5F0] hover:bg-[#E8E4DE] border border-[#E8E4DE] transition">
                <span>← Lihat Website Utama</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition cursor-pointer">
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- FLOATING NOTIFIKASI TOAST REAL-TIME ADMIN (Z-INDEX 9999) -->
<div id="admin-toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

<!-- ELEMEN AUDIO NOTIFIKASI ADMIN -->
<audio id="admin-notif-audio" preload="auto">
    <source src="{{ asset('sounds/notification.wav') }}" type="audio/wav">
</audio>

<!-- SCRIPT ENGINE NOTIFIKASI REAL-TIME ADMIN -->
<script>
    window.currentAdminActiveMenu = "{{ $active }}";
    let lastMaxPendingChatId = null;
    let lastPendingCount = null;
    let lastOrdersCount = null;
    let lastOrderId = null;
    let isNotifAudioEnabled = true;
    let adminAudioCtx = null;

    // Handler klik menu sidebar agar nomor langsung hilang responsif
    function handleOrderMenuClick() {
        localStorage.setItem('admin_orders_seen_count', window.latestOrdersCount || 9999);
        document.querySelectorAll('.order-notif-badge').forEach(b => b.classList.add('hidden'));
    }

    function handleChatMenuClick() {
        document.querySelectorAll('.chat-notif-badge').forEach(b => b.classList.add('hidden'));
    }

    // Inisialisasi Web Audio Context hanya saat interaksi pertama admin
    function unlockAudioContextOnce() {
        try {
            if (!adminAudioCtx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) adminAudioCtx = new AudioCtx();
            }
            if (adminAudioCtx && adminAudioCtx.state === 'suspended') {
                adminAudioCtx.resume();
            }
        } catch(e) {}
    }

    ['click', 'touchstart', 'pointerdown', 'keydown'].forEach(evt => {
        document.addEventListener(evt, unlockAudioContextOnce, { passive: true });
    });

    // Pemutar Suara Notifikasi (DIPANGGIL HANYA KETIKA ADA TUGAS BARU ADMIN)
    function playAdminChime() {
        if (!isNotifAudioEnabled) return;
        unlockAudioContextOnce();

        try {
            const audioElem = document.getElementById('admin-notif-audio');
            if (audioElem) {
                audioElem.currentTime = 0;
                audioElem.volume = 1.0;
                const playPromise = audioElem.play();
                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        fallbackWebAudioChime();
                    });
                    return;
                }
            }
        } catch(e) {}

        fallbackWebAudioChime();
    }

    function fallbackWebAudioChime() {
        try {
            const ctx = adminAudioCtx || (window.AudioContext || window.webkitAudioContext ? new (window.AudioContext || window.webkitAudioContext)() : null);
            if (!ctx) return;
            if (ctx.state === 'suspended') ctx.resume();

            const t = ctx.currentTime;
            
            // Nada 1: E5 (659.25 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, t);
            gain1.gain.setValueAtTime(0.3, t);
            gain1.gain.exponentialRampToValueAtTime(0.001, t + 0.35);
            osc1.start(t);
            osc1.stop(t + 0.35);

            // Nada 2: B5 (987.77 Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(987.77, t + 0.12);
            gain2.gain.setValueAtTime(0.35, t + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, t + 0.6);
            osc2.start(t + 0.12);
            osc2.stop(t + 0.6);
        } catch(e) {
            console.warn('Audio chime fallback error:', e);
        }
    }

    function showAdminToast(title, message, icon = '🔔', linkUrl = '#', isAlert = false) {
        const container = document.getElementById('admin-toast-container');
        if (!container) return;

        const toastId = 'toast-' + Date.now();
        const borderColor = isAlert ? 'border-rose-400' : 'border-[#8C5E3C]';
        const badgeBg = isAlert ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-900';

        const toastHtml = `
            <div id="${toastId}" class="pointer-events-auto bg-white border-2 ${borderColor} shadow-2xl rounded-2xl p-4 flex items-start gap-3 transition-all duration-300 transform translate-y-2 opacity-0">
                <div class="w-9 h-9 rounded-xl ${badgeBg} flex items-center justify-center font-black text-base shrink-0">
                    ${icon}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <h4 class="text-xs font-black text-[#18181B] truncate">${title}</h4>
                        <span class="text-[9px] text-[#78716C]">Baru saja</span>
                    </div>
                    <p class="text-xs text-[#57534E] line-clamp-2 leading-relaxed mb-2.5">${message}</p>
                    <div class="flex items-center gap-2">
                        <a href="${linkUrl}" class="bg-[#8C5E3C] hover:bg-[#744A29] text-white text-[11px] font-bold px-3 py-1 rounded-lg transition shadow-2xs">
                            Lihat Rincian →
                        </a>
                        <button type="button" onclick="document.getElementById('${toastId}').remove()" class="text-[#78716C] hover:text-[#18181B] text-[11px] px-2 py-1 cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('${toastId}').remove()" class="text-[#A8A29E] hover:text-[#18181B] font-bold text-xs p-1 cursor-pointer">✕</button>
            </div>`;

        container.insertAdjacentHTML('beforeend', toastHtml);

        const toastElem = document.getElementById(toastId);
        if (toastElem) {
            setTimeout(() => {
                toastElem.classList.remove('translate-y-2', 'opacity-0');
                toastElem.classList.add('translate-y-0', 'opacity-100');
            }, 50);

            // Auto dismiss after 8 seconds
            setTimeout(() => {
                if (toastElem && toastElem.parentElement) {
                    toastElem.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toastElem.remove(), 300);
                }
            }, 8000);
        }
    }

    async function fetchAdminNotifications(isManual = false) {
        try {
            const res = await fetch("{{ route('admin.api.notifications') }}");
            if (!res.ok) return;
            const data = await res.json();
            if (data.status !== 'success') return;

            const currentMenu = window.currentAdminActiveMenu || 'dashboard';

            // 1. Logika Notifikasi Pesanan (Orders)
            const rawOrdersCount = Number(data.new_orders_count || 0);
            window.latestOrdersCount = rawOrdersCount;

            if (currentMenu === 'orders') {
                // Sedang berada di halaman status pesanan: bersihkan badge dan tandai dilihat
                localStorage.setItem('admin_orders_seen_count', rawOrdersCount);
                document.querySelectorAll('.order-notif-badge').forEach(badge => badge.classList.add('hidden'));
            } else {
                const seenOrdersCount = Number(localStorage.getItem('admin_orders_seen_count') || 0);
                if (rawOrdersCount > seenOrdersCount) {
                    const unreadOrders = rawOrdersCount - seenOrdersCount;
                    document.querySelectorAll('.order-notif-badge').forEach(badge => {
                        badge.textContent = unreadOrders;
                        badge.classList.remove('hidden');
                    });
                } else {
                    document.querySelectorAll('.order-notif-badge').forEach(badge => badge.classList.add('hidden'));
                }
            }

            // 2. Logika Notifikasi Chat Konsultasi Pelanggan
            const pendingChatsCount = Number(data.pending_chats_count || 0);

            if (currentMenu === 'chatlogs') {
                // Di dalam menu Konsultasi Pelanggan: nomor disembunyikan di sidebar
                // karena nomor telah dipecah langsung ke setiap user di daftar pelanggan!
                document.querySelectorAll('.chat-notif-badge').forEach(badge => badge.classList.add('hidden'));

                // Perbarui pemecahan nomor notifikasi per-pelanggan secara realtime
                if (typeof window.updateCustomerListBadgesRealtime === 'function') {
                    window.updateCustomerListBadgesRealtime(data.pending_chats || []);
                }
            } else {
                // Di menu lain (Dashboard, Katalog, Pesanan, Rekap): tampilkan total chat pending
                document.querySelectorAll('.chat-notif-badge').forEach(badge => {
                    if (pendingChatsCount > 0) {
                        badge.textContent = pendingChatsCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            }

            // 3. Deteksi Event Baru untuk Suara Chime & Toast
            const currentPendingId = Number(data.latest_pending_id || (data.pending_chats && data.pending_chats[0] ? data.pending_chats[0].id : 0));
            const currentPendingCount = Number(data.pending_chats_count || 0);
            const currentOrderId = String(data.latest_order_id || (data.new_orders && data.new_orders[0] ? data.new_orders[0].id : ''));

            // Patokan baseline saat pertama kali halaman admin dibuka
            if (lastMaxPendingChatId === null) {
                lastMaxPendingChatId = currentPendingId;
                lastPendingCount = currentPendingCount;
                lastOrdersCount = rawOrdersCount;
                lastOrderId = currentOrderId;
                return;
            }

            let hasNewPendingChat = false;
            let hasNewOrder = false;

            // Chat baru yang BUTUH balasan admin
            if (currentPendingId > lastMaxPendingChatId || (currentPendingCount > lastPendingCount && currentPendingCount > 0)) {
                hasNewPendingChat = true;
            }

            // Pesanan baru PO yang masuk
            if (rawOrdersCount > lastOrdersCount || (currentOrderId !== '' && currentOrderId !== lastOrderId && lastOrderId !== '')) {
                hasNewOrder = true;
            }

            // Bunyikan suara & toast HANYA jika ada tugas baru yang benar-benar butuh tindakan admin
            if (hasNewPendingChat) {
                playAdminChime();
                if (data.pending_chats && data.pending_chats[0]) {
                    const topChat = data.pending_chats[0];
                    showAdminToast(
                        '💬 Chat Butuh Balasan Admin',
                        `${topChat.user_name}: "${topChat.message}"`,
                        '💬',
                        topChat.url,
                        true
                    );
                }
            } else if (hasNewOrder) {
                playAdminChime();
                if (data.new_orders && data.new_orders[0]) {
                    const topOrder = data.new_orders[0];
                    showAdminToast(
                        '📦 Pesanan Pre-Order Baru Masuk',
                        `${topOrder.customer_name} memesan ${topOrder.product_name} (${topOrder.dp_price_fmt})`,
                        '📦',
                        topOrder.url,
                        false
                    );
                }
            }

            // Perbarui patokan state terakhir
            lastMaxPendingChatId = currentPendingId;
            lastPendingCount = currentPendingCount;
            lastOrdersCount = rawOrdersCount;
            lastOrderId = currentOrderId;

        } catch (e) {
            console.log('Admin notification poll error:', e);
        }
    }

    // Jalankan segera saat halaman dimuat, lalu poll setiap 2.5 detik untuk respon sangat reaktif
    document.addEventListener('DOMContentLoaded', function () {
        fetchAdminNotifications();
        setInterval(fetchAdminNotifications, 2500);
    });
</script>
