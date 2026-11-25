@extends('layouts.app')

@section('title', 'AI CS - Layanan Mesin Roasting - RJ TEKNIK')

@section('content')
<div class="bg-[#F7F5F0] min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white border border-[#E8E4DE] shadow-xl rounded-3xl overflow-hidden flex flex-col h-[78vh]">

        <!-- Header Chatbot Nuansa Hitam & Coklat -->
        <div class="bg-[#18181B] text-white px-6 py-4 flex items-center justify-between z-10 border-b border-[#2C2D30]">
            <div class="flex items-center gap-4">
                <img src="{{ asset('img/logo.png') }}" alt="Logo RJ TEKNIK" class="w-11 h-11 object-contain rounded-2xl shadow-sm bg-black">
                <div>
                    <h1 class="font-extrabold text-base sm:text-lg tracking-wide text-white">Asisten AI Customer Service</h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="text-xs text-[#A8A29E] font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                            Online
                        </p>
                        <span class="text-[#57534E] text-xs">•</span>
                        <div class="flex items-center gap-1.5 text-xs text-[#E8E4DE] bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10 shadow-2xs">
                            <span class="text-[#C5A070] text-[10px]">🕒</span>
                            <span id="chatbot-live-clock" class="font-mono font-bold tracking-wider">--:--:-- WIB</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Akun Pelanggan & Tombol Aksi -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Tombol Hapus / Bersihkan Chat (Ikon Trash) -->
                <button type="button" onclick="confirmClearChat()"
                    class="bg-white/10 hover:bg-rose-500/20 hover:text-rose-400 hover:border-rose-500/40 text-[#E8E4DE] px-3 py-1.5 rounded-full border border-white/20 flex items-center gap-1.5 text-xs font-semibold transition cursor-pointer shadow-xs"
                    title="Hapus / Bersihkan Seluruh Pesan Obrolan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="hidden sm:inline">Hapus Chat</span>
                </button>

                @if(session('user_name') || Auth::check())
                <span class="text-xs bg-white/10 text-white border border-white/20 px-3.5 py-1.5 rounded-full flex items-center gap-1.5">
                    <span class="text-[#C5A070]">👤</span>
                    <strong class="text-white max-w-[140px] truncate">{{ session('user_name', Auth::user()->name ?? 'User') }}</strong>
                </span>
                @else
                <a href="{{ route('login') }}" class="text-xs bg-[#8C5E3C] hover:bg-[#744A29] text-white font-bold px-4 py-1.5 rounded-full transition shadow-xs">
                    Login Pengguna
                </a>
                @endif
            </div>
        </div>

        <!-- Ruang Obrolan (Chat Box) -->
        <div id="chat-box" class="flex-1 overflow-y-auto p-6 space-y-5 bg-[#F7F5F0] scroll-smooth">
            
            <!-- Pesan Pembuka AI CS Proaktif (Sesuai Arahan Revisi Dosen) -->
            <div class="flex items-start gap-3 sm:gap-4 max-w-[96%] sm:max-w-[90%]">
                <!-- Avatar Maskot 3D Animasi Bergerak -->
                <div class="shrink-0 w-14 sm:w-16 hidden sm:block self-start pt-1">
                    <img src="{{ asset('img/robot-mascot.png') }}?v=2" 
                         alt="Maskot AI CS RJ TEKNIK" 
                         class="w-full h-auto object-contain mascot-greeting-anim drop-shadow-sm">
                </div>
                <div class="flex-1 text-[#18181B] bg-white border border-[#E8E4DE] shadow-xs px-5 pt-4 pb-3.5 rounded-2xl rounded-tl-none leading-relaxed text-sm">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#8C5E3C] mb-1">
                        <span class="w-2 h-2 rounded-full bg-[#8C5E3C]"></span>
                        <span>Asisten AI CS RJ TEKNIK (Official)</span>
                    </div>
                    <p class="font-black text-[#8C5E3C] text-sm sm:text-base">
                        Halo, Sobat Kopi......
                    </p>
                    <p class="mt-1 text-xs font-semibold text-[#18181B]">
                        Senang bertemu dengan Anda 🤗
                    </p>
                    <p class="mt-2 text-xs sm:text-sm text-[#3F3F46] leading-relaxed">
                        Sedang mencari Mesin Roasting Kopi untuk usaha Anda? Yuk, konsultasikan kebutuhan mesin roasting Anda bersama AI CS RJ Teknik. Kami siap membantu menemukan pilihan mesin yang tepat untuk bisnis kopi Anda! 🤝
                    </p>

                    <!-- Pilihan Tombol Cepat Kotak-Kotak (Tanpa Ikon) -->
                    <div class="mt-4 pt-3.5 border-t border-[#E8E4DE]">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#78716C] block mb-2.5">
                            Pilihan Tombol Cepat:
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button type="button" onclick="sendQuickReply('Kebutuhan Kedai / Pemula (1–3 Kg)')" class="text-left bg-[#F7F5F0] hover:bg-[#EAE4DC] hover:border-[#8C5E3C] text-[#18181B] text-xs font-semibold px-3.5 py-2.5 rounded-lg border border-[#E8E4DE] transition cursor-pointer shadow-2xs">
                                Kebutuhan Kedai / Pemula (1–3 Kg)
                            </button>
                            <button type="button" onclick="sendQuickReply('Kebutuhan Roastery Komersial (5–15 Kg / 20–50 Kg)')" class="text-left bg-[#F7F5F0] hover:bg-[#EAE4DC] hover:border-[#8C5E3C] text-[#18181B] text-xs font-semibold px-3.5 py-2.5 rounded-lg border border-[#E8E4DE] transition cursor-pointer shadow-2xs">
                                Kebutuhan Roastery Komersial (5–15 Kg / 20–50 Kg)
                            </button>
                            <button type="button" onclick="sendQuickReply('Lihat Rincian Harga & DP 20%')" class="text-left bg-[#F7F5F0] hover:bg-[#EAE4DC] hover:border-[#8C5E3C] text-[#18181B] text-xs font-semibold px-3.5 py-2.5 rounded-lg border border-[#E8E4DE] transition cursor-pointer shadow-2xs">
                                Lihat Rincian Harga & DP 20%
                            </button>
                            <button type="button" onclick="sendQuickReply('Rincian Estimasi Waktu Pengerjaan')" class="text-left bg-[#F7F5F0] hover:bg-[#EAE4DC] hover:border-[#8C5E3C] text-[#18181B] text-xs font-semibold px-3.5 py-2.5 rounded-lg border border-[#E8E4DE] transition cursor-pointer shadow-2xs">
                                Rincian Estimasi Waktu Pengerjaan
                            </button>
                            <button type="button" onclick="sendQuickReply('Konsultasi Kustom Drum / Spesifikasi Khusus')" class="text-left sm:col-span-2 bg-[#F7F5F0] hover:bg-[#EAE4DC] hover:border-[#8C5E3C] text-[#18181B] text-xs font-semibold px-3.5 py-2.5 rounded-lg border border-[#E8E4DE] transition cursor-pointer shadow-2xs">
                                Konsultasi Kustom Drum / Spesifikasi Khusus
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-2.5 pt-2 border-t border-[#E8E4DE] text-[10px] text-[#A8A29E] font-medium select-none">
                        <span>Asisten Resmi Workshop RJ TEKNIK</span>
                        <span id="greeting-live-time" class="font-bold text-[#8C5E3C]"></span>
                    </div>
                </div>
            </div>

            

            <!-- Placeholder untuk riwayat chat yang dimuat otomatis -->
            <div id="history-container" class="space-y-4"></div>

        </div>

        <!-- Area Input Pesan & Pilihan Tombol Cepat Menempel di Bawah (Sesuai Gaya Admin) -->
        <div class="border-t border-[#E8E4DE] p-3 sm:p-4 bg-white shrink-0 space-y-2.5">
            <!-- Pilihan Cepat Chips Bar (Docked di Atas Input agar User Tidak Perlu Scroll ke Atas) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                <button type="button" onclick="sendQuickReply('Kebutuhan Kedai / Pemula (1–3 Kg)')"
                    class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] hover:border-[#8C5E3C] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-3 py-1.5 rounded-lg transition text-xs font-semibold cursor-pointer shadow-2xs whitespace-nowrap">
                    ☕ Kedai / Pemula (1–3 Kg)
                </button>
                <button type="button" onclick="sendQuickReply('Kebutuhan Roastery Komersial (5–15 Kg / 20–50 Kg)')"
                    class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] hover:border-[#8C5E3C] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-3 py-1.5 rounded-lg transition text-xs font-semibold cursor-pointer shadow-2xs whitespace-nowrap">
                    🏭 Roastery Komersial (5–15 Kg / 20–50 Kg)
                </button>
                <button type="button" onclick="sendQuickReply('Lihat Rincian Harga & DP 20%')"
                    class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] hover:border-[#8C5E3C] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-3 py-1.5 rounded-lg transition text-xs font-semibold cursor-pointer shadow-2xs whitespace-nowrap">
                    💰 Rincian Harga & DP 20%
                </button>
                <button type="button" onclick="sendQuickReply('Rincian Estimasi Waktu Pengerjaan')"
                    class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] hover:border-[#8C5E3C] border border-[#E8E4DE] text-[#57534E] hover:text-[#18181B] px-3 py-1.5 rounded-lg transition text-xs font-semibold cursor-pointer shadow-2xs whitespace-nowrap">
                    ⏱️ Estimasi Waktu Pengerjaan
                </button>
                <button type="button" onclick="sendQuickReply('Konsultasi Kustom Drum / Spesifikasi Khusus')"
                    class="shrink-0 bg-[#FAF9F5] hover:bg-[#F3EFEA] hover:border-[#8C5E3C] border border-[#8C5E3C]/40 text-[#8C5E3C] hover:text-[#744A29] px-3 py-1.5 rounded-lg transition text-xs font-bold cursor-pointer shadow-2xs whitespace-nowrap">
                    🛠️ Kustom Drum / Spek Khusus
                </button>
            </div>

            <div class="flex items-end gap-2.5 max-w-4xl mx-auto">
                <textarea id="chat-input" rows="1"
                    placeholder="Tanya spesifikasi mesin, estimasi pengerjaan, atau request kustom... (Shift+Enter untuk baris baru)"
                    oninput="autoResizeTextarea(this)"
                    onkeydown="handleChatKeydown(event)"
                    class="flex-grow bg-[#F7F5F0] border border-[#E8E4DE] rounded-2xl px-5 py-3 text-sm text-[#18181B] placeholder-[#57534E]/60 outline-none focus:border-[#8C5E3C] focus:ring-2 focus:ring-[#8C5E3C]/20 transition resize-none leading-relaxed overflow-y-hidden"
                    style="min-height: 48px; max-height: 160px;"></textarea>
                <button onclick="sendChatMessage()" id="btn-send-chat"
                    class="bg-[#8C5E3C] hover:bg-[#744A29] text-white px-5 sm:px-6 h-12 rounded-2xl text-sm font-extrabold cursor-pointer transition shadow-md flex items-center justify-center gap-2 border border-transparent shrink-0 self-end">
                    <span>Kirim</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const inputField = document.getElementById('chat-input');
    const sendButton = document.getElementById('btn-send-chat');
    const historyContainer = document.getElementById('history-container');
    const isLoggedIn = {{ $isLoggedIn ? 'true' : 'false' }};
    const chatToken = '{{ $chatToken ?? "" }}';

    function formatMessageText(text) {
        if (!text) return '';
        let formatted = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="text-[#8C5E3C] hover:text-[#744A29] font-black underline transition inline-flex items-center gap-1">$1 ↗</a>')
            .replace(/\n/g, '<br>');
        return formatted;
    }

    function getFormattedTime(dateString) {
        if (!dateString) {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            return `${h}:${m}`;
        }
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return '';
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }

    function getFormattedDate(dateString) {
        if (!dateString) return 'Hari Ini';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return 'Hari Ini';

        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);

        if (d.toDateString() === today.toDateString()) return 'Hari Ini';
        if (d.toDateString() === yesterday.toDateString()) return 'Kemarin';

        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function updateLiveClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const clockElem = document.getElementById('chatbot-live-clock');
        if (clockElem) {
            clockElem.textContent = `${h}:${m}:${s} WIB`;
        }
        const greetingElem = document.getElementById('greeting-live-time');
        if (greetingElem && !greetingElem.dataset.set) {
            greetingElem.textContent = `Aktif • ${h}:${m} WIB`;
            greetingElem.dataset.set = "true";
        }
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    

    

    let lastHistorySignature = '';

    async function loadChatHistory(force = false) {
        try {
            const url = '{{ route("chatbot.history") }}' + (chatToken ? `?chat_token=${encodeURIComponent(chatToken)}` : '');
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success') {
                if (data.data && data.data.length > 0) {
                    const signature = data.data.map(d => `${d.id}_${d.status}_${d.admin_reply ? d.admin_reply.length : 0}`).join('|');
                    if (!force && signature === lastHistorySignature) {
                        return;
                    }
                    lastHistorySignature = signature;

                    let lastDateStr = '';
                    historyContainer.innerHTML = '';
                    data.data.forEach(item => {
                        const itemDate = item.date_str || getFormattedDate(item.created_at);
                        if (itemDate && itemDate !== lastDateStr) {
                            historyContainer.innerHTML += `
                                <div class="flex justify-center my-3.5">
                                    <span class="bg-white text-[#57534E] border border-[#E8E4DE] text-[11px] font-bold px-4 py-1 rounded-full shadow-2xs tracking-wide">
                                        ${itemDate}
                                    </span>
                                </div>`;
                            lastDateStr = itemDate;
                        }
                        renderHistoryItem(item);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                } else {
                    // Ketika riwayat kosong atau telah dihapus oleh Admin
                    if (lastHistorySignature !== '') {
                        historyContainer.innerHTML = '';
                        lastHistorySignature = '';
                    }
                }
            }
        } catch (e) {
            console.log("Could not load chat history:", e);
        }
    }

    function renderHistoryItem(item) {
        const timeStr = item.time_str || getFormattedTime(item.created_at);

        // 1. Pesan User
        const userHtml = `
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-bold text-[#57534E] mr-2 mb-1">Anda</span>
                <div class="text-white bg-[#8C5E3C] shadow-sm px-4 pt-3 pb-2 rounded-2xl rounded-tr-none max-w-[85%] text-sm leading-relaxed border border-[#8C5E3C]">
                    <div class="whitespace-pre-wrap break-words">${item.user_message}</div>
                    <div class="flex items-center justify-end gap-1.5 mt-1.5 text-[10px] text-white/90 font-medium select-none">
                        <span>${timeStr}</span>
                        <span class="text-[11px] font-bold leading-none">✓✓</span>
                    </div>
                </div>
            </div>`;
        historyContainer.innerHTML += userHtml;

        // 2. Respon AI
        let botBadge = `<span class="text-[10px] font-bold text-[#57534E] ml-2 mb-1">Asisten AI CS RJ TEKNIK</span>`;
        let botCardClass = "text-[#18181B] bg-white border border-[#E8E4DE]";

        if (item.category === 'out_of_scope_irrelevant') {
            botBadge = `<span class="text-[10px] font-bold text-amber-700 ml-2 mb-1">⚠️  Peringatan Ranah Layanan</span>`;
            botCardClass = "text-amber-900 bg-amber-50 border border-amber-200";
        } else if (item.category === 'needs_admin' || item.category === 'needs_login') {
            botBadge = `<span class="text-[10px] font-bold text-[#8C5E3C] ml-2 mb-1">ℹ️  Eskalasi Konsultasi Teknis</span>`;
            botCardClass = "text-[#18181B] bg-[#F3EFEA] border border-[#8C5E3C]/30";
        }

        const botContent = (item.bot_response && item.bot_response.trim() !== '') 
            ? formatMessageText(item.bot_response) 
            : '<em>Respon konsultasi mesin sedang disiapkan...</em>';

        const botHtml = `
            <div class="flex flex-col items-start">
                ${botBadge}
                <div class="${botCardClass} shadow-xs px-5 pt-3.5 pb-2 rounded-2xl rounded-tl-none max-w-[88%] text-sm leading-relaxed">
                    <div>${botContent}</div>
                    <div class="flex items-center justify-end mt-1.5 text-[10px] text-[#A8A29E] font-medium select-none">
                        <span>${timeStr}</span>
                    </div>
                </div>
            </div>`;
        historyContainer.innerHTML += botHtml;

        // 3. Jika Admin sudah membalas secara manual dari Dashboard
        if (item.admin_reply) {
            const replyTimeStr = item.replied_time_str || getFormattedTime(item.replied_at || item.created_at);
            const replyDateStr = item.replied_date_str || item.date_str || 'Hari Ini';

            const adminHtml = `
                <div class="flex flex-col items-start ml-2 sm:ml-4 border-l-4 border-[#8C5E3C] pl-3 py-1 my-2">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-[#8C5E3C] text-white text-[9px] px-2.5 py-0.5 rounded-full font-black uppercase tracking-wider shadow-2xs">Balasan Resmi Admin RJ TEKNIK</span>
                        <span class="text-[10px] text-[#57534E] font-bold">
                            ${replyDateStr} • ${replyTimeStr}
                        </span>
                    </div>
                    <div class="text-[#18181B] bg-[#F3EFEA] border border-[#E8E4DE] shadow-xs px-5 pt-3.5 pb-2 rounded-2xl text-sm leading-relaxed max-w-[92%] font-medium">
                        <div>${formatMessageText(item.admin_reply)}</div>
                        <div class="flex items-center justify-end gap-1.5 mt-1.5 text-[10px] text-[#8C5E3C] font-semibold select-none">
                            <span>Dibalas ${replyTimeStr}</span>
                            <span class="text-[11px] font-bold leading-none">✓✓</span>
                        </div>
                    </div>
                </div>`;
            historyContainer.innerHTML += adminHtml;
        }
    }

    function playChatbotMessageSound() {
        try {
            const audio = document.getElementById('chatbot-msg-audio');
            if (audio) {
                audio.currentTime = 0;
                audio.volume = 0.6;
                audio.play().catch(() => {});
            }
        } catch(e) {}
    }

    function renderBotResponseOnly(item) {
        playChatbotMessageSound();
        const timeStr = item.time_str || getFormattedTime(item.created_at);

        let botBadge = `<span class="text-[10px] font-bold text-[#57534E] ml-2 mb-1">Asisten AI CS RJ TEKNIK</span>`;
        let botCardClass = "text-[#18181B] bg-white border border-[#E8E4DE]";

        if (item.category === 'out_of_scope_irrelevant') {
            botBadge = `<span class="text-[10px] font-bold text-amber-700 ml-2 mb-1">⚠️  Peringatan Ranah Layanan</span>`;
            botCardClass = "text-amber-900 bg-amber-50 border border-amber-200";
        } else if (item.category === 'needs_admin' || item.category === 'needs_login') {
            botBadge = `<span class="text-[10px] font-bold text-[#8C5E3C] ml-2 mb-1">ℹ️  Eskalasi Konsultasi Teknis</span>`;
            botCardClass = "text-[#18181B] bg-[#F3EFEA] border border-[#8C5E3C]/30";
        }

        const botContent = (item.bot_response && item.bot_response.trim() !== '') 
            ? formatMessageText(item.bot_response) 
            : '<em>Respon konsultasi mesin sedang disiapkan...</em>';

        const botHtml = `
            <div class="flex flex-col items-start animate-fade-in">
                ${botBadge}
                <div class="${botCardClass} shadow-xs px-5 pt-3.5 pb-2 rounded-2xl rounded-tl-none max-w-[88%] text-sm leading-relaxed">
                    <div>${botContent}</div>
                    <div class="flex items-center justify-end mt-1.5 text-[10px] text-[#A8A29E] font-medium select-none">
                        <span>${timeStr}</span>
                    </div>
                </div>
            </div>`;
        historyContainer.innerHTML += botHtml;
    }

    function sendQuickReply(text) {
        if (inputField) {
            inputField.value = text;
            sendChatMessage();
        }
    }

    async function sendChatMessage() {
        const message = inputField.value.trim();
        if (!message) return;

        const currentTimeStr = getFormattedTime();
        const userTempId = 'temp-msg-' + Date.now();

        const userTempHtml = `
            <div id="${userTempId}" class="flex flex-col items-end">
                <span class="text-[10px] font-bold text-[#57534E] mr-2 mb-1">Anda</span>
                <div class="text-white bg-[#8C5E3C] shadow-sm px-4 pt-3 pb-2 rounded-2xl rounded-tr-none max-w-[85%] text-sm leading-relaxed border border-[#8C5E3C]">
                    <div class="whitespace-pre-wrap break-words">${message}</div>
                    <div class="flex items-center justify-end gap-1.5 mt-1.5 text-[10px] text-white/90 font-medium select-none">
                        <span>${currentTimeStr}</span>
                        <span class="check-status text-[11px] font-bold leading-none">✓</span>
                    </div>
                </div>
            </div>`;
        historyContainer.innerHTML += userTempHtml;

        inputField.value = '';
        inputField.style.height = '48px';
        inputField.style.overflowY = 'hidden';
        chatBox.scrollTop = chatBox.scrollHeight;

        const loadingId = 'loading-' + Date.now();
        const loadingHtml = `
            <div id="${loadingId}" class="flex flex-col items-start animate-pulse">
                <span class="text-[10px] font-bold text-[#57534E] ml-2 mb-1">Asisten AI CS Sedang Mengetik...</span>
                <div class="bg-white border border-[#E8E4DE] px-4 py-3 rounded-2xl rounded-tl-none shadow-xs text-xs text-[#57534E]">
                    Sedang mencari rincian data mesin di sistem...
                </div>
            </div>`;
        historyContainer.innerHTML += loadingHtml;
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            const response = await fetch('{{ route("chatbot.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: message,
                    chat_token: chatToken
                })
            });

            const data = await response.json();
            const loadingElem = document.getElementById(loadingId);
            if (loadingElem) loadingElem.remove();



            if (data.status === 'success') {
                // Tandai pesan user berhasil terkirim ke server (double check)
                const userMsgElem = document.getElementById(userTempId);
                if (userMsgElem) {
                    const checkSpan = userMsgElem.querySelector('.check-status');
                    if (checkSpan) checkSpan.textContent = '✓✓';
                }

                // Render HANYA balasan bot, tidak menduplikasi balon chat user
                if (data.response) {
                    renderBotResponseOnly({
                        id: data.log_id || Date.now(),
                        bot_response: data.response,
                        category: data.category || 'in_scope_db',
                        created_at: new Date().toISOString(),
                        time_str: data.time_str || currentTimeStr
                    });
                }

                chatBox.scrollTop = chatBox.scrollHeight;
            } else {
                historyContainer.innerHTML += `
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-2xl text-xs">
                        ${data.message || 'Gagal memproses pesan.'}
                    </div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        } catch (error) {
            const loadingElem = document.getElementById(loadingId);
            if (loadingElem) loadingElem.remove();

            historyContainer.innerHTML += `
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-2xl text-xs">
                    Terjadi kendala jaringan saat menghubungkan ke asisten AI CS.
                </div>`;
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    async function confirmClearChat() {
        if (!confirm("Apakah Anda yakin ingin menghapus seluruh pesan percakapan ini? Ruang obrolan akan kembali bersih seperti semula.")) {
            return;
        }

        try {
            const response = await fetch('{{ route("chatbot.clear") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ chat_token: chatToken })
            });
            const data = await response.json();
            if (data.status === 'success') {
                historyContainer.innerHTML = '';
                lastHistorySignature = '';
                chatBox.scrollTop = 0;
            } else {
                alert(data.message || 'Gagal menghapus pesan.');
            }
        } catch (e) {
            console.error('Gagal menghapus riwayat chat:', e);
            alert('Terjadi kendala saat menghapus pesan obrolan.');
        }
    }

    function autoResizeTextarea(textarea) {
        if (!textarea) return;
        textarea.style.height = 'auto';
        const maxHeight = 160;
        const newHeight = Math.min(textarea.scrollHeight, maxHeight);
        textarea.style.height = (newHeight < 48 ? 48 : newHeight) + 'px';
        if (textarea.scrollHeight > maxHeight) {
            textarea.style.overflowY = 'auto';
        } else {
            textarea.style.overflowY = 'hidden';
        }
    }

    function handleChatKeydown(e) {
        autoResizeTextarea(e.target);

        if (e.key === 'Enter') {
            // Shift + Enter: selalu membuat baris/paragraf baru di semua perangkat
            if (e.shiftKey) {
                return;
            }

            // Cek apakah di perangkat smartphone / Android / layar sentuh
            const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || (window.innerWidth < 768);

            if (isMobile) {
                // Di Android: tombol Enter di keyboard membuat baris baru (paragraf)
                // Pengiriman dilakukan dengan menekan tombol Kirim di samping
                return;
            } else {
                // Di Laptop/Desktop: Enter langsung mengirim pesan
                e.preventDefault();
                sendChatMessage();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadChatHistory(true);
        setInterval(() => loadChatHistory(false), 5000);
    });
</script>

<!-- ELEMEN AUDIO PESAN MASUK CHATBOT -->
<audio id="chatbot-msg-audio" preload="auto">
    <source src="{{ asset('sounds/message.wav') }}" type="audio/wav">
</audio>
@endsection
