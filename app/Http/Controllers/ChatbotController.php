<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;
use App\Models\ChatLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Memastikan struktur kolom tabel chat_logs selalu lengkap & kompatibel secara dinamis
     */
    private function ensureChatLogsTableSchema(): void
    {
        try {
            if (Schema::hasTable('chat_logs')) {
                $table = 'chat_logs';

                if (!Schema::hasColumn($table, 'category')) {
                    Schema::table($table, function ($t) {
                        $t->string('category', 50)->default('in_scope_db');
                    });
                }
                if (!Schema::hasColumn($table, 'status')) {
                    Schema::table($table, function ($t) {
                        $t->string('status', 50)->default('resolved');
                    });
                }
                if (!Schema::hasColumn($table, 'admin_reply')) {
                    Schema::table($table, function ($t) {
                        $t->text('admin_reply')->nullable();
                    });
                }
                if (!Schema::hasColumn($table, 'admin_id')) {
                    Schema::table($table, function ($t) {
                        $t->unsignedBigInteger('admin_id')->nullable();
                    });
                }
                if (!Schema::hasColumn($table, 'replied_at')) {
                    Schema::table($table, function ($t) {
                        $t->timestamp('replied_at')->nullable();
                    });
                }
                if (!Schema::hasColumn($table, 'called_function')) {
                    Schema::table($table, function ($t) {
                        $t->string('called_function', 100)->nullable();
                    });
                }
                if (!Schema::hasColumn($table, 'function_payload_json')) {
                    Schema::table($table, function ($t) {
                        $t->json('function_payload_json')->nullable();
                    });
                }
                if (!Schema::hasColumn($table, 'is_out_of_scope')) {
                    Schema::table($table, function ($t) {
                        $t->boolean('is_out_of_scope')->default(false);
                    });
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Auto-schema check chat_logs warning: " . $e->getMessage());
        }
    }

    /**
     * Memproses pengiriman pesan dari antarmuka Chatbot
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $userMessage = trim($request->input('message'));

        // Self-Healing Sinkronisasi User Login:
        // Selalu pastikan user_id sesuai dengan email akun pelanggan yang sedang login di sesi
        $userEmail = session('user_email');
        if ($userEmail) {
            $matchedUser = \App\Models\User::where('email', strtolower(trim($userEmail)))->first();
            if (!$matchedUser) {
                $matchedUser = \App\Models\User::create([
                    'name'            => session('user_name', 'Pelanggan'),
                    'email'           => strtolower(trim($userEmail)),
                    'password'        => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                    'role'            => 'pelanggan',
                    'is_chat_deleted' => false,
                ]);
            }
            $userId = $matchedUser->id;
            $userName = $matchedUser->name;
            session(['user_id' => $userId, 'user_name' => $userName]);
            if (!Auth::check() || Auth::id() !== $userId) {
                Auth::login($matchedUser);
            }
        } else {
            $userId = Auth::id() ?? session('user_id');
            $userName = Auth::check() ? Auth::user()->name : session('user_name');
        }

        $chatToken = $request->input('chat_token');
        if ($userId) {
            $sessionId = 'user_' . $userId;
        } else {
            $sessionId = $chatToken ?: session('active_chat_token', session()->getId());
            if (!$sessionId) {
                $sessionId = Str::random(40);
                session()->setId($sessionId);
            }
        }

        // 1. KEBIJAKAN PENGUNJUNG TAMU (UNLIMITED TANPA BATAS KUOTA):
        // Calon pembeli yang belum login maupun sudah login mendapatkan pelayanan interaktif penuh dari AI CS.
        // Batasan kuota 2 kali ditiadakan agar pengalaman calon pembeli tidak terhambat (frictionless).
        session()->forget('guest_chat_count');

        // FITUR AI ON/OFF (ADMIN TAKEOVER):
        // Cek status AI untuk pengguna ini. Jika dinonaktifkan (OFF),
        // backend benar-benar mencegah pemrosesan & pengiriman balasan otomatis AI (Gemini API).
        $user = $userId ? \App\Models\User::find($userId) : null;
        if ($user && ($user->is_chat_deleted ?? false)) {
            $user->update(['is_chat_deleted' => false]);
        }
        $isAiActive = true;
        if ($user) {
            $isAiActive = (bool) ($user->is_ai_enabled ?? true);
        } else {
            $cleanSession = str_replace('guest_', '', $sessionId);
            $isAiActive = !\Illuminate\Support\Facades\Cache::get('guest_ai_takeover_' . $sessionId, false)
                       && !\Illuminate\Support\Facades\Cache::get('guest_ai_takeover_' . $cleanSession, false)
                       && !\Illuminate\Support\Facades\Cache::get('guest_ai_takeover_global', false);
        }

        if (!$isAiActive) {
            $finalResponse = "Halo Kak <strong>" . e($userName ?? 'Pelanggan') . "</strong>, pesan Anda telah diterima oleh Admin RJ TEKNIK. Saat ini ruang obrolan Anda sedang dalam <strong>Mode Admin (Takeover Manual)</strong>. Admin kami akan segera membaca dan membalas pesan Anda secara langsung.";
            $category = 'needs_admin';
            $status = 'pending_admin';
            $escalated = true;

            $logId = null;
            $this->ensureChatLogsTableSchema();
            try {
                $chatLog = ChatLog::create([
                    'user_id'               => $userId,
                    'session_id'            => $sessionId,
                    'user_message'          => $userMessage,
                    'called_function'       => 'ADMIN_TAKEOVER_MANUAL',
                    'function_payload_json' => json_encode(['admin_takeover' => true]),
                    'bot_response'          => $finalResponse,
                    'is_out_of_scope'       => false,
                    'category'              => $category,
                    'status'                => $status,
                ]);
                $logId = $chatLog->id;
            } catch (\Throwable $e) {
                Log::error("ChatLog insert takeover error: " . $e->getMessage());
            }

            $now = now();

            return response()->json([
                'status'         => 'success',
                'response'       => $finalResponse,
                'category'       => $category,
                'status_log'     => $status,
                'needs_login'    => false,
                'escalated'      => true,
                'ai_disabled'    => true,
                'log_id'         => $logId,
                'user_logged_in' => !empty($userId),
                'remaining'      => null,
                'limit_reached'  => false,
                'time_str'       => $now->format('H:i'),
                'date_str'       => 'Hari Ini',
            ]);
        }

        // 2. Proses query melalui ChatbotService (Gemini API / Data Grounding / Greeting)
        $result = $this->chatbotService->processQuery($userMessage);

        $finalResponse = $result['response'];
        $category = $result['category'] ?? 'in_scope_db';
        $status = 'resolved';
        $needsLogin = false;
        $escalated = false;

        $lowerMsg = strtolower(trim($userMessage));

        // 1. Deteksi panggilan langsung ke admin / CS / bantuan manusia
        $callsAdmin = str_contains($lowerMsg, 'admin') || str_contains($lowerMsg, ' min') || str_starts_with($lowerMsg, 'min') ||
                      str_contains($lowerMsg, 'cs') || str_contains($lowerMsg, 'operator') ||
                      str_contains($lowerMsg, 'bantuan') || str_contains($lowerMsg, 'tolong') ||
                      str_contains($lowerMsg, 'hubungi') || str_contains($lowerMsg, 'kontak') ||
                      str_contains($lowerMsg, 'bicara') || str_contains($lowerMsg, 'manusia');

        // 2. Deteksi permintaan kustom spesifikasi / modifikasi / nego / bengkel
        $isCustomRequest = str_contains($lowerMsg, 'custom') || str_contains($lowerMsg, 'kustom') ||
                           str_contains($lowerMsg, 'modifikasi') || str_contains($lowerMsg, 'nego') ||
                           str_contains($lowerMsg, 'tawar') || str_contains($lowerMsg, 'diskon') ||
                           str_contains($lowerMsg, 'bengkel') || str_contains($lowerMsg, 'workshop') ||
                           str_contains($lowerMsg, 'inspeksi') || str_contains($lowerMsg, 'survei') ||
                           str_contains($lowerMsg, 'survey') || str_contains($lowerMsg, 'kunjung') ||
                           str_contains($lowerMsg, 'pesan khusus') || str_contains($lowerMsg, 'borongan');

        // 3. Evaluasi Kategori & Status Chat:
        // A. Gangguan koneksi API
        if (!empty($result['is_connection_error'])) {
            $finalResponse = "Mohon tunggu, sistem dalam gangguan, admin akan segera menghubungi Anda";
            $category = 'needs_admin';
            $status = 'pending_admin';
            $escalated = true;
        }
        // B. Permintaan kustom spesifikasi / panggilan admin / eskalasi teknisi
        elseif ($callsAdmin || $isCustomRequest || $category === 'needs_admin' || ($result['needs_admin'] ?? false)) {
            $category = 'needs_admin';
            $status = 'pending_admin';
            $escalated = true;
            $needsLogin = false;
            if (empty($finalResponse) || ($result['function'] ?? '') === 'LOCAL_FILTER_NEEDS_ADMIN') {
                $salutation = !empty($userName) ? "Halo Kak <strong>" . e($userName) . "</strong>, " : "Terima kasih, ";
                $finalResponse = $salutation . "pertanyaan kustomisasi spesifikasi khusus Anda seputar mesin roasting telah berhasil dicatat ke antrean konsultasi teknisi bengkel **RJ TEKNIK**. Tim Admin kami akan segera memberikan estimasi teknis dan tanggapan langsung melalui ruang obrolan ini.";
            }
        }
        // C. Pertanyaan di luar topik / di luar lingkup (Out of scope)
        // Bot menyampaikan: 'admin kami akan segera merespons pertanyaan Anda', sehingga status dijadikan pending_admin agar Admin ternotifikasi
        elseif ($category === 'out_of_scope_irrelevant' || ($result['is_out_of_scope'] ?? false)) {
            $category = 'out_of_scope_irrelevant';
            $status = 'pending_admin';
            $escalated = true;
        }
        // D. Pertanyaan seputar katalog & mesin yang sudah dijawab tuntas oleh AI CS:
        // Status = 'resolved' (TIDAK PERLU NOTIFIKASI ADMIN, SESUAI PERMINTAAN USER)
        else {
            $status = 'resolved';
            $escalated = false;
        }

        // Jaminan keamanan: Jangan pernah biarkan respon bot tersimpan kosong/blank
        if (empty(trim((string)$finalResponse))) {
            $fallback = $this->chatbotService->processLocalFallback($userMessage);
            $finalResponse = $fallback['response'];
            $category = $fallback['category'] ?? 'in_scope_db';
            $status = ($category === 'needs_admin') ? 'pending_admin' : 'resolved';
        }

        // 4. Simpan ke database MySQL:
        // Baik pengguna terdaftar maupun pengunjung tamu dicatat agar Admin dapat memantau dan membalas konsultasi.
        $logId = null;
        $this->ensureChatLogsTableSchema();
        try {
            $chatLog = ChatLog::create([
                'user_id'               => $userId,
                'session_id'            => $sessionId,
                'user_message'          => $userMessage,
                'called_function'       => $result['function'] ?? null,
                'function_payload_json' => !empty($result['payload']) ? json_encode($result['payload']) : null,
                'bot_response'          => $finalResponse,
                'is_out_of_scope'       => ($category === 'out_of_scope_irrelevant'),
                'category'              => $category,
                'status'                => $status,
            ]);
            $logId = $chatLog->id;
        } catch (\Throwable $e) {
            Log::error("ChatLog insert fallback log: " . $e->getMessage());
        }

        $now = now();
        return response()->json([
            'status'         => 'success',
            'response'       => $finalResponse,
            'category'       => $category,
            'status_log'     => $status,
            'needs_login'    => false,
            'escalated'      => $escalated,
            'log_id'         => $logId,
            'user_logged_in' => !empty($userId),
            'remaining'      => null,
            'limit_reached'  => false,
            'time_str'       => $now->format('H:i'),
            'date_str'       => 'Hari Ini',
        ]);
    }

    /**
     * Menghasilkan contoh riwayat konsultasi awal untuk pelanggan yang sudah login
     * sesuai alur skripsi (Grounding Data, Out-of-scope, & Eskalasi Balasan Admin)
     */
    private function seedInitialCustomerLogs($userId, string $userName): void
    {
        try {
            $now = now();
            ChatLog::insert([
                [
                    'user_id'               => $userId,
                    'session_id'            => 'history-init-' . $userId,
                    'user_message'          => 'Mas, apa bedanya spesifikasi mesin kapasitas 3 Kg dan 5-10 Kg serta berapa DP-nya?',
                    'called_function'       => 'getRoastingMachineData',
                    'function_payload_json' => json_encode(['capacity_keyword' => '3', 'aspect_focus' => 'spesifikasi']),
                    'bot_response'          => "Berdasarkan data resmi basis data katalog RJ TEKNIK:\nâ€¢ **Mesin 3 Kg** (Rp 28.000.000 / DP 20% = Rp 14.000.000): Tabung plat SS 2mm (Ã˜ 25x35 cm), dinamo drum gearbox 1/2 HP + cooler 1/4 HP, cyclone separator filter.\nâ€¢ **Mesin 5-10 Kg** (Rp 48.000.000 / DP 20% = Rp 24.000.000): Tabung Ã˜ 30x50 cm tutup SS 2mm, cooling bin SS Ã˜ 60x25 cm, kontrol kecepatan drum (speed control).\n\nPemesanan dilakukan berskema Pre-Order dengan pembayaran DP 20% via Midtrans, dan Pelunasan 80% saat mesin selesai dirakit.",
                    'is_out_of_scope'       => false,
                    'category'              => 'in_scope_db',
                    'status'                => 'resolved',
                    'admin_reply'           => null,
                    'admin_id'              => null,
                    'replied_at'            => null,
                    'created_at'            => $now->copy()->subHours(2),
                    'updated_at'            => $now->copy()->subHours(2),
                ],
                [
                    'user_id'               => $userId,
                    'session_id'            => 'history-init-' . $userId,
                    'user_message'          => 'Apakah RJ TEKNIK juga menjual mesin espresso dan biji kopi arabika robusta?',
                    'called_function'       => 'LOCAL_FILTER_IRRELEVANT',
                    'function_payload_json' => json_encode(['keyword' => 'biji kopi']),
                    'bot_response'          => "Mohon maaf, saya belum dapat membantu menjawab pertanyaan tersebut karena berada di luar topik layanan RJ Teknik. Saya siap membantu memberikan informasi seputar produk dan mesin roasting kopi kami.\n\nSilakan tunggu sebentar, admin kami akan segera merespons pertanyaan Anda. Terima kasih!",
                    'is_out_of_scope'       => true,
                    'category'              => 'out_of_scope_irrelevant',
                    'status'                => 'resolved',
                    'admin_reply'           => null,
                    'admin_id'              => null,
                    'replied_at'            => null,
                    'created_at'            => $now->copy()->subHour(),
                    'updated_at'            => $now->copy()->subHour(),
                ],
                [
                    'user_id'               => $userId,
                    'session_id'            => 'history-init-' . $userId,
                    'user_message'          => 'Apakah saya bisa meminta kustomisasi ketebalan drum tabung sangrai menjadi 6 mm full cast iron dan berkunjung langsung ke bengkel RJ TEKNIK di Dawar Blandong?',
                    'called_function'       => 'LOCAL_FILTER_NEEDS_ADMIN',
                    'function_payload_json' => json_encode(['keyword' => 'kustom']),
                    'bot_response'          => "Halo Kak <strong>" . e($userName) . "</strong>, pertanyaan teknis Anda seputar mesin roasting ini telah berhasil dicatat ke antrean konsultasi Admin RJ TEKNIK. Admin kami akan segera membaca riwayat spesifikasi ini dan memberikan balasan langsung melalui ruang obrolan ini.",
                    'is_out_of_scope'       => false,
                    'category'              => 'needs_admin',
                    'status'                => 'answered_by_admin',
                    'admin_reply'           => "Halo Mas " . e($userName) . "! Untuk permintaan tabung drum 6 mm cast iron (besi cor) dapat kami kerjakan secara custom dengan penyesuaian rangka dan dinamo gearbox penggerak menjadi 1 HP. Untuk jadwal kunjungan bengkel workshop di Dawar Blandong Mojokerto kami buka hari Seninâ€“Sabtu jam 08.00â€“16.00 WIB. Silakan konfirmasi hari kedatangan Anda di ruang obrolan ini ya Mas.",
                    'admin_id'              => 1,
                    'replied_at'            => $now->copy()->subMinutes(15),
                    'created_at'            => $now->copy()->subMinutes(30),
                    'updated_at'            => $now->copy()->subMinutes(15),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::warning("Initial seed customer chat logs failed: " . $e->getMessage());
        }
    }

    /**
     * Mengambil riwayat pesan chat pelanggan (termasuk balasan manual dari Admin)
     */
    public function getHistory(Request $request)
    {
        try {
            $this->ensureChatLogsTableSchema();

            $userEmail = session('user_email');
            if ($userEmail) {
                $matchedUser = \App\Models\User::where('email', strtolower(trim($userEmail)))->first();
                if ($matchedUser) {
                    $userId = $matchedUser->id;
                    $userName = $matchedUser->name;
                    session(['user_id' => $userId, 'user_name' => $userName]);
                } else {
                    $userId = Auth::id() ?? session('user_id');
                    $userName = Auth::check() ? Auth::user()->name : session('user_name', 'Pelanggan');
                }
            } else {
                $userId = Auth::id() ?? session('user_id');
                $userName = Auth::check() ? Auth::user()->name : session('user_name', 'Pelanggan');
            }
            $chatToken = $request->query('chat_token') ?: session('active_chat_token');
            $sessionId = $userId ? ('user_' . $userId) : ($chatToken ?: session('active_chat_token', session()->getId()));

            // Cek apakah percakapan pelanggan telah dihapus oleh Admin
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user && ($user->is_chat_deleted ?? false)) {
                    return response()->json([
                        'status'               => 'success',
                        'data'                 => [],
                        'user_logged_in'       => true,
                        'user_name'            => $userName,
                        'active_admin_session' => false,
                        'remaining'            => null,
                        'limit_reached'        => false,
                    ]);
                }
            }

            // =========================================================================
            // SMART RESET UNTUK MELINDUNGI BALASAN MANUAL ADMIN:
            // 1. Jika sesi dalam status active_ai (obrolan normal dengan AI / closed / resolved):
            //    Me-refresh halaman langsung me-reset tampilan menjadi kosong (data = []),
            //    sehingga layar bersih hanya menampilkan sapaan awal + 5 tombol cepat.
            // 2. Jika sesi dalam status needs_admin, pending_admin, atau admin_handling:
            //    Ruang obrolan DIPERTAHANKAN sementara di layar pelanggan meskipun di-refresh,
            //    sampai Admin menekan "Tutup Sesi" (closed).
            // =========================================================================
            $query = ChatLog::query();
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $cleanSession = str_replace('guest_', '', $sessionId);
                $query->where(function ($q) use ($sessionId, $cleanSession) {
                    $q->where('session_id', $sessionId)
                      ->orWhere('session_id', $cleanSession)
                      ->orWhere('session_id', 'guest_' . $cleanSession);
                })->whereNull('user_id');
            }

            $hasActiveAdminEscalation = (clone $query)
                ->whereIn('status', ['pending_admin', 'needs_admin', 'admin_handling', 'answered_by_admin'])
                ->exists();

            if (!$hasActiveAdminEscalation) {
                // Sesi obrolan normal AI / sudah ditutup admin: Smart Reset aktif
                return response()->json([
                    'status'               => 'success',
                    'data'                 => [],
                    'user_logged_in'       => !empty($userId),
                    'user_name'            => $userName,
                    'active_admin_session' => false,
                    'remaining'            => null,
                    'limit_reached'        => false,
                ]);
            }

            // Sesi sedang membutuhkan atau dalam penanganan Admin: Tahan obrolan aktif di layar (abaikan riwayat sesi lama yang sudah ditutup/closed)
            $logs = $query->where('status', '!=', 'closed')->orderBy('id', 'asc')->get();

            $formattedLogs = $logs->map(function ($log) {
                $created = $log->created_at ? \Carbon\Carbon::parse($log->created_at) : now();
                $replied = $log->replied_at ? \Carbon\Carbon::parse($log->replied_at) : null;

                $dateLabel = $created->isToday() ? 'Hari Ini' : ($created->isYesterday() ? 'Kemarin' : $created->translatedFormat('d M Y'));
                $repliedDateLabel = $replied ? ($replied->isToday() ? 'Hari Ini' : ($replied->isYesterday() ? 'Kemarin' : $replied->translatedFormat('d M Y'))) : null;

                return [
                    'id'               => $log->id,
                    'user_id'          => $log->user_id,
                    'session_id'       => $log->session_id,
                    'user_message'     => $log->user_message,
                    'bot_response'     => $log->bot_response,
                    'category'         => $log->category,
                    'status'           => $log->status,
                    'admin_reply'      => $log->admin_reply,
                    'created_at'       => $created->toISOString(),
                    'time_str'         => $created->format('H:i'),
                    'date_str'         => $dateLabel,
                    'replied_at'       => $replied ? $replied->toISOString() : null,
                    'replied_time_str' => $replied ? $replied->format('H:i') : null,
                    'replied_date_str' => $repliedDateLabel,
                ];
            });

            return response()->json([
                'status'               => 'success',
                'data'                 => $formattedLogs,
                'user_logged_in'       => !empty($userId),
                'user_name'            => $userName,
                'active_admin_session' => true,
                'remaining'            => null,
                'limit_reached'        => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to retrieve chat history: " . $e->getMessage());
            return response()->json([
                'status'               => 'error',
                'message'              => 'Gagal memuat riwayat percakapan.',
                'data'                 => [],
                'user_logged_in'       => !empty($userId),
                'active_admin_session' => false,
                'remaining'            => null,
                'limit_reached'        => false,
            ], 500);
        }
    }

    /**
     * Menghapus seluruh riwayat percakapan pengguna (dari tombol ikon trash / delete di antarmuka pelanggan)
     */
    public function clearChat(Request $request)
    {
        try {
            $userEmail = session('user_email');
            if ($userEmail) {
                $matchedUser = \App\Models\User::where('email', strtolower(trim($userEmail)))->first();
                $userId = $matchedUser ? $matchedUser->id : (Auth::id() ?? session('user_id'));
            } else {
                $userId = Auth::id() ?? session('user_id');
            }

            $chatToken = $request->input('chat_token') ?: session('active_chat_token');
            $sessionId = $userId ? ('user_' . $userId) : ($chatToken ?: session('active_chat_token', session()->getId()));

            if ($userId) {
                ChatLog::where('user_id', $userId)->delete();
                $user = \App\Models\User::find($userId);
                if ($user && ($user->is_chat_deleted ?? false)) {
                    $user->update(['is_chat_deleted' => false]);
                }
            } else {
                ChatLog::where('session_id', $sessionId)->delete();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Seluruh riwayat pesan konsultasi berhasil dibersihkan.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to clear chat: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membersihkan riwayat obrolan.'
            ], 500);
        }
    }
}
