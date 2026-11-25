<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KONSULTASI PELANGGAN & LOG AI - Admin RJ TEKNIK</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Custom scrollbar styling for conversation panel */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #E8E4DE;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #8C5E3C;
        }
    </style>
</head>

<body class="h-full font-sans antialiased bg-[#F7F5F0] text-[#18181B] m-0 p-0 flex">

    @php
    $conversationsList = collect($conversations ?? []);
    $activeId = $activeConversation['id'] ?? null;
    @endphp

    <!-- SIDEBAR UTAMA ADMIN -->
    @include('admin.partials.sidebar', ['activeMenu' => 'chatlogs'])

    <!-- KONTEN UTAMA -->
    <main class="flex-grow flex flex-col min-h-screen overflow-y-auto bg-[#F7F5F0]">
        <!-- Flash Messages -->
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
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 font-bold cursor-pointer">✕</button>
            </div>
        </div>
        @endif

        <div class="p-6 sm:p-10 space-y-6 max-w-7xl w-full mx-auto">
            <!-- Header Judul Halaman -->
            <div class="border-b border-[#E8E4DE] pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl sm:text-3xl font-black text-[#18181B] tracking-tight">KONSULTASI PELANGGAN & LOG AI</h2>
                        <span id="title-pending-badge" class="{{ ($pendingTotal ?? 0) > 0 ? '' : 'hidden' }} bg-[#8C5E3C] text-white text-xs font-black px-3 py-1 rounded-full animate-pulse shadow-xs">
                            {{ $pendingTotal ?? 0 }} Butuh Balasan Admin
                        </span>
                    </div>
                    <p class="text-xs text-[#57534E] mt-1">
                        Antarmuka manajemen percakapan pelanggan. Riwayat chat terpusat per akun pelanggan dengan eskalasi balasan admin, penanganan pertanyaan di luar lingkup, dan filter ruang dialog.
                    </p>
                </div>
            </div>

            <!-- Hidden span for JS counter safety -->
            <span id="kpi-total-logs-count" class="hidden">{{ $totalLogs ?? 0 }}</span>

            <!-- KPI SUMMARY CARDS (ESKALASI ADMIN & DI LUAR LINGKUP) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div id="kpi-pending-card" class="bg-white border {{ ($pendingTotal ?? 0) > 0 ? 'border-[#8C5E3C] ring-2 ring-[#8C5E3C]/20' : 'border-[#E8E4DE]' }} rounded-3xl p-5 shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-[#8C5E3C] uppercase tracking-wider block">Butuh Balasan Admin</span>
                    <p id="kpi-pending-count" class="text-2xl font-black text-[#8C5E3C]">{{ $pendingTotal ?? 0 }} <span class="text-xs font-normal text-[#78716C]">Menunggu</span></p>
                    <span id="kpi-pending-desc" class="text-[11px] {{ ($pendingTotal ?? 0) > 0 ? 'text-[#8C5E3C] font-semibold animate-pulse' : 'text-[#78716C]' }} block truncate">
                        {{ ($pendingTotal ?? 0) > 0 ? 'Eskalasi pertanyaan kustom' : 'Semua pertanyaan tertangani' }}
                    </span>
                </div>

                <div class="bg-white border border-[#E8E4DE] rounded-3xl p-5 shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-[#78716C] uppercase tracking-wider block">Di Luar Lingkup</span>
                    <p class="text-2xl font-black text-amber-700">{{ $outOfScopeTotal ?? 0 }} <span class="text-xs font-normal text-[#78716C]">Query</span></p>
                    <span class="text-[11px] text-[#78716C] block truncate">Ditolak sopan oleh AI CS</span>
                </div>
            </div>

            <!-- CONTAINER UTAMA: ANTARMUKA PERCAKAPAN WHATSAPP WEB STYLE -->
            <div class="bg-white border border-[#E8E4DE] rounded-3xl shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-12 h-[750px] min-h-[600px]">

                <!-- ========================================== -->
                <!-- SIDEBAR KIRI: DAFTAR PELANGGAN TERDAFTAR  -->
                <!-- ========================================== -->
                <div class="md:col-span-5 lg:col-span-4 border-r border-[#E8E4DE] flex flex-col bg-white h-full overflow-hidden">
                    <!-- Header Sidebar Kiri -->
                    <div class="p-4 border-b border-[#E8E4DE] bg-[#FAF9F5] space-y-3 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-[#18181B] tracking-wide">Daftar Pelanggan</span>
                                <span id="sidebar-total-accounts-badge" class="bg-[#F3EFEA] border border-[#E8E4DE] text-[#8C5E3C] text-[10px] font-black px-2 py-0.5 rounded-full">
                                    {{ count($conversationsList) }} Akun
                                </span>
                            </div>
                            <span class="text-[10px] text-[#78716C] font-medium">1 Ruang / Pelanggan</span>
                        </div>

                        <!-- Kolom Pencarian Pelanggan (Live Filter) -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#78716C]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text" id="search-customer-input" onkeyup="filterCustomerList()"
                                placeholder="Cari nama atau nomor HP..."
                                class="w-full bg-white border border-[#E8E4DE] text-[#18181B] text-xs rounded-xl pl-9 pr-3 py-2 placeholder-[#78716C]/60 focus:outline-none focus:border-[#8C5E3C] transition shadow-2xs">
                        </div>

                        <!-- Filter Tab Pills -->
                        <div class="flex items-center gap-1.5 pt-1">
                            <button type="button" onclick="setConversationFilter('all', this)"
                                class="customer-filter-pill px-3 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer bg-[#8C5E3C] text-white shadow-2xs">
                                Semua ({{ count($conversationsList) }})
                            </button>
                            <button type="button" onclick="setConversationFilter('pending', this)"
                                class="customer-filter-pill px-3 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer bg-white text-[#57534E] border border-[#E8E4DE] hover:bg-[#F3EFEA]">
                                Butuh Balas ({{ $conversationsList->where('pending_count', '>', 0)->count() }})
                            </button>
                            <button type="button" onclick="setConversationFilter('registered', this)"
                                class="customer-filter-pill px-3 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer bg-white text-[#57534E] border border-[#E8E4DE] hover:bg-[#F3EFEA]">
                                Terdaftar ({{ $conversationsList->where('type', 'registered')->count() }})
                            </button>
                        </div>
                    </div>

                    <!-- List Pelanggan (Scrollable) -->
                    <div id="customer-list-container" class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-[#F7F5F0]">
                        @forelse($conversationsList as $conv)
                        @php
                            $isSelected = ($activeId !== null && (string)$activeId === (string)$conv['id']);
                            $initials = collect(explode(' ', $conv['name']))
                                ->filter(fn($w) => strlen(trim($w)) > 0)
                                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                ->take(2)
                                ->join('');
                            if (empty($initials)) $initials = 'RJ';

                            $timeLabel = '';
                            if ($conv['latest_activity']) {
                                $actTime = \Carbon\Carbon::parse($conv['latest_activity']);
                                if ($actTime->isToday()) {
                                    $timeLabel = $actTime->format('H:i');
                                } elseif ($actTime->isYesterday()) {
                                    $timeLabel = 'Kemarin';
                                } else {
                                    $timeLabel = $actTime->format('d M');
                                }
                            }
                        @endphp
                        <a href="{{ route('admin.chatlogs.index', ['user_id' => $conv['id']]) }}"
                            id="customer-item-{{ $conv['id'] }}"
                            data-name="{{ strtolower($conv['name']) }}"
                            data-phone="{{ strtolower($conv['phone'] ?? '') }}"
                            data-type="{{ $conv['type'] }}"
                            data-ai="{{ ($conv['is_ai_enabled'] ?? true) ? 'on' : 'off' }}"
                            data-pending="{{ $conv['pending_count'] > 0 ? 'true' : 'false' }}"
                            class="customer-item group relative block p-3.5 transition cursor-pointer hover:bg-[#FAF9F5] {{ $isSelected ? 'bg-[#F3EFEA] border-l-4 border-[#8C5E3C]' : '' }}">
                            <div class="flex items-start gap-3">
                                <!-- Avatar -->
                                <div class="relative shrink-0 mt-0.5">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-xs shadow-2xs {{ $isSelected ? 'bg-[#8C5E3C] text-white' : 'bg-[#FAF9F5] border border-[#E8E4DE] text-[#18181B]' }}">
                                        {{ $initials }}
                                    </div>
                                    @if($conv['type'] === 'registered')
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white" title="Akun Terdaftar"></span>
                                    @else
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-[#A8A29E] border-2 border-white" title="Pengunjung Tamu"></span>
                                    @endif
                                </div>

                                <!-- Text info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h4 class="text-xs font-black text-[#18181B] truncate {{ $isSelected ? 'text-[#8C5E3C]' : '' }}">
                                            {{ $conv['name'] }}
                                        </h4>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span class="text-[10px] text-[#78716C] font-semibold">
                                                {{ $timeLabel }}
                                            </span>
                                            <!-- Tombol Hapus Percakapan di Sidebar -->
                                            <button type="button" 
                                                onclick="deleteConversation(event, '{{ $conv['id'] }}', '{{ addslashes($conv['name']) }}')"
                                                class="opacity-0 group-hover:opacity-100 hover:opacity-100 p-1 text-[#A8A29E] hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                                title="Hapus percakapan dengan {{ $conv['name'] }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Nomor HP -->
                                    <div class="flex items-center gap-1.5 text-[10px] text-[#57534E] mb-1 font-medium">
                                        <svg class="w-2.5 h-2.5 text-[#78716C] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span class="truncate">{{ $conv['phone'] ?: 'Tidak ada no. telp' }}</span>
                                    </div>

                                    <!-- Snippet Pesan & Badge -->
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] text-[#78716C] truncate font-medium">
                                            {{ $conv['latest_snippet'] }}
                                        </p>
                                        <div class="customer-badges-container flex items-center gap-1 shrink-0">
                                            @if(!($conv['is_ai_enabled'] ?? true))
                                            <span class="ai-status-sidebar-badge bg-amber-100 text-amber-900 border border-amber-300 text-[8px] font-black px-1.5 py-0.2 rounded-full shrink-0">
                                                👨‍🔧 Takeover
                                            </span>
                                            @endif
                                            @if($conv['pending_count'] > 0)
                                            <span class="user-pending-badge bg-rose-600 text-white text-[10px] font-black min-w-[20px] h-5 px-1.5 rounded-full flex items-center justify-center shadow-xs animate-pulse">
                                                {{ $conv['pending_count'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="p-8 text-center text-xs text-[#78716C] space-y-2">
                            <span class="text-2xl block">💬</span>
                            <p class="font-bold">Belum ada pelanggan terdaftar.</p>
                        </div>
                        @endforelse

                        <!-- Empty state hasil search JS -->
                        <div id="no-customer-found" class="hidden p-8 text-center text-xs text-[#78716C] space-y-2">
                            <span class="text-2xl block">🔍</span>
                            <p class="font-bold">Tidak ada pelanggan yang cocok dengan pencarian.</p>
                        </div>
                    </div>
                </div>


                <!-- ============================================================== -->
                <!-- PANEL KANAN: CHAT WINDOW KRONOLOGIS & DIRECT REPLY FORM        -->
                <!-- ============================================================== -->
                <div class="md:col-span-7 lg:col-span-8 flex flex-col bg-[#F7F5F0]/50 h-full overflow-hidden">
                    @if($activeConversation)
                    @php
                        $actInitials = collect(explode(' ', $activeConversation['name']))
                            ->filter(fn($w) => strlen(trim($w)) > 0)
                            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                            ->take(2)
                            ->join('');
                        if (empty($actInitials)) $actInitials = 'RJ';
                        $isGuest = ($activeConversation['type'] === 'guest');
                        $activeLogs = $activeConversation['logs'] ?? collect([]);
                    @endphp

                    <!-- 1. HEADER CHAT WINDOW AKTIF -->
                    <div class="bg-white border-b border-[#E8E4DE] px-6 py-3.5 flex items-center justify-between shadow-2xs shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Avatar Pelanggan -->
                            <div class="relative shrink-0">
                                <div class="w-11 h-11 rounded-2xl bg-[#8C5E3C] text-white flex items-center justify-center font-black text-sm shadow-xs">
                                    {{ $actInitials }}
                                </div>
                                @if(!$isGuest)
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white" title="Pelanggan Terdaftar"></span>
                                @endif
                            </div>

                            <!-- Detail Pelanggan (Nama saja) -->
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-black text-[#18181B] truncate">
                                    {{ $activeConversation['name'] }}
                                </h3>
                            </div>
                        </div>

                        <!-- Status Evaluasi, Notifikasi Balasan, & FITUR TOGGLE AI ON/OFF -->
                        @php
                            $isAiActive = (bool) ($activeConversation['is_ai_enabled'] ?? true);
                        @endphp
                        <div class="flex items-center gap-2.5 shrink-0">
                            <!-- FITUR AI ON/OFF (ADMIN TAKEOVER) -->
                            <div id="ai-toggle-wrapper" class="flex items-center gap-2.5 px-3 py-1.5 rounded-2xl border transition shadow-2xs {{ $isAiActive ? 'bg-emerald-50/80 border-emerald-300' : 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' }}">
                                <div class="flex flex-col text-right">
                                    <span id="ai-toggle-label" class="text-[10px] font-black uppercase tracking-wider flex items-center gap-1 justify-end {{ $isAiActive ? 'text-emerald-800' : 'text-amber-900' }}">
                                        <span id="ai-toggle-dot" class="w-2 h-2 rounded-full {{ $isAiActive ? 'bg-emerald-500 animate-pulse' : 'bg-amber-600' }}"></span>
                                        <span id="ai-toggle-text">{{ $isAiActive ? 'AI AKTIF (ON)' : 'MODE ADMIN (OFF)' }}</span>
                                    </span>
                                    <span id="ai-toggle-desc" class="text-[9px] {{ $isAiActive ? 'text-emerald-700/80 font-medium' : 'text-amber-800 font-bold' }}">
                                        {{ $isAiActive ? 'Otomatis balas pesan' : 'Takeover manual admin' }}
                                    </span>
                                </div>

                                <!-- Tombol Tutup Sesi (Smart Reset Pelanggan) -->
                                <button type="button"
                                    onclick="closeConversationSession('{{ $activeConversation['id'] }}', '{{ addslashes($activeConversation['name']) }}')"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-white hover:bg-[#F3EFEA] text-[#57534E] hover:text-[#18181B] border border-[#E8E4DE] text-xs font-bold transition shadow-2xs cursor-pointer"
                                    title="Tutup sesi percakapan (Reset tampilan obrolan di layar pelanggan saat refresh)">
                                    <span class="text-emerald-600 font-black">✓</span>
                                    <span class="hidden sm:inline">Tutup Sesi</span>
                                </button>

                                <!-- Tombol Switch Toggle Interaktif -->
                                <button type="button"
                                    id="btn-ai-toggle"
                                    onclick="toggleAiStatus('{{ $activeConversation['id'] }}')"
                                    data-active="{{ $isAiActive ? 'true' : 'false' }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isAiActive ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                    title="{{ $isAiActive ? 'Klik untuk mematikan AI (Mode Admin Takeover)' : 'Klik untuk mengaktifkan AI kembali (ON)' }}">
                                    <span class="sr-only">Toggle AI CS</span>
                                    <span id="ai-toggle-knob"
                                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ $isAiActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>

                            <!-- Status Respon -->
                            <div id="customer-header-status-badge">
                                @if($activeConversation['pending_count'] > 0)
                                <div class="hidden sm:flex items-center gap-1 bg-[#8C5E3C] text-white text-[11px] font-black px-2.5 py-1.5 rounded-xl shadow-xs animate-pulse">
                                    <span>⏳</span>
                                    <span>{{ $activeConversation['pending_count'] }} Butuh Balas</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($activeLogs->isNotEmpty())
                    <!-- TOOLBAR MANAJEMEN PEMBERSIHAN CHAT (MUNCUL SAAT KLIK 2X / DOUBLE CLICK PESAN) -->
                    <div id="chat-cleaning-toolbar" class="hidden bg-white border-b border-[#E8E4DE] px-6 py-2.5 items-center justify-between gap-3 text-xs shrink-0 shadow-2xs">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-[#18181B] select-none hover:text-[#8C5E3C] transition">
                                <input type="checkbox" id="select-all-chats" onchange="toggleSelectAllChats(this)"
                                    class="w-4 h-4 rounded text-[#8C5E3C] border-[#D6D3D1] focus:ring-[#8C5E3C] cursor-pointer">
                                <span>Pilih Semua Chat</span>
                            </label>
                            <span class="text-[#E8E4DE]">|</span>
                            <span id="selected-chat-count-label" class="text-[11px] text-[#78716C] font-semibold">0 chat dipilih</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Tombol Hapus Terpilih (Cheklis) -->
                            <button type="button" id="btn-delete-selected" onclick="deleteSelectedChats()" disabled
                                class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-[#F7F5F0] text-[#A8A29E] cursor-not-allowed transition flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Hapus Terpilih</span>
                            </button>

                            <!-- Tombol Hapus Semua Chat Pelanggan Ini -->
                            <button type="button" onclick="deleteAllCustomerChats()"
                                class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition cursor-pointer flex items-center gap-1.5 shadow-2xs"
                                title="Bersihkan seluruh riwayat chat pelanggan ini">
                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Hapus Semua</span>
                            </button>

                            <!-- Tombol Batal Mode Seleksi -->
                            <button type="button" onclick="exitSelectionMode()"
                                class="px-2.5 py-1.5 rounded-xl font-medium text-xs bg-white hover:bg-[#F3EFEA] text-[#78716C] border border-[#E8E4DE] transition cursor-pointer">
                                ✕ Batal
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- 2. STREAM PERCAKAPAN KRONOLOGIS (SCROLLABLE) -->
                    <div id="chat-messages-container" class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4 bg-[#FAF9F5]">
                        <!-- BANNER ALERT MODE ADMIN TAKEOVER -->
                        <div id="banner-admin-takeover" class="{{ $isAiActive ? 'hidden' : 'flex' }} items-center justify-between gap-3 bg-amber-50 border border-amber-300 text-amber-900 px-4 py-2.5 rounded-2xl text-xs shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="text-base">👨‍🔧</span>
                                <div>
                                    <strong class="font-bold">Mode Admin (Takeover Manual) Sedang Aktif:</strong>
                                    <span class="text-amber-800 ml-1">AI dinonaktifkan sementara untuk pelanggan ini. Semua pesan baru pelanggan akan ditangani langsung oleh Admin tanpa balasan otomatis AI.</span>
                                </div>
                            </div>
                            <button type="button" onclick="toggleAiStatus('{{ $activeConversation['id'] }}')"
                                class="bg-amber-800 hover:bg-amber-900 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 shadow-2xs">
                                Aktifkan AI (ON)
                            </button>
                        </div>
                        @php
                            $currentDateHeader = null;
                        @endphp

                        @forelse($activeLogs as $log)
                        @php
                            $created = $log->created_at ? \Carbon\Carbon::parse($log->created_at) : now();
                            $dateStr = $created->isToday() ? 'Hari Ini' : ($created->isYesterday() ? 'Kemarin' : $created->translatedFormat('d F Y'));

                            $replied = $log->replied_at ? \Carbon\Carbon::parse($log->replied_at) : null;
                            $repliedTimeStr = $replied ? $replied->format('H:i') : ($log->updated_at ? $log->updated_at->format('H:i') : $created->format('H:i'));

                            $category = $log->category ?? ($log->is_out_of_scope ? 'out_of_scope_irrelevant' : 'in_scope_db');
                            $userMsg = $log->user_message ?? $log->message ?? '';
                            $botResp = $log->bot_response ?? $log->response ?? '';
                            $adminReply = $log->admin_reply ?? null;
                            $calledFn = $log->called_function ?? null;
                        @endphp

                        <!-- Pembatas Tanggal Percakapan -->
                        @if($currentDateHeader !== $dateStr)
                            @php $currentDateHeader = $dateStr; @endphp
                            <div class="flex justify-center my-3">
                                <span class="bg-white/90 border border-[#E8E4DE] text-[10px] font-bold text-[#78716C] px-3 py-1 rounded-full shadow-2xs uppercase tracking-wider">
                                    {{ $dateStr }}
                                </span>
                            </div>
                        @endif

                        <!-- CHAT ITEM ROW DENGAN CHECKBOX & TOMBOL HAPUS -->
                        <div class="chat-log-group relative group flex items-start gap-2.5 w-full py-1.5 transition rounded-2xl hover:bg-black/[0.02] px-2 -mx-2 select-text cursor-pointer"
                            id="chat-row-{{ $log->id }}"
                            data-id="{{ $log->id }}"
                            ondblclick="handleChatDoubleClick(event, {{ $log->id }})"
                            title="Klik 2x untuk memilih & menghapus pesan">
                            <!-- Checkbox Cheklis per Chat (Tersembunyi, muncul saat klik 2x / mode pilih aktif) -->
                            <div class="chat-checkbox-container pt-2 shrink-0 hidden">
                                <label class="cursor-pointer flex items-center justify-center p-1 rounded-lg hover:bg-white transition" title="Pilih pesan ini untuk dihapus">
                                    <input type="checkbox" value="{{ $log->id }}"
                                        class="chat-item-checkbox w-4 h-4 rounded text-[#8C5E3C] border-[#D6D3D1] focus:ring-[#8C5E3C] cursor-pointer"
                                        onchange="handleChatCheckboxChange()">
                                </label>
                            </div>

                            <!-- Konten Pesan -->
                            <div class="flex-1 space-y-3 min-w-0">
                                <!-- ============================================== -->
                                <!-- 1. PESAN DARI PELANGGAN (KIRI, LATAR PUTIH)    -->
                                <!-- ============================================== -->
                                @if(!empty($userMsg) && $userMsg !== 'Konsultasi Lanjutan dengan Admin RJ TEKNIK')
                                <div class="flex flex-col items-start max-w-[85%] sm:max-w-[75%] space-y-1">
                                    <div class="flex items-center gap-1.5 px-1">
                                        <span class="text-[10px] font-bold text-[#57534E]">👤 {{ $activeConversation['name'] }}</span>
                                        <span class="text-[9px] text-[#78716C]">• {{ $created->format('H:i') }} WIB</span>
                                    </div>
                                    <div class="bg-white border border-[#E8E4DE] text-[#18181B] px-4 py-3 rounded-2xl rounded-tl-xs shadow-xs text-xs sm:text-sm leading-relaxed font-medium">
                                        <div>{!! nl2br(e($userMsg)) !!}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- ============================================================== -->
                                <!-- 2. BALASAN ASISTEN AI CS RJ TEKNIK (KIRI, KREM DENGAN BADGE)   -->
                                <!-- ============================================================== -->
                                @if(!empty($botResp) && $botResp !== 'Admin RJ TEKNIK terhubung langsung dalam percakapan.')
                                <div class="flex flex-col items-start max-w-[88%] sm:max-w-[80%] space-y-1 ml-2 sm:ml-4">
                                    <div class="flex items-center gap-2 px-1 flex-wrap">
                                        <span class="text-[10px] font-black text-[#8C5E3C] flex items-center gap-1">
                                            <span>🤖</span> Asisten AI CS RJ TEKNIK
                                        </span>
                                        <span class="text-[9px] text-[#78716C]">• {{ $created->format('H:i') }} WIB</span>

                                        <!-- Klasifikasi AI Badge -->
                                        @if($category === 'out_of_scope_irrelevant')
                                        <span class="bg-amber-50 text-amber-800 border border-amber-200 text-[9px] font-bold px-2 py-0.5 rounded-full">
                                            🚫 Di Luar Lingkup
                                        </span>
                                        @elseif($category === 'needs_admin' || $log->status === 'pending_admin')
                                        <span class="bg-[#8C5E3C] text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-2xs">
                                            ⏳ Butuh Balasan Admin
                                        </span>
                                        @else
                                        <span class="bg-emerald-50 text-emerald-800 border border-emerald-200 text-[9px] font-bold px-2 py-0.5 rounded-full">
                                            ✓ Grounding Data MySQL
                                        </span>
                                        @endif
                                    </div>

                                    <div class="bg-[#FAF9F5] border border-[#E8E4DE] text-[#18181B] px-4 py-3 rounded-2xl rounded-tl-xs shadow-xs text-xs sm:text-sm leading-relaxed">
                                        <div class="prose prose-xs text-[#18181B] font-normal">{!! nl2br(e($botResp)) !!}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- ============================================================== -->
                                <!-- 3. BALASAN MANUAL ADMIN RJ TEKNIK (KANAN, WARNA KOPI COKELAT)  -->
                                <!-- ============================================================== -->
                                @if(!empty($adminReply))
                                <div class="flex flex-col items-end max-w-[85%] sm:max-w-[75%] ml-auto space-y-1">
                                    <div class="flex items-center gap-1.5 px-1 justify-end">
                                        <span class="text-[9px] text-[#78716C]">{{ $repliedTimeStr }} WIB •</span>
                                        <span class="text-[10px] font-black text-[#8C5E3C] flex items-center gap-1">
                                            <span>👨‍🔧</span> Admin RJ TEKNIK
                                        </span>
                                    </div>
                                    <div class="bg-[#8C5E3C] text-white border border-[#744A29] px-4.5 py-3 rounded-2xl rounded-tr-xs shadow-sm text-xs sm:text-sm leading-relaxed font-medium">
                                        <div>{!! nl2br(e($adminReply)) !!}</div>
                                        <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-amber-200 font-bold select-none">
                                            <span>Terkirim ke Pelanggan</span>
                                            <span class="text-xs leading-none">✓✓</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Tombol Hapus Satuan (Hover / Mode Seleksi) -->
                            <button type="button" onclick="deleteSingleChat({{ $log->id }})"
                                class="chat-delete-btn opacity-0 group-hover:opacity-100 transition p-1.5 text-[#A8A29E] hover:text-rose-600 hover:bg-rose-50 rounded-xl cursor-pointer shrink-0 self-start mt-1.5"
                                title="Hapus pesan ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        @empty
                        <div id="chat-empty-state" class="h-full flex flex-col items-center justify-center text-center p-8 text-[#78716C] space-y-3">
                            <div class="w-16 h-16 rounded-3xl bg-white border border-[#E8E4DE] flex items-center justify-center text-3xl shadow-xs">
                                💬
                            </div>
                            <h4 class="font-black text-sm text-[#18181B]">Belum Ada Riwayat Percakapan</h4>
                            <p class="text-xs max-w-xs text-[#57534E]">
                                Pelanggan ini belum mengirimkan pesan konsultasi. Admin dapat memulai pesan balasan atau menyapa langsung di bawah.
                            </p>
                        </div>
                        @endforelse
                    </div>

                    <!-- 3. KOLOM BALAS LANGSUNG (ADMIN DIRECT REPLY FORM) -->
                    <div class="bg-white border-t border-[#E8E4DE] p-3.5 sm:p-4 shrink-0 space-y-2">
                        <!-- Quick Response Template Chips -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px] custom-scrollbar">
                            <span class="text-[10px] font-bold text-[#78716C] uppercase shrink-0">Template Cepat:</span>
                            <button type="button" onclick="insertReplyTemplate('kustom')"
                                class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-2.5 py-1 rounded-lg transition font-medium cursor-pointer"
                                title="Gunakan template format pengajuan kustom spesifikasi drum & mesin">
                                🛠️ Kustom Spesifikasi
                            </button>
                            <button type="button" onclick="insertReplyTemplate('jadwal')"
                                class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-2.5 py-1 rounded-lg transition font-medium cursor-pointer"
                                title="Informasi jadwal kunjungan workshop">
                                📍 Jadwal Workshop
                            </button>
                            <button type="button" onclick="insertReplyTemplate('estimasi')"
                                class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-2.5 py-1 rounded-lg transition font-medium cursor-pointer"
                                title="Informasi estimasi waktu perakitan PO">
                                ⏱️ Estimasi PO
                            </button>
                        </div>

                        <!-- Form Balas Langsung -->
                        <form id="admin-direct-reply-form"
                            action="{{ route('admin.chatlogs.replyCustomer', $activeConversation['id']) }}"
                            method="POST"
                            onsubmit="submitAdminReply(event)">
                            @csrf
                            <div class="flex items-end gap-2 bg-[#FAF9F5] border border-[#E8E4DE] rounded-2xl p-2 focus-within:border-[#8C5E3C] focus-within:ring-1 focus-within:ring-[#8C5E3C]/30 transition shadow-2xs">
                                <textarea id="admin-reply-textarea" name="admin_reply" rows="1" required
                                    placeholder="Ketik balasan resmi Admin ke {{ $activeConversation['name'] }}... (Shift+Enter untuk baris baru)"
                                    oninput="autoResizeAdminTextarea(this)"
                                    onkeydown="handleReplyKeydown(event)"
                                    class="w-full bg-transparent border-0 text-xs sm:text-sm text-[#18181B] placeholder-[#78716C]/60 focus:outline-none resize-none px-2 py-1 leading-relaxed overflow-y-hidden"
                                    style="min-height: 44px; max-height: 260px;"></textarea>
                                
                                <button type="submit" id="btn-send-reply"
                                    class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-4 h-11 rounded-xl font-bold text-xs transition cursor-pointer shrink-0 flex items-center gap-1.5 shadow-sm active:scale-95 self-end">
                                    <span>Kirim</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                        <div class="flex items-center justify-between text-[10px] text-[#78716C] px-1">
                            <span id="reply-mode-status-note">
                                @if(!$isAiActive)
                                <span class="text-amber-800 font-bold flex items-center gap-1">
                                    <span>👨‍🔧</span>
                                    <span>Mode Admin Takeover: Balasan Anda terkirim langsung tanpa balasan otomatis AI.</span>
                                </span>
                                @endif
                            </span>
                        </div>
                    </div>

                    @else
                    <!-- TAMPILAN JIKA BELUM MEMILIH PELANGGAN -->
                    <div class="h-full flex flex-col items-center justify-center text-center p-8 text-[#78716C] space-y-4">
                        <div class="w-20 h-20 rounded-3xl bg-white border border-[#E8E4DE] flex items-center justify-center text-4xl shadow-xs">
                            💬
                        </div>
                        <div>
                            <h3 class="font-black text-lg text-[#18181B]">Pilih Ruang Percakapan Pelanggan</h3>
                            <p class="text-xs text-[#57534E] max-w-sm mt-1">
                                Klik salah satu akun pelanggan di sidebar sebelah kiri untuk melihat seluruh riwayat dialog konsultasi dan mengirim balasan admin.
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </main>

    <!-- SCRIPT INTERAKTIF MANAJEMEN PERCAKAPAN WHATSAPP WEB STYLE -->
    <script>
        // 1. Auto-scroll chat container ke posisi paling bawah saat halaman dibuka
        function scrollChatToBottom() {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            scrollChatToBottom();
            const textarea = document.getElementById('admin-reply-textarea');
            if (textarea) {
                textarea.focus();
            }
        });

        // 2. Filter Pencarian Nama atau No. HP Pelanggan di Sidebar Kiri
        function filterCustomerList() {
            const query = document.getElementById('search-customer-input').value.toLowerCase().trim();
            const items = document.querySelectorAll('.customer-item');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                const phone = item.getAttribute('data-phone') || '';
                const matches = name.includes(query) || phone.includes(query);

                if (matches) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            const noFound = document.getElementById('no-customer-found');
            if (noFound) {
                if (visibleCount === 0) {
                    noFound.classList.remove('hidden');
                } else {
                    noFound.classList.add('hidden');
                }
            }
        }

        // 3. Filter Tab Pills ("Semua", "Butuh Balas", "Terdaftar")
        function setConversationFilter(filterType, btn) {
            // Update button UI
            document.querySelectorAll('.customer-filter-pill').forEach(b => {
                b.classList.remove('bg-[#8C5E3C]', 'text-white', 'shadow-2xs');
                b.classList.add('bg-white', 'text-[#57534E]', 'border', 'border-[#E8E4DE]');
            });
            btn.classList.remove('bg-white', 'text-[#57534E]', 'border', 'border-[#E8E4DE]');
            btn.classList.add('bg-[#8C5E3C]', 'text-white', 'shadow-2xs');

            const items = document.querySelectorAll('.customer-item');
            let visibleCount = 0;

            items.forEach(item => {
                const isPending = item.getAttribute('data-pending') === 'true';
                const type = item.getAttribute('data-type') || '';

                let show = true;
                if (filterType === 'pending') {
                    show = isPending;
                } else if (filterType === 'registered') {
                    show = (type === 'registered');
                }

                if (show) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            const noFound = document.getElementById('no-customer-found');
            if (noFound) {
                if (visibleCount === 0) {
                    noFound.classList.remove('hidden');
                } else {
                    noFound.classList.add('hidden');
                }
            }
        }

        // 3b. Perbarui Pemecahan Notifikasi Per Pelanggan Secara Real-Time
        window.updateCustomerListBadgesRealtime = function(pendingChats) {
            const userPendingMap = {};
            (pendingChats || []).forEach(chat => {
                const uid = String(chat.user_id);
                userPendingMap[uid] = (userPendingMap[uid] || 0) + 1;
            });

            document.querySelectorAll('.customer-item').forEach(item => {
                const rawId = item.id ? item.id.replace('customer-item-', '') : null;
                if (!rawId) return;

                let count = userPendingMap[rawId] || 0;
                if (count === 0 && !rawId.startsWith('guest_')) {
                    count = userPendingMap['guest_' + rawId] || 0;
                }

                item.setAttribute('data-pending', count > 0 ? 'true' : 'false');

                const badgeContainer = item.querySelector('.customer-badges-container');
                let pendingBadge = item.querySelector('.user-pending-badge');

                if (count > 0) {
                    if (pendingBadge) {
                        pendingBadge.textContent = count;
                    } else if (badgeContainer) {
                        badgeContainer.insertAdjacentHTML('beforeend', `
                            <span class="user-pending-badge bg-rose-600 text-white text-[10px] font-black min-w-[20px] h-5 px-1.5 rounded-full flex items-center justify-center shadow-xs animate-pulse">
                                ${count}
                            </span>
                        `);
                    }
                } else {
                    if (pendingBadge) {
                        pendingBadge.remove();
                    }
                }
            });

            // Perbarui tombol filter "Butuh Balas (X)"
            const uniquePendingUsers = Object.keys(userPendingMap).length;
            const pendingFilterBtn = document.querySelector('button[onclick*="pending"]');
            if (pendingFilterBtn) {
                pendingFilterBtn.textContent = `Butuh Balas (${uniquePendingUsers})`;
            }
        };

        // 4. Masukkan Template Balasan Cepat ke Textarea
        const QUICK_TEMPLATES = {
            kustom: `Halo Admin RJ TEKNIK, saya ingin mengajukan permintaan custom Mesin Roasting Kopi di luar spesifikasi katalog standar.

Berikut detail kebutuhan saya:

- Kapasitas mesin:
- Diameter drum:
- Panjang drum:
- Material drum:
- Spesifikasi khusus lainnya:
- Kebutuhan atau tujuan penggunaan:

Apakah spesifikasi tersebut memungkinkan untuk dibuat atau disesuaikan?

Mohon bantuannya untuk melakukan konsultasi dan estimasi harga. Terima kasih.`,
            jadwal: `Halo Kak, untuk jadwal kunjungan dan konsultasi langsung di bengkel workshop Dawar Blandong Mojokerto kami buka Senin–Sabtu jam 08.00–16.00 WIB. Kami siap menyambut kedatangan Anda.`,
            estimasi: `Estimasi perakitan mesin Pre-Order membutuhkan waktu 14 hingga 29 hari kerja sejak pembayaran DP 20% diverifikasi. Progres setiap tahap pengerjaan dapat Anda pantau di menu Status Pesanan.`
        };

        function autoResizeAdminTextarea(textarea) {
            if (!textarea) return;
            textarea.style.height = 'auto';
            const maxHeight = 260;
            const newHeight = Math.min(textarea.scrollHeight, maxHeight);
            textarea.style.height = (newHeight < 44 ? 44 : newHeight) + 'px';
            if (textarea.scrollHeight > maxHeight) {
                textarea.style.overflowY = 'auto';
            } else {
                textarea.style.overflowY = 'hidden';
            }
        }

        function insertReplyTemplate(keyOrText) {
            const text = QUICK_TEMPLATES[keyOrText] !== undefined ? QUICK_TEMPLATES[keyOrText] : keyOrText;
            const textarea = document.getElementById('admin-reply-textarea');
            if (textarea) {
                textarea.value = text;
                autoResizeAdminTextarea(textarea);
                textarea.focus();
                textarea.scrollTop = 0;
            }
        }

        // 5. Dukungan Tombol Enter untuk Kirim (Shift+Enter untuk baris baru, Enter di Mobile untuk paragraf)
        function handleReplyKeydown(event) {
            autoResizeAdminTextarea(event.target);

            if (event.key === 'Enter') {
                if (event.shiftKey) {
                    return;
                }

                const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || (window.innerWidth < 768);

                if (isMobile) {
                    return;
                } else {
                    event.preventDefault();
                    document.getElementById('admin-direct-reply-form').dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        }

        // 6. Pengiriman Balasan Admin secara AJAX Real-Time dengan DOM update instan
        async function submitAdminReply(event) {
            event.preventDefault();
            const textarea = document.getElementById('admin-reply-textarea');
            const replyText = textarea.value.trim();
            if (!replyText) return;

            const form = event.target;
            const actionUrl = form.action;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const btnSend = document.getElementById('btn-send-reply');

            // Disabled state saat mengirim
            if (btnSend) {
                btnSend.disabled = true;
                btnSend.innerHTML = '<span>Mengirim...</span>';
            }

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ admin_reply: replyText })
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    // Tambahkan bubble balasan admin ke stream chat
                    const container = document.getElementById('chat-messages-container');
                    const nowTime = data.time_str || (new Date()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    const adminBubbleHtml = `
                        <div class="chat-log-group relative group flex items-start gap-2.5 w-full py-1.5 transition rounded-2xl hover:bg-black/[0.02] px-2 -mx-2 select-text cursor-pointer"
                            id="chat-row-${data.log_id || ('temp-' + Date.now())}"
                            data-id="${data.log_id || 0}"
                            ondblclick="handleChatDoubleClick(event, ${data.log_id || 0})"
                            title="Klik 2x untuk memilih & menghapus pesan">
                            <div class="chat-checkbox-container pt-2 shrink-0 ${isSelectionMode ? '' : 'hidden'}">
                                <label class="cursor-pointer flex items-center justify-center p-1 rounded-lg hover:bg-white transition" title="Pilih pesan ini untuk dihapus">
                                    <input type="checkbox" value="${data.log_id || 0}"
                                        class="chat-item-checkbox w-4 h-4 rounded text-[#8C5E3C] border-[#D6D3D1] focus:ring-[#8C5E3C] cursor-pointer"
                                        onchange="handleChatCheckboxChange()">
                                </label>
                            </div>
                            <div class="flex-1 space-y-3 min-w-0">
                                <div class="flex flex-col items-end max-w-[85%] sm:max-w-[75%] ml-auto space-y-1">
                                    <div class="flex items-center gap-1.5 px-1 justify-end">
                                        <span class="text-[9px] text-[#78716C]">${nowTime} WIB •</span>
                                        <span class="text-[10px] font-black text-[#8C5E3C] flex items-center gap-1">
                                            <span>👨‍🔧</span> Admin RJ TEKNIK
                                        </span>
                                    </div>
                                    <div class="bg-[#8C5E3C] text-white border border-[#744A29] px-4.5 py-3 rounded-2xl rounded-tr-xs shadow-sm text-xs sm:text-sm leading-relaxed font-medium">
                                        <div>${escapeHtml(replyText).replace(/\n/g, '<br>')}</div>
                                        <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-amber-200 font-bold select-none">
                                            <span>Terkirim ke Pelanggan</span>
                                            <span class="text-xs leading-none">✓✓</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${data.log_id ? `
                            <button type="button" onclick="deleteSingleChat(${data.log_id})"
                                class="chat-delete-btn ${isSelectionMode ? 'opacity-80' : 'opacity-0'} group-hover:opacity-100 transition p-1.5 text-[#A8A29E] hover:text-rose-600 hover:bg-rose-50 rounded-xl cursor-pointer shrink-0 self-start mt-1.5"
                                title="Hapus pesan ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>` : ''}
                        </div>`;
                    
                    const emptyState = document.getElementById('chat-empty-state');
                    if (emptyState) emptyState.remove();

                    container.insertAdjacentHTML('beforeend', adminBubbleHtml);
                    scrollChatToBottom();
                    textarea.value = '';
                    textarea.style.height = '44px';
                    textarea.style.overflowY = 'hidden';

                    // Perbarui cuplikan pesan di sidebar kiri untuk pelanggan aktif
                    const activeItem = document.querySelector('.customer-item.bg-\\[\\#F3EFEA\\]');
                    if (activeItem) {
                        activeItem.setAttribute('data-pending', 'false');
                        const snippetElem = activeItem.querySelector('p');
                        if (snippetElem) {
                            snippetElem.textContent = 'Admin: ' + replyText.substring(0, 38) + (replyText.length > 38 ? '...' : '');
                        }
                        const pendingBadges = activeItem.querySelectorAll('.user-pending-badge, .animate-pulse');
                        pendingBadges.forEach(b => b.remove());
                    }

                    // Perbarui hitungan tombol tab filter "Butuh Balas (X)"
                    const remainingPendingUsers = document.querySelectorAll('.customer-item[data-pending="true"]').length;
                    const pendingFilterBtn = document.querySelector('button[onclick*="pending"]');
                    if (pendingFilterBtn) {
                        pendingFilterBtn.textContent = `Butuh Balas (${remainingPendingUsers})`;
                    }

                    // PERBARUI SEMUA STATUS CARD & BADGE SECARA REAL-TIME:
                    const pendingTotal = (data.pending_total !== undefined) ? data.pending_total : 0;
                    updatePendingKpiRealtime(pendingTotal);

                    // Header Status Badge Percakapan Pelanggan
                    const customerHeaderStatus = document.getElementById('customer-header-status-badge');
                    if (customerHeaderStatus) {
                        customerHeaderStatus.innerHTML = '';
                    }

                    // Sidebar Nav Menu Kiri
                    const navMenuPendingBadge = document.getElementById('nav-menu-chatlogs-badge');
                    if (navMenuPendingBadge) {
                        if (pendingTotal > 0) {
                            navMenuPendingBadge.textContent = `${pendingTotal} butuh balas`;
                        } else {
                            navMenuPendingBadge.className = 'text-[10px] px-2 py-0.5 rounded-full bg-white border border-[#E8E4DE] text-[#8C5E3C] font-bold';
                            const kpiTotal = document.getElementById('kpi-total-logs-count');
                            const curTotal = kpiTotal ? (parseInt(kpiTotal.textContent) || 0) + 1 : 0;
                            navMenuPendingBadge.textContent = curTotal;
                        }
                    }

                    // Total Pesan KPI Card (+1)
                    updateTotalChatCount(1);

                    showToastNotification('Balasan berhasil dikirim ke pelanggan!', 'success');
                } else {
                    // Fallback submit biasa jika AJAX return error
                    form.submit();
                }
            } catch (err) {
                console.error("AJAX error, submitting normally:", err);
                form.submit();
            } finally {
                if (btnSend) {
                    btnSend.disabled = false;
                    btnSend.innerHTML = `<span>Kirim</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>`;
                }
            }
        }

        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // 7. Toggle AI ON / OFF (Admin Takeover) secara AJAX Real-Time
        async function toggleAiStatus(userId) {
            const btn = document.getElementById('btn-ai-toggle');
            const wrapper = document.getElementById('ai-toggle-wrapper');
            const label = document.getElementById('ai-toggle-label');
            const dot = document.getElementById('ai-toggle-dot');
            const text = document.getElementById('ai-toggle-text');
            const desc = document.getElementById('ai-toggle-desc');
            const knob = document.getElementById('ai-toggle-knob');
            const banner = document.getElementById('banner-admin-takeover');
            const replyNote = document.getElementById('reply-mode-status-note');

            if (!btn) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            btn.disabled = true;
            btn.classList.add('opacity-70');

            try {
                const toggleUrl = "{{ url('/admin/chatlogs/toggle-ai') }}/" + userId;
                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken })
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    const newIsActive = Boolean(data.is_ai_enabled);

                    btn.setAttribute('data-active', newIsActive ? 'true' : 'false');

                    if (newIsActive) {
                        // AI AKTIF (ON)
                        wrapper.className = 'flex items-center gap-2.5 px-3 py-1.5 rounded-2xl border transition shadow-2xs bg-emerald-50/80 border-emerald-300';
                        label.className = 'text-[10px] font-black uppercase tracking-wider flex items-center gap-1 justify-end text-emerald-800';
                        dot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
                        text.textContent = 'AI AKTIF (ON)';
                        desc.className = 'text-[9px] text-emerald-700/80 font-medium';
                        desc.textContent = 'Otomatis balas pesan';
                        btn.className = 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none bg-emerald-500';
                        knob.className = 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out translate-x-5';
                        btn.title = 'Klik untuk mematikan AI (Mode Admin Takeover)';

                        if (banner) {
                            banner.classList.add('hidden');
                            banner.classList.remove('flex');
                        }
                        if (replyNote) {
                            replyNote.innerHTML = '';
                        }
                    } else {
                        // MODE ADMIN (OFF)
                        wrapper.className = 'flex items-center gap-2.5 px-3 py-1.5 rounded-2xl border transition shadow-2xs bg-amber-50 border-amber-300 ring-2 ring-amber-400/30';
                        label.className = 'text-[10px] font-black uppercase tracking-wider flex items-center gap-1 justify-end text-amber-900';
                        dot.className = 'w-2 h-2 rounded-full bg-amber-600';
                        text.textContent = 'MODE ADMIN (OFF)';
                        desc.className = 'text-[9px] text-amber-800 font-bold';
                        desc.textContent = 'Takeover manual admin';
                        btn.className = 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none bg-amber-500';
                        knob.className = 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out translate-x-0';
                        btn.title = 'Klik untuk mengaktifkan AI kembali (ON)';

                        if (banner) {
                            banner.classList.remove('hidden');
                            banner.classList.add('flex');
                        }
                        if (replyNote) {
                            replyNote.innerHTML = '<span class="text-amber-800 font-bold flex items-center gap-1"><span>👨‍🔧</span><span>Mode Admin Takeover: Balasan Anda terkirim langsung tanpa balasan otomatis AI.</span></span>';
                        }
                    }

                    // Perbarui badge status takeover di daftar pelanggan sidebar kiri
                    const activeItem = document.querySelector('.customer-item.bg-\\[\\#F3EFEA\\]');
                    if (activeItem) {
                        activeItem.setAttribute('data-ai', newIsActive ? 'on' : 'off');
                        let existingBadge = activeItem.querySelector('.ai-status-sidebar-badge');
                        if (!newIsActive) {
                            if (!existingBadge) {
                                const badgeContainer = activeItem.querySelector('.flex.items-center.gap-1.shrink-0');
                                if (badgeContainer) {
                                    badgeContainer.insertAdjacentHTML('afterbegin', `
                                        <span class="ai-status-sidebar-badge bg-amber-100 text-amber-900 border border-amber-300 text-[8px] font-black px-1.5 py-0.2 rounded-full shrink-0">
                                            👨‍🔧 Takeover
                                        </span>
                                    `);
                                }
                            }
                        } else {
                            if (existingBadge) {
                                existingBadge.remove();
                            }
                        }
                    }

                    showToastNotification(data.message, newIsActive ? 'success' : 'warning');
                } else {
                    alert('Gagal mengubah status AI: ' + (data.message || 'Error server'));
                }
            } catch (err) {
                console.error('Error toggling AI status:', err);
                alert('Terjadi kendala jaringan saat mengubah status AI.');
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
            }
        }

        // 8. MANAJEMEN PENGHAPUSAN CHAT (WHATSAPP WEB STYLE: DOUBLE CLICK & SELEKSI)
        let isSelectionMode = false;

        function handleChatDoubleClick(event, logId) {
            // Hindari jika klik mengenai tombol, link, atau form control
            if (event.target.closest('button') || event.target.closest('a') || event.target.closest('input')) {
                return;
            }

            // Aktifkan mode pemilihan pesan WhatsApp-style jika belum aktif
            if (!isSelectionMode) {
                enterSelectionMode();
            }

            const row = document.getElementById('chat-row-' + logId);
            if (!row) return;

            const checkbox = row.querySelector('.chat-item-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateRowSelectionStyle(row, checkbox.checked);
                handleChatCheckboxChange();
            }
        }

        function enterSelectionMode() {
            isSelectionMode = true;
            const toolbar = document.getElementById('chat-cleaning-toolbar');
            if (toolbar) {
                toolbar.classList.remove('hidden');
                toolbar.classList.add('flex');
            }

            document.querySelectorAll('.chat-checkbox-container').forEach(el => {
                el.classList.remove('hidden');
            });

            document.querySelectorAll('.chat-delete-btn').forEach(btn => {
                btn.classList.remove('opacity-0');
                btn.classList.add('opacity-80');
            });
        }

        function exitSelectionMode() {
            isSelectionMode = false;
            const toolbar = document.getElementById('chat-cleaning-toolbar');
            if (toolbar) {
                toolbar.classList.add('hidden');
                toolbar.classList.remove('flex');
            }

            document.querySelectorAll('.chat-checkbox-container').forEach(el => {
                el.classList.add('hidden');
            });

            const selectAll = document.getElementById('select-all-chats');
            if (selectAll) selectAll.checked = false;

            document.querySelectorAll('.chat-item-checkbox').forEach(cb => {
                cb.checked = false;
                const row = cb.closest('.chat-log-group');
                if (row) updateRowSelectionStyle(row, false);
            });

            document.querySelectorAll('.chat-delete-btn').forEach(btn => {
                btn.classList.add('opacity-0');
                btn.classList.remove('opacity-80');
            });

            handleChatCheckboxChange();
        }

        function updateRowSelectionStyle(row, isSelected) {
            if (isSelected) {
                row.classList.add('bg-[#8C5E3C]/10', 'ring-1', 'ring-[#8C5E3C]/30');
            } else {
                row.classList.remove('bg-[#8C5E3C]/10', 'ring-1', 'ring-[#8C5E3C]/30');
            }
        }

        function handleChatCheckboxChange() {
            const checkboxes = document.querySelectorAll('.chat-item-checkbox');
            const checked = document.querySelectorAll('.chat-item-checkbox:checked');
            const count = checked.length;

            checkboxes.forEach(cb => {
                const row = cb.closest('.chat-log-group');
                if (row) updateRowSelectionStyle(row, cb.checked);
            });

            const countLabel = document.getElementById('selected-chat-count-label');
            if (countLabel) {
                countLabel.textContent = count + ' chat dipilih';
            }

            const selectAll = document.getElementById('select-all-chats');
            if (selectAll) {
                selectAll.checked = (checkboxes.length > 0 && count === checkboxes.length);
                selectAll.indeterminate = (count > 0 && count < checkboxes.length);
            }

            const btnDeleteSelected = document.getElementById('btn-delete-selected');
            if (btnDeleteSelected) {
                if (count > 0) {
                    btnDeleteSelected.disabled = false;
                    btnDeleteSelected.className = 'px-3.5 py-1.5 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-700 text-white transition cursor-pointer flex items-center gap-1.5 shadow-sm active:scale-95';
                } else {
                    btnDeleteSelected.disabled = true;
                    btnDeleteSelected.className = 'px-3.5 py-1.5 rounded-xl font-bold text-xs bg-[#F7F5F0] text-[#A8A29E] cursor-not-allowed transition flex items-center gap-1.5 shadow-2xs';
                }
            }
        }

        function toggleSelectAllChats(masterCheckbox) {
            const isChecked = masterCheckbox.checked;
            document.querySelectorAll('.chat-item-checkbox').forEach(cb => {
                cb.checked = isChecked;
                const row = cb.closest('.chat-log-group');
                if (row) updateRowSelectionStyle(row, isChecked);
            });
            handleChatCheckboxChange();
        }

        async function deleteSingleChat(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
                return;
            }

            const row = document.getElementById('chat-row-' + id);
            if (row) {
                row.style.opacity = '0.4';
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const userId = "{{ $activeConversation['id'] ?? '' }}";

            try {
                const response = await fetch("{{ url('/admin/chatlogs/delete') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken }),
                    body: JSON.stringify({ user_id: userId, _token: csrfToken })
                });

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    if (row) {
                        row.style.transition = 'all 0.25s ease';
                        row.style.transform = 'scale(0.95)';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            checkIfChatEmpty();
                        }, 250);
                    }
                    showToastNotification(data.message || 'Pesan berhasil dihapus!', 'success');
                    handleChatCheckboxChange();
                    updateTotalChatCount(-1);
                    if (data.pending_total !== undefined) {
                        updatePendingKpiRealtime(data.pending_total);
                    }
                } else {
                    if (row) row.style.opacity = '1';
                    alert('Gagal menghapus pesan: ' + (data.message || 'Error server'));
                }
            } catch (err) {
                console.error('Delete single error:', err);
                if (row) row.style.opacity = '1';
                alert('Terjadi kesalahan jaringan saat menghapus pesan.');
            }
        }

        async function deleteSelectedChats() {
            const checked = Array.from(document.querySelectorAll('.chat-item-checkbox:checked')).map(cb => parseInt(cb.value)).filter(v => v > 0);
            if (checked.length === 0) return;

            if (!confirm(`Apakah Anda yakin ingin menghapus ${checked.length} pesan yang dipilih?`)) {
                return;
            }

            const btn = document.getElementById('btn-delete-selected');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>Menghapus...</span>';
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const userId = "{{ $activeConversation['id'] ?? '' }}";

            try {
                const response = await fetch("{{ route('admin.chatlogs.deleteBulk') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken }),
                    body: JSON.stringify({
                        ids: checked,
                        user_id: userId,
                        _token: csrfToken
                    })
                });

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    checked.forEach(id => {
                        const row = document.getElementById('chat-row-' + id);
                        if (row) row.remove();
                    });
                    showToastNotification(data.message || `${checked.length} pesan berhasil dihapus!`, 'success');
                    updateTotalChatCount(-checked.length);
                    if (data.pending_total !== undefined) {
                        updatePendingKpiRealtime(data.pending_total);
                    }
                    exitSelectionMode();
                    checkIfChatEmpty();
                } else {
                    alert('Gagal menghapus: ' + (data.message || 'Error server'));
                    handleChatCheckboxChange();
                }
            } catch (err) {
                console.error('Delete bulk error:', err);
                alert('Terjadi kendala jaringan saat menghapus pesan.');
                handleChatCheckboxChange();
            }
        }

        async function deleteAllCustomerChats() {
            const customerName = "{{ $activeConversation['name'] ?? 'pelanggan ini' }}";
            if (!confirm(`PERINGATAN: Apakah Anda yakin ingin membersihkan SELURUH riwayat pesan dengan ${customerName}? Seluruh percakapan akan dihapus permanen.`)) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const userId = "{{ $activeConversation['id'] ?? '' }}";

            try {
                const response = await fetch("{{ route('admin.chatlogs.deleteBulk') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken }),
                    body: JSON.stringify({
                        delete_all: true,
                        user_id: userId,
                        _token: csrfToken
                    })
                });

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    document.querySelectorAll('.chat-log-group').forEach(el => el.remove());
                    exitSelectionMode();
                    showToastNotification('Seluruh riwayat obrolan berhasil dibersihkan!', 'success');
                    checkIfChatEmpty();

                    // Perbarui info pelanggan di sidebar
                    const activeItem = document.querySelector('.customer-item.bg-\\[\\#F3EFEA\\]');
                    if (activeItem) {
                        const snippet = activeItem.querySelector('p');
                        if (snippet) snippet.textContent = 'Belum ada riwayat percakapan';
                        const countBadge = activeItem.querySelector('span.text-\\[10px\\]');
                        if (countBadge) countBadge.textContent = '0';
                    }

                    if (data.total_logs !== undefined) {
                        const totalEl = document.getElementById('kpi-total-logs-count');
                        if (totalEl) totalEl.textContent = data.total_logs;
                    }
                    if (data.pending_total !== undefined) {
                        updatePendingKpiRealtime(data.pending_total);
                    }
                } else {
                    alert('Gagal membersihkan riwayat: ' + (data.message || 'Error server'));
                }
            } catch (err) {
                console.error('Delete all error:', err);
                alert('Terjadi kendala jaringan saat membersihkan obrolan.');
            }
        }

        function checkIfChatEmpty() {
            const rows = document.querySelectorAll('.chat-log-group');
            if (rows.length === 0) {
                const container = document.getElementById('chat-messages-container');
                const toolbar = document.getElementById('chat-cleaning-toolbar');
                if (toolbar) toolbar.remove();
                if (container && !document.getElementById('chat-empty-state')) {
                    container.innerHTML = `
                        <div id="chat-empty-state" class="h-full flex flex-col items-center justify-center text-center p-8 text-[#78716C] space-y-3">
                            <div class="w-16 h-16 rounded-3xl bg-white border border-[#E8E4DE] flex items-center justify-center text-3xl shadow-xs">
                                💬
                            </div>
                            <h4 class="font-black text-sm text-[#18181B]">Belum Ada Riwayat Percakapan</h4>
                            <p class="text-xs max-w-xs text-[#57534E]">
                                Pelanggan ini belum mengirimkan pesan konsultasi. Admin dapat memulai pesan balasan atau menyapa langsung di bawah.
                            </p>
                        </div>`;
                }
            }
        }

        function updateTotalChatCount(delta) {
            const kpiTotal = document.getElementById('kpi-total-logs-count');
            if (kpiTotal) {
                const current = parseInt(kpiTotal.textContent) || 0;
                kpiTotal.textContent = Math.max(0, current + delta);
            }
            const activeItem = document.querySelector('.customer-item.bg-\\[\\#F3EFEA\\]');
            if (activeItem) {
                const countBadge = activeItem.querySelector('span.text-\\[10px\\]');
                if (countBadge) {
                    const cur = parseInt(countBadge.textContent) || 0;
                    countBadge.textContent = Math.max(0, cur + delta);
                }
            }
        }

        function updatePendingKpiRealtime(pendingTotal) {
            const kpiPendingCard = document.getElementById('kpi-pending-card');
            const kpiPendingCount = document.getElementById('kpi-pending-count');
            const kpiPendingDesc = document.getElementById('kpi-pending-desc');
            if (kpiPendingCount) {
                kpiPendingCount.innerHTML = `${pendingTotal} <span class="text-xs font-normal text-[#78716C]">Menunggu</span>`;
            }
            if (kpiPendingDesc) {
                kpiPendingDesc.textContent = pendingTotal > 0 ? 'Eskalasi pertanyaan kustom' : 'Semua pertanyaan tertangani';
                kpiPendingDesc.className = pendingTotal > 0 ? 'text-[11px] text-[#8C5E3C] font-semibold animate-pulse block truncate' : 'text-[11px] text-[#78716C] block truncate';
            }
            if (kpiPendingCard) {
                if (pendingTotal > 0) {
                    kpiPendingCard.className = 'bg-white border border-[#8C5E3C] ring-2 ring-[#8C5E3C]/20 rounded-3xl p-5 shadow-xs space-y-1';
                } else {
                    kpiPendingCard.className = 'bg-white border border-[#E8E4DE] rounded-3xl p-5 shadow-xs space-y-1';
                }
            }
            const titlePendingBadge = document.getElementById('title-pending-badge');
            if (titlePendingBadge) {
                if (pendingTotal > 0) {
                    titlePendingBadge.textContent = `${pendingTotal} Butuh Balasan Admin`;
                    titlePendingBadge.classList.remove('hidden');
                } else {
                    titlePendingBadge.classList.add('hidden');
                }
            }
        }

        // 8.5. Tutup Sesi Percakapan (Smart Reset untuk Layar Pelanggan)
        async function closeConversationSession(id, name) {
            if (!confirm(`Tutup sesi percakapan dengan ${name}?\n\nSetelah sesi ditutup, jika pelanggan me-refresh halaman, tampilan chat mereka akan ter-reset bersih kembali ke sapaan awal + 5 tombol cepat.`)) {
                return;
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const response = await fetch("{{ url('/admin/chatlogs/close-session') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.status === 'success') {
                    alert('Sesi percakapan berhasil ditutup.');
                    window.location.reload();
                } else {
                    alert('Gagal menutup sesi: ' + (data.message || 'Error server'));
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan saat menutup sesi.');
            }
        }

        // 9. Hapus Percakapan Pelanggan dari Sidebar (WhatsApp Style)
        async function deleteConversation(event, id, name) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (!confirm(`Hapus percakapan dengan ${name}?\n\nSeluruh riwayat chat pelanggan ini akan dibersihkan dan percakapan akan dihapus dari daftar.`)) {
                return;
            }

            const item = document.getElementById('customer-item-' + id);
            if (item) {
                item.style.opacity = '0.4';
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const isCurrentlySelected = (String(id) === "{{ (string)($activeConversation['id'] ?? '') }}");

            try {
                const response = await fetch("{{ url('/admin/chatlogs/conversation/delete') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken })
                });

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    showToastNotification(data.message || 'Percakapan berhasil dihapus!', 'success');

                    if (item) {
                        item.style.transition = 'all 0.3s ease';
                        item.style.transform = 'translateX(-20px)';
                        item.style.opacity = '0';
                        setTimeout(() => {
                            item.remove();
                            updateSidebarCustomerCount();
                        }, 300);
                    }

                    if (data.total_logs !== undefined) {
                        const totalEl = document.getElementById('kpi-total-logs-count');
                        if (totalEl) totalEl.textContent = data.total_logs;
                    }
                    if (data.pending_total !== undefined) {
                        updatePendingKpiRealtime(data.pending_total);
                    }

                    if (isCurrentlySelected) {
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.chatlogs.index') }}";
                        }, 400);
                    }
                } else {
                    if (item) item.style.opacity = '1';
                    alert('Gagal menghapus percakapan: ' + (data.message || 'Error server'));
                }
            } catch (err) {
                console.error('Delete conversation error:', err);
                if (item) item.style.opacity = '1';
                alert('Terjadi kesalahan jaringan saat menghapus percakapan.');
            }
        }

        function updateSidebarCustomerCount() {
            const count = document.querySelectorAll('.customer-item').length;
            const badge = document.getElementById('sidebar-total-accounts-badge');
            if (badge) {
                badge.textContent = count + ' Akun';
            }
            const tabAll = document.querySelector('.customer-filter-pill');
            if (tabAll) {
                tabAll.textContent = 'Semua (' + count + ')';
            }
            if (count === 0) {
                const listContainer = document.getElementById('customer-list-container');
                if (listContainer) {
                    listContainer.innerHTML = `
                        <div class="p-8 text-center text-xs text-[#78716C] space-y-2">
                            <span class="text-2xl block">💬</span>
                            <p class="font-bold">Belum ada pelanggan terdaftar.</p>
                        </div>`;
                }
            }
        }

        // 10. Toast Notifikasi Mengambang
        function showToastNotification(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const isSuccess = (type === 'success');
            toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-2xl text-xs font-bold shadow-lg transition-all transform duration-300 translate-y-3 opacity-0 ${
                isSuccess ? 'bg-emerald-800 text-white border border-emerald-700' : 'bg-amber-800 text-white border border-amber-700'
            }`;
            toast.innerHTML = `
                <span>${isSuccess ? '🤖✓' : '👨‍🔧⚠️'}</span>
                <span>${escapeHtml(message)}</span>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-3', 'opacity-0');
            });

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
    </script>
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>
</body>

</html>
