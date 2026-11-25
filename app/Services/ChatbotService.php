<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ChatbotService
{
    private ?string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->model = env('GEMINI_MODEL', 'gemini-2.5-flash');
    }

    /**
     * System instruction untuk mengarahkan Gemini AI:
     * - Melayani spesifikasi, harga, estimasi timeline, dan rekomendasi kebutuhan customer mesin roasting kopi RJ TEKNIK via tool
     * - Menjawab secara spesifik, interaktif, dan fokus pada inti pertanyaan customer
     * - Memisahkan pertanyaan yang melebar jauh / di luar konteks (out of scope irrelevant)
     * - Mendeteksi pertanyaan yang masih ranah mesin roasting tetapi butuh konsultasi manual admin
     */
    private function getSystemInstruction(): array
    {
        return [
            "parts" => [
                [
                    "text" => "Anda adalah Asisten AI CS resmi RJ TEKNIK (spesialis manufaktur mesin roasting kopi komersial berskema pre-order di Dawar Blandong, Mojokerto).

ATURAN UTAMA:
1. JANGAN PERNAH menyebutkan atau mengarahkan pengguna ke WhatsApp atau nomor telepon eksternal! Seluruh konsultasi ditangani di dalam sistem website ini.
2. JIKA pertanyaan adalah tentang spesifikasi teknis, timeline waktu pengerjaan/estimasi perakitan, harga unit, skema DP 20%, atau REKOMENDASI MESIN SESUAI KEBUTUHAN CUSTOMER (kapasitas 1 s/d 50 Kg):
   - WAJIB gunakan tool function 'getRoastingMachineData' untuk mengambil data faktual dari basis data workshop RJ TEKNIK.
   - Sajikan jawaban yang SPESIFIK, RAMAH, INTERAKTIF, dan 100% akurat sesuai data database tanpa mengarang angka:
     * Jika pelanggan menanyakan HARGA / RINCIAN HARGA (contoh: 'kasih saya rincian harga', 'harga mesin kopi disini ada apa aja?', 'Lihat Rincian Harga & DP 20%'):
       Fokus sajikan rincian harga unit dan nominal Uang Muka DP 20% untuk tiap kapasitas mesin, serta jelaskan skema pemesanan Pre-Order DP 20% via Midtrans dan Pelunasan 80% saat mesin selesai dirakit.

🔗 **Katalog Resmi:** Ingin melihat tampilan visual mesin dan rincian lengkapnya? Silakan [Buka Halaman Katalog Resmi](/katalog). JANGAN mencantumkan seluruh spesifikasi fisik panjang lebar (seperti dinamo, gearbox, burner) jika pelanggan hanya menanyakan harga.
     * Jika pelanggan menanyakan KEBUTUHAN KEDAI / PEMULA (1–3 Kg):
       Fokus bandingkan dan jelaskan Mesin 1 Kg (cocok untuk coffee shop rintisan/sample lab) dan Mesin 3 Kg (ideal untuk coffee shop aktif dengan target sangrai 15–35 kg/minggu). Sertakan harga unit, DP 20%, dan estimasi pengerjaan.
     * Jika pelanggan menanyakan KEBUTUHAN ROASTERY KOMERSIAL (5–15 Kg / 20–50 Kg):
       Fokus jelaskan Mesin 5–15 Kg (roastery berkembang/supplier beans) dan Mesin 20–25 Kg hingga custom 50 Kg (skala pabrik/industri besar). Jelaskan fitur pengatur kecepatan (speed control), motor dinamo heavy-duty, rincian harga, DP 20%, dan estimasi fabrikasi.
     * Jika pelanggan menanyakan RINCIAN ESTIMASI WAKTU PENGERJAAN:
       Fokus sajikan lama hari kerja perakitan tiap unit (1 Kg = 14 hari kerja, 3 Kg = 21 hari kerja, 5–10 Kg = 30 hari kerja, 20–25 Kg = 45 hari kerja), serta jelaskan tahapan perakitan hingga uji coba roasting sebelum pelunasan dan pengiriman.
     * Jika pelanggan menanyakan SPESIFIKASI KAPASITAS TERTENTU (misal 3 Kg atau 1 Kg):
       Fokus jelaskan unit kapasitas tersebut secara mendalam, keunggulan material drum, dinamo gearbox, burner, airflow, serta estimasi pengerjaannya.
     * Jika pelanggan menanyakan REKOMENDASI MESIN (contoh: 'mesin apa yang cocok untuk kedai kopi rintisan?', 'saran mesin untuk pemula', 'bingung pilih mesin apa'):
       Gunakan kolom 'recommendation' dari data database untuk memberikan anjuran objektif yang telah disetel oleh Admin/Teknisi RJ TEKNIK. Jelaskan alasan kecocokan, kapasitas batch ideal, estimasi timeline perakitan (hari kerja), serta rincian harga dan DP 20%.
3. JIKA pertanyaan MELEBAR JAUH / TIDAK RELEVAN / DI LUAR KONTEKS mesin roasting kopi RJ TEKNIK (contoh: kulkas, barang elektronik rumah, info ngopi/nongkrong/tempat ngopi, mesin espresso rumahan, biji kopi seduh/green bean retail, cuaca, politik, resep kuliner, coding, mobil, motor, barang atau topik lain di luar mesin roasting):
   - WAJIB sisipkan kode tag di paling awal respon: '[OUT_OF_SCOPE_IRRELEVANT]'
   - WAJIB gunakan teks balasan persis seperti berikut tanpa diubah:
Mohon maaf, saya belum dapat membantu menjawab pertanyaan tersebut karena berada di luar topik layanan RJ Teknik. Saya siap membantu memberikan informasi seputar produk dan mesin roasting kopi kami.

Silakan tunggu sebentar, admin kami akan segera merespons pertanyaan Anda. Terima kasih!
4. JIKA pertanyaan MASIH DALAM RANAH MESIN ROASTING KOPI, tetapi merupakan permintaan kustom spesifikasi khusus di luar katalog standar, modifikasi drum/burner khusus, negosiasi harga borongan, atau permohonan kunjungan langsung ke bengkel perakitan RJ TEKNIK:
   - Jawab bahwa pertanyaan teknis khusus ini memerlukan konfirmasi langsung dari Admin / Teknisi RJ TEKNIK.
   - Sisipkan kode tag di paling awal respon: '[NEEDS_ADMIN_CONSULTATION]'
5. JIKA pesan pengguna secara eksplisit meminta berbicara langsung atau dihubungi oleh Admin/Teknisi manusia ('hubungi admin', 'bicara dengan admin', 'panggil admin', 'minta nomor admin', 'cs manusia'):
   - Sampaikan dengan sopan bahwa Admin RJ TEKNIK akan segera merespons pertanyaan secara langsung.
   - WAJIB sisipkan kode tag di paling awal respon: '[NEEDS_ADMIN_CONSULTATION]'
"
                ]
            ]
        ];
    }

    private function getToolDeclarations(): array
    {
        return [
            [
                "functionDeclarations" => [
                    [
                        "name" => "getRoastingMachineData",
                        "description" => "Ambil data spesifikasi teknis lengkap, timeline estimasi hari pengerjaan, harga unit, skema DP 20%, dan rekomendasi kebutuhan pelanggan dari basis data mesin roasting kopi RJ TEKNIK (kapasitas 1 s/d 50 Kg).",
                        "parameters" => [
                            "type" => "OBJECT",
                            "properties" => [
                                "capacity_keyword" => [
                                    "type" => "STRING",
                                    "description" => "Kapasitas mesin yang dicari ('1', '3', '5-10', '20-25', angka 1-50, atau 'all')"
                                ],
                                "aspect_focus" => [
                                    "type" => "STRING",
                                    "description" => "Fokus pertanyaan ('spesifikasi', 'harga', 'estimasi', 'rekomendasi', atau 'semua')"
                                ]
                            ],
                            "required" => ["capacity_keyword"]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function executeFunctionCall(string $capacityKeyword = 'all'): array
    {
        $defaultCatalog = [
            [
                'id' => 1,
                'name' => 'Mesin Roasting Kopi Kapasitas 1 Kg',
                'capacity_kg' => 1,
                'drum_material' => 'Tabung Full SS Double Jacket (Ø 20 x 25 cm), Rangka Besi Siku',
                'gearbox_ratio' => '2 Dinamo Listrik & 2 Transmisi Gearbox Rantai',
                'motor_power_watt' => 250,
                'burner_type' => 'Kompor LPG Modifikasi Panjang Khusus Roasting',
                'airflow_system' => '1 Blower 2 Fungsi (Hisap & Buang)',
                'cooling_bin_spec' => 'Cooling Bin SS + Penampung Asap & Sampah, 4 Tombol',
                'estimation_days' => 14,
                'price' => 12000000,
                'dp_percentage' => 20,
                'description' => 'Unit mesin roasting kopi kapasitas 1 kg sangat presisi dan hemat konsumsi bahan bakar.',
                'recommendation' => 'Sangat cocok untuk kedai kopi rintisan/pemula, home roastery enthusiast, sample roasting lab, atau pengujian profil cita rasa sangrai.',
            ],
            [
                'id' => 2,
                'name' => 'Mesin Roasting Kopi Kapasitas 3 Kg',
                'capacity_kg' => 3,
                'drum_material' => 'Plat SS 2 mm (Ø 25 x 35 cm), Rangka Besi 4 Roda, Bodi SS',
                'gearbox_ratio' => 'Dinamo Drum Gearbox 1/2 HP & Cooler 1/4 HP',
                'motor_power_watt' => 450,
                'burner_type' => 'Kompor LPG Modifikasi + Selang & Regulator',
                'airflow_system' => '1 Blower 2 Fungsi + Cyclone Separator Filter',
                'cooling_bin_spec' => 'Cooling Bin SS + 4 Tombol & Termometer Drum',
                'estimation_days' => 21,
                'price' => 15000000,
                'dp_percentage' => 20,
                'description' => 'Unit mesin roasting kopi 3 kg ideal untuk produksi harian kedai kopi komersial.',
                'recommendation' => 'Rekomendasi terbaik untuk coffee shop aktif dan micro-roastery dengan target kapasitas sangrai 15–35 kg per minggu.',
            ],
            [
                'id' => 3,
                'name' => 'Mesin Roasting Kopi Kapasitas 5-10 Kg',
                'capacity_kg' => 10,
                'drum_material' => 'Tutup Depan SS 2 mm, Tabung Ø 30 x 50 cm, Rangka Besi',
                'gearbox_ratio' => 'Dinamo Tabung Gearbox 1/2 HP & Cooling 1/4 HP',
                'motor_power_watt' => 750,
                'burner_type' => 'Kompor LPG Modifikasi Khusus Sangrai',
                'airflow_system' => 'Blower Hisap Kuat & Siklon Penampung',
                'cooling_bin_spec' => 'Cooling Bin SS Ø 60 x 25 cm + 5 Tombol (+Speed Control)',
                'estimation_days' => 30,
                'price' => 16000000,
                'dp_percentage' => 20,
                'description' => 'Mesin kapasitas 5-10 kg dengan fitur lengkap untuk produksi roastery skala menengah.',
                'recommendation' => 'Pilihan utama untuk coffee shop multi-outlet, roastery komersial, atau supplier beans lokal dengan target 40–100 kg per minggu.',
            ],
            [
                'id' => 4,
                'name' => 'Mesin Roasting Kopi Kapasitas 20-25 Kg',
                'capacity_kg' => 25,
                'drum_material' => 'Full SS 4 mm Double Jacket, Corong Full SS, 4 Roda Heavy Duty',
                'gearbox_ratio' => '2 Unit Penggerak Dinamo Listrik Transmisi Gearbox',
                'motor_power_watt' => 1500,
                'burner_type' => 'Pipa Gas LPG Modifikasi + Selang Regulator Gas',
                'airflow_system' => '1 Blower 2 Fungsi & Siklon Sampah',
                'cooling_bin_spec' => 'Full SS Cooling + 5 Tombol Kontrol (+Speed Control)',
                'estimation_days' => 45,
                'price' => 85000000,
                'dp_percentage' => 20,
                'description' => 'Mesin kapasitas industri 20-25 kg untuk kontinuitas produksi skala besar.',
                'recommendation' => 'Direkomendasikan bagi pabrik kopi, distributor skala besar, dan roastery industri dengan volume produksi di atas 100 kg per minggu.',
            ]
        ];

        try {
            $query = Product::query()->where('is_active', true);
            $clean = trim(strtolower($capacityKeyword));

            if ($clean !== 'all' && $clean !== '') {
                preg_match('/(\d+)/', $clean, $numMatch);
                $num = isset($numMatch[1]) ? (int)$numMatch[1] : 0;

                if ($num === 1) {
                    $query->where('capacity_kg', 1);
                } elseif ($num === 3) {
                    $query->where('capacity_kg', 3);
                } elseif ($num === 5 || $num === 10) {
                    $query->where(function ($q) {
                        $q->where('capacity_kg', 10)->orWhere('capacity_kg', 5);
                    });
                } elseif ($num === 20 || $num === 25) {
                    $query->where(function ($q) {
                        $q->where('capacity_kg', 25)->orWhere('capacity_kg', 20);
                    });
                } elseif ($num > 0) {
                    $query->where(function ($q) use ($num) {
                        $q->where('capacity_kg', $num)
                          ->orWhere('name', 'LIKE', "%{$num}%");
                    });
                } else {
                    $query->where('name', 'LIKE', "%{$capacityKeyword}%");
                }
            }

            $columns = [
                'id', 'name', 'capacity_kg', 'drum_material', 'gearbox_ratio',
                'motor_power_watt', 'burner_type', 'airflow_system', 'cooling_bin_spec',
                'estimation_days', 'price', 'dp_percentage', 'description'
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'recommendation')) {
                $columns[] = 'recommendation';
            }

            $products = $query->get($columns);

            if ($products->isNotEmpty()) {
                return $products->map(function ($p) {
                    $arr = $p->toArray();
                    if (empty($arr['recommendation'])) {
                        $cap = (int) ($arr['capacity_kg'] ?? 1);
                        if ($cap <= 2) {
                            $arr['recommendation'] = 'Cocok untuk kebutuhan coffee shop rintisan/pemula, home roastery, dan sample roasting.';
                        } elseif ($cap <= 4) {
                            $arr['recommendation'] = 'Cocok untuk coffee shop skala menengah dengan konsumsi 15–35 kg sangrai per minggu.';
                        } elseif ($cap <= 15) {
                            $arr['recommendation'] = 'Cocok untuk roastery komersial skala berkembang dan supplier biji kopi lokal (40–100 kg/minggu).';
                        } else {
                            $arr['recommendation'] = 'Cocok untuk roastery komersial skala industri besar dan distribusi berkelanjutan (>100 kg/minggu).';
                        }
                    }
                    return $arr;
                })->toArray();
            }
        } catch (\Throwable $e) {
            // Fallback ke data katalog resmi jika database belum aktif
        }

        $clean = trim(strtolower($capacityKeyword));
        if ($clean === '1,3' || (str_contains($clean, '1') && str_contains($clean, '3'))) return [$defaultCatalog[0], $defaultCatalog[1]];
        if ($clean === '5,25' || str_contains($clean, 'komersial') || (str_contains($clean, '5') && str_contains($clean, '20'))) return [$defaultCatalog[2], $defaultCatalog[3]];
        if ($clean === '1') return [$defaultCatalog[0]];
        if ($clean === '3') return [$defaultCatalog[1]];
        if (str_contains($clean, '5') || str_contains($clean, '10')) return [$defaultCatalog[2]];
        if (str_contains($clean, '20') || str_contains($clean, '25')) return [$defaultCatalog[3]];
        return $defaultCatalog;
    }

    /**
     * Memproses pesan chat pengguna
     */
    public function processQuery(string $userMessage): array
    {
        // 1. Jika API Key Gemini tidak diset atau string dummy, kembalikan respon penanganan gangguan koneksi sistem
        if (empty($this->apiKey) || strlen($this->apiKey) < 20) {
            return [
                'response'            => "Mohon tunggu, sistem dalam gangguan, admin akan segera menghubungi Anda",
                'function'            => 'API_CONNECTION_ERROR',
                'payload'             => ['error' => 'API Key is missing or invalid'],
                'category'            => 'needs_admin',
                'is_out_of_scope'     => false,
                'needs_admin'         => true,
                'is_connection_error' => true,
            ];
        }

        $payload = [
            "systemInstruction" => $this->getSystemInstruction(),
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [["text" => $userMessage]]
                ]
            ],
            "tools" => $this->getToolDeclarations()
        ];

        $candidateModels = array_unique([
            'gemini-3-flash-preview',
            'gemini-flash-lite-latest',
            'gemini-3.1-flash-lite-preview',
            $this->model,
            'gemini-3.5-flash',
            'gemini-flash-latest'
        ]);
        $response = null;
        $url = "";

        foreach ($candidateModels as $candidateModel) {
            $currentUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$candidateModel}:generateContent?key={$this->apiKey}";
            try {
                $res = Http::timeout(25)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($currentUrl, $payload);
                if ($res->successful()) {
                    $response = $res;
                    $url = $currentUrl;
                    break;
                } elseif (in_array($res->status(), [404, 429, 503])) {
                    // 404: model deprecated, 429: quota/rate limit free tier, 503: service high demand
                    // Otomatis rotasi ke model kandidat berikutnya
                    continue;
                } else {
                    $response = $res;
                    $url = $currentUrl;
                    break;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            if (!$response || !$response->successful()) {
                Log::warning('Gemini API returned error: ' . ($response ? $response->body() : 'No response from candidate models'));
                // Fallback cerdas berbasis data katalog MySQL agar pengguna tetap terlayani secara real-time
                return $this->processLocalFallback($userMessage);
            }

            $responseData = $response->json();
            $candidates = $responseData['candidates'][0]['content']['parts'] ?? [];
            $firstPart = $candidates[0] ?? [];

            // A. Gemini memanggil Function Calling
            if (isset($firstPart['functionCall'])) {
                $funcName = $firstPart['functionCall']['name'];
                $args = $firstPart['functionCall']['args'] ?? [];

                if ($funcName === 'getRoastingMachineData') {
                    $dbData = $this->executeFunctionCall($args['capacity_keyword'] ?? 'all');

                    // Kirim kembali data MySQL ke Gemini sebagai Data Grounding
                    $payload['contents'][] = $responseData['candidates'][0]['content'];
                    $fnResp = [
                        "name" => "getRoastingMachineData",
                        "response" => ["content" => $dbData]
                    ];
                    if (!empty($firstPart['functionCall']['id'])) {
                        $fnResp['id'] = $firstPart['functionCall']['id'];
                    }

                    $payload['contents'][] = [
                        "role" => "user",
                        "parts" => [
                            [
                                "functionResponse" => $fnResp
                            ]
                        ]
                    ];

                    $groundedResponse = Http::timeout(25)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);

                    if ($groundedResponse->successful()) {
                        $groundedData = $groundedResponse->json();
                        $parts = $groundedData['candidates'][0]['content']['parts'] ?? [];
                        $finalText = '';
                        foreach ($parts as $p) {
                            if (!empty($p['text'])) {
                                $finalText .= $p['text'];
                            }
                        }
                        $finalText = trim($finalText);
                        if (!empty($finalText)) {
                            return $this->parseResponseTags($finalText, $funcName, $args);
                        } else {
                            Log::warning('Gemini Grounded response had no text parts. Using local fallback.');
                            return $this->processLocalFallback($userMessage);
                        }
                    } else {
                        Log::warning('Gemini Grounded API returned error: ' . $groundedResponse->body());
                        // Jika tahap grounding kedua mengalami gangguan kuota API, gunakan fallback lokal berbasis MySQL
                        return $this->processLocalFallback($userMessage);
                    }
                }
            }

            // B. Respon teks langsung dari Gemini
            $parts = $candidates;
            $text = '';
            foreach ($parts as $p) {
                if (!empty($p['text'])) {
                    $text .= $p['text'];
                }
            }
            $text = trim($text);
            if (!empty($text)) {
                return $this->parseResponseTags($text, null, null);
            } else {
                return $this->processLocalFallback($userMessage);
            }

        } catch (\Throwable $e) {
            Log::error('ChatbotService Exception: ' . $e->getMessage());
            return $this->processLocalFallback($userMessage);
        }
    }

    /**
     * Membedah tag klasifikasi respon dan membersihkan teks output
     */
    private function parseResponseTags(string $rawText, ?string $funcName, ?array $args): array
    {
        $rawText = trim($rawText);
        $outOfScopeExact = "Mohon maaf, saya belum dapat membantu menjawab pertanyaan tersebut karena berada di luar topik layanan RJ Teknik. Saya siap membantu memberikan informasi seputar produk dan mesin roasting kopi kami.\n\nSilakan tunggu sebentar, admin kami akan segera merespons pertanyaan Anda. Terima kasih!";

        if (str_contains($rawText, '[OUT_OF_SCOPE_IRRELEVANT]')) {
            return [
                'response'        => $outOfScopeExact,
                'function'        => $funcName,
                'payload'         => $args,
                'category'        => 'out_of_scope_irrelevant',
                'is_out_of_scope' => true,
                'needs_admin'     => false,
            ];
        }

        if (str_contains($rawText, 'di luar topik layanan RJ Teknik') || str_contains($rawText, 'di luar ranah')) {
            return [
                'response'        => $outOfScopeExact,
                'function'        => $funcName,
                'payload'         => $args,
                'category'        => 'out_of_scope_irrelevant',
                'is_out_of_scope' => true,
                'needs_admin'     => false,
            ];
        }

        if (str_contains($rawText, '[NEEDS_ADMIN_CONSULTATION]')) {
            $cleanText = str_replace('[NEEDS_ADMIN_CONSULTATION]', '', $rawText);
            return [
                'response'        => trim($cleanText),
                'function'        => $funcName,
                'payload'         => $args,
                'category'        => 'needs_admin',
                'is_out_of_scope' => false,
                'needs_admin'     => true,
            ];
        }

        return [
            'response'        => $rawText,
            'function'        => $funcName,
            'payload'         => $args,
            'category'        => 'in_scope_db',
            'is_out_of_scope' => false,
            'needs_admin'     => false,
        ];
    }

    /**
     * Mesin Fallback Cerdas Lokal (Bekerja secara offline & berdasar database MySQL)
     */
    public function processLocalFallback(string $userMessage): array
    {
        $lower = strtolower(trim($userMessage));
        $outOfScopeExact = "Mohon maaf, saya belum dapat membantu menjawab pertanyaan tersebut karena berada di luar topik layanan RJ Teknik. Saya siap membantu memberikan informasi seputar produk dan mesin roasting kopi kami.\n\nSilakan tunggu sebentar, admin kami akan segera merespons pertanyaan Anda. Terima kasih!";

        // 0. Deteksi Sapaan / Greeting
        $pureGreetings = [
            'halo', 'hai', 'hello', 'hi', 'hei', 'assalamu\'alaikum', 'assalamualaikum',
            'pagi', 'siang', 'sore', 'malam', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam',
            'permisi', 'tes', 'test', 'ping'
        ];

        $hasMachineContext = str_contains($lower, 'kg') || str_contains($lower, 'harga') ||
                             str_contains($lower, 'biaya') || str_contains($lower, 'mesin') ||
                             str_contains($lower, 'roast') || str_contains($lower, 'sangrai') ||
                             str_contains($lower, 'spek') || str_contains($lower, 'spesifikasi') ||
                             str_contains($lower, 'dp') || str_contains($lower, 'down payment') ||
                             str_contains($lower, 'custom') || str_contains($lower, 'kustom') ||
                             str_contains($lower, 'estimasi') || str_contains($lower, 'timeline') ||
                             str_contains($lower, 'rekomendasi') || str_contains($lower, 'saran');

        if (in_array($lower, $pureGreetings) || (!$hasMachineContext && preg_match('/^(halo|hai|hello|hi|assalamualaikum|selamat pagi|selamat siang|selamat sore|selamat malam)\b/i', $lower))) {
            return [
                'response'        => "Halo! Selamat datang di layanan konsultasi teknis **RJ TEKNIK** (spesialis manufaktur mesin roasting kopi komersial berskema pre-order di Dawar Blandong, Mojokerto). ☕\n\nSaya Asisten AI resmi yang terhubung langsung dengan basis data workshop. Anda dapat menanyakan:\n• **Spesifikasi Teknis:** Pilihan kapasitas 1 Kg, 3 Kg, 5–10 Kg, dan 20–25 Kg.\n• **Harga & Skema Pembayaran:** Rincian Down Payment (DP 20% via Midtrans) dan pelunasan saat mesin selesai dirakit.\n• **Estimasi Fabrikasi:** Lama hari pengerjaan mesin dari rangka hingga pengujian.\n• **Konsultasi Rancang Bangun & Kustom:** Pertanyaan khusus yang akan kami teruskan ke Admin / Teknisi RJ TEKNIK.\n\nAda tipe mesin roasting yang ingin Anda tanyakan atau bandingkan?",
                'function'        => 'GREETING_HANDLER',
                'payload'         => ['intent' => 'greeting'],
                'category'        => 'in_scope_db',
                'is_out_of_scope' => false,
                'needs_admin'     => false,
            ];
        }

        // 1.5. Deteksi Tombol Cepat / Pertanyaan Kustom Drum & Spesifikasi Khusus
        $isCustomDrumQuickButton = str_contains($lower, "kustom drum") || str_contains($lower, "spesifikasi khusus") ||
                                  (str_contains($lower, "konsultasi") && (str_contains($lower, "drum") || str_contains($lower, "kustom")));

        if ($isCustomDrumQuickButton) {
            $customDrumResponse = "Terima kasih atas minat Anda pada layanan perakitan mesin roasting kopi **RJ TEKNIK**! ☕\n\n" .
                "**1. Standar Spesifikasi Drum RJ TEKNIK:**\n" .
                "• **Material Tabung:** Tabung Stainless Steel Food Grade (SS 304 / Double Jacket dengan ketebalan 2–4 mm sesuai kapasitas) untuk konduksi dan retensi panas yang stabil, higienis, serta awet untuk sangrai harian intensif.\n" .
                "• **Penggerak & Pendingin:** Dilengkapi motor gearbox drum independen dan dinamo blower cooling bin bertenaga kuat.\n" .
                "• **Kapasitas Standar Katalog:** Tersedia 8 varian mulai dari 1 Kg, 3 Kg, 5 Kg, 10 Kg, 15 Kg, 20 Kg, 25 Kg, hingga 50 Kg.\n\n" .
                "**2. Layanan Kustom Drum & Spesifikasi Khusus:**\n" .
                "Sebagai bengkel manufaktur spesialis, RJ TEKNIK siap melayani modifikasi di luar standar katalog (seperti: kustom ketebalan plat drum, penyesuaian burner/pengapian khusus, kustom corong & airflow, penambahan panel digital/artisan, atau dimensi rangka dudukan khusus).\n\n" .
                "📌 **Eskalasi Langsung ke Admin Bengkel:**\n" .
                "Kebutuhan kustomisasi Anda telah kami catat dan langsung diteruskan ke antrean teknisi/Admin RJ TEKNIK. Silakan ketikkan detail kustom atau spesifikasi khusus yang Anda inginkan di bawah ini. Admin kami akan segera memberikan estimasi teknis dan solusi terbaik untuk usaha Anda!";

            return [
                'response'        => $customDrumResponse,
                'function'        => 'CUSTOM_DRUM_CONSULTATION',
                'payload'         => ['intent' => 'custom_drum_inquiry'],
                'category'        => 'needs_admin',
                'is_out_of_scope' => false,
                'needs_admin'     => true,
            ];
        }

        // 1.6. Deteksi Khusus Tombol Cepat 1: Kebutuhan Kedai / Pemula (1–3 Kg)
        $isKedaiPemula = (str_contains($lower, 'kedai') && str_contains($lower, 'pemula')) ||
                         (str_contains($lower, 'kedai') && (str_contains($lower, '1-3') || str_contains($lower, '1–3'))) ||
                         (str_contains($lower, 'pemula') && (str_contains($lower, '1-3') || str_contains($lower, '1–3')));

        if ($isKedaiPemula) {
            $text = "Halo! Senang sekali Anda tertarik memulai perjalanan di dunia roasting kopi bersama **RJ TEKNIK**! ☕\n\n" .
                "Untuk kebutuhan **Kedai Kopi Rintisan maupun Pemula**, kami merekomendasikan dua pilihan unit mesin roasting terfavorit:\n\n" .
                "### 1. Mesin Roasting Kopi Kapasitas 1 Kg\n" .
                "*Pilihan tepat untuk coffee shop rintisan, micro-roastery, atau sample roasting lab.*\n" .
                "• **Material Drum:** Tabung Full SS Double Jacket (Ø 20 x 25 cm), Rangka Besi Siku kokoh.\n" .
                "• **Penggerak & Pendingin:** 2 Dinamo Listrik & Transmisi Gearbox Rantai + Cooling Bin SS terintegrasi.\n" .
                "• **Harga Unit:** **Rp 12.000.000**\n" .
                "• **Uang Muka (DP 20%):** **Rp 2.400.000**\n" .
                "• **Estimasi Perakitan:** **14 Hari Kerja**\n\n" .
                "### 2. Mesin Roasting Kopi Kapasitas 3 Kg\n" .
                "*Pilihan utama kedai kopi aktif dengan target produksi rutin 15–35 kg per minggu.*\n" .
                "• **Material Drum:** Plat SS tebal 2 mm (Ø 25 x 35 cm), Rangka Besi dengan 4 roda (mudah dipindah).\n" .
                "• **Penggerak & Fitur:** Dinamo Drum Gearbox 1/2 HP + Cooler 1/4 HP + Cyclone Separator Filter & Termometer Drum.\n" .
                "• **Harga Unit:** **Rp 15.000.000**\n" .
                "• **Uang Muka (DP 20%):** **Rp 3.000.000**\n" .
                "• **Estimasi Perakitan:** **21 Hari Kerja**\n\n" .
                "---\n" .
                "💡 **Skema Pemesanan Pre-Order:**\n" .
                "1. **DP 20%:** Pembayaran awal melalui payment gateway Midtrans di website ini sebagai tanda jadi fabrikasi.\n" .
                "2. **Fabrikasi & Uji Coba:** Mesin dirakit oleh teknisi bengkel RJ TEKNIK di Mojokerto dan melalui tahap *quality control* uji sangrai.\n" .
                "3. **Pelunasan 80%:** Dilakukan setelah mesin selesai dirakit dan siap dikirim ke alamat Anda.\n\n" .
                "🔗 **Katalog Lengkap:** Ingin melihat visual fisik unit mesin? Silakan [Buka Halaman Katalog Resmi](/katalog).";

            return [
                'response'        => $text,
                'function'        => 'getRoastingMachineData',
                'payload'         => ['capacity_keyword' => '1,3', 'aspect_focus' => 'rekomendasi'],
                'category'        => 'in_scope_db',
                'is_out_of_scope' => false,
                'needs_admin'     => false,
            ];
        }

        // 1.7. Deteksi Khusus Tombol Cepat 2: Kebutuhan Roastery Komersial (5–15 Kg / 20–50 Kg)
        $isRoasteryKomersial = (str_contains($lower, 'komersial') && (str_contains($lower, 'roastery') || str_contains($lower, '5-15') || str_contains($lower, '5–15') || str_contains($lower, '20-50') || str_contains($lower, '20–50')));

        if ($isRoasteryKomersial) {
            $text = "Halo! Untuk kebutuhan **Roastery Komersial & Produksi Skala Besar**, **RJ TEKNIK** menghadirkan unit mesin berkapasitas tinggi dengan efisiensi maksimal:\n\n" .
                "### 1. Mesin Roasting Kopi Kapasitas 5–10 Kg (s/d 15 Kg)\n" .
                "*Dirancang khusus untuk supplier biji kopi lokal dan coffee shop multi-cabang (target 40–100 kg/minggu).*\n" .
                "• **Material Drum:** Tabung SS tebal 2 mm (Ø 30 x 50 cm) dengan tutup depan SS kokoh.\n" .
                "• **Fitur Unggulan:** Dilengkapi **Speed Control Drum** (pengatur kecepatan putaran), Cooling Bin SS besar (Ø 60 x 25 cm), dinamo drum 1/2 HP & cooling 1/4 HP.\n" .
                "• **Harga Unit:** **Rp 16.000.000**\n" .
                "• **Uang Muka (DP 20%):** **Rp 3.200.000**\n" .
                "• **Estimasi Perakitan:** **30 Hari Kerja**\n\n" .
                "### 2. Mesin Roasting Kopi Kapasitas 20–25 Kg (Hingga Kustom 50 Kg)\n" .
                "*Solusi industri pabrik kopi komersial dengan kontinuitas produksi massal harian.*\n" .
                "• **Material Drum:** Full SS 4 mm Double Jacket, corong pengumpan stainless, rangka heavy-duty 4 roda.\n" .
                "• **Fitur Industri:** 2 unit penggerak motor gearbox bertenaga besar (1500 Watt), panel kontrol lengkap + Speed Control, blower hisap bertenaga tinggi, burner LPG kapasitas industri.\n" .
                "• **Harga Unit:** **Rp 85.000.000**\n" .
                "• **Uang Muka (DP 20%):** **Rp 17.000.000**\n" .
                "• **Estimasi Perakitan:** **45 Hari Kerja**\n\n" .
                "---\n" .
                "💡 **Skema Pemesanan Pre-Order:**\n" .
                "Pemesanan dilakukan dengan skema DP 20% via Midtrans, pengerjaan dimulai langsung di workshop, dan Pelunasan 80% dilakukan saat mesin selesai dirakit.\n\n" .
                "🔗 **Katalog Lengkap:** Ingin melihat visual fisik mesin komersial ini? Silakan [Buka Halaman Katalog Resmi](/katalog).";

            return [
                'response'        => $text,
                'function'        => 'getRoastingMachineData',
                'payload'         => ['capacity_keyword' => '5,25', 'aspect_focus' => 'spesifikasi'],
                'category'        => 'in_scope_db',
                'is_out_of_scope' => false,
                'needs_admin'     => false,
            ];
        }

        // 1.8. Deteksi Khusus Tombol Cepat 3: Lihat Rincian Harga & DP 20%
        $isRincianHargaDP = (str_contains($lower, 'rincian harga') && str_contains($lower, 'dp')) ||
                            ($lower === 'lihat rincian harga & dp 20%') ||
                            ($lower === 'lihat rincian harga dan dp 20%');

        if ($isRincianHargaDP) {
            $text = "Berikut adalah **Daftar Rincian Harga Unit & Skema DP 20%** mesin roasting kopi komersial resmi dari workshop **RJ TEKNIK**:\n\n" .
                "### 📋 Daftar Harga Unit Mesin Roasting RJ TEKNIK\n\n" .
                "| Kapasitas Mesin | Harga Total Unit | **Uang Muka (DP 20%)** | Estimasi Pengerjaan |\n" .
                "| :--- | :--- | :--- | :--- |\n" .
                "| **Kapasitas 1 Kg** | Rp 12.000.000 | **Rp 2.400.000** | 14 Hari Kerja |\n" .
                "| **Kapasitas 3 Kg** | Rp 15.000.000 | **Rp 3.000.000** | 21 Hari Kerja |\n" .
                "| **Kapasitas 5–10 Kg** | Rp 16.000.000 | **Rp 3.200.000** | 30 Hari Kerja |\n" .
                "| **Kapasitas 20–25 Kg** | Rp 85.000.000 | **Rp 17.000.000** | 45 Hari Kerja |\n\n" .
                "---\n" .
                "💡 **Alur Pembayaran Pre-Order:**\n" .
                "• **Uang Muka (DP 20%):** Dibayarkan secara otomatis dan aman via Midtrans di website ini sebagai tanda jadi fabrikasi.\n" .
                "• **Pelunasan (80%):** Dilunasi setelah mesin selesai dirakit dan lulus uji coba *quality control* sebelum pengiriman.\n\n" .
                "🔗 **Buka Katalog:** Lihat foto unit dan spesifikasi lengkap di [Halaman Katalog Resmi](/katalog).";

            return [
                'response'        => $text,
                'function'        => 'getRoastingMachineData',
                'payload'         => ['capacity_keyword' => 'all', 'aspect_focus' => 'harga'],
                'category'        => 'in_scope_db',
                'is_out_of_scope' => false,
                'needs_admin'     => false,
            ];
        }

        // 1.9. Deteksi Khusus Tombol Cepat 4: Rincian Estimasi Waktu Pengerjaan
        $isEstimasiWaktu = (str_contains($lower, 'estimasi') && (str_contains($lower, 'waktu') || str_contains($lower, 'pengerjaan'))) ||
                           ($lower === 'rincian estimasi waktu pengerjaan');

        if ($isEstimasiWaktu) {
            $text = "Berikut adalah **Rincian Estimasi Lama Hari Kerja Perakitan (Fabrikasi)** mesin roasting kopi berskema Pre-Order di workshop **RJ TEKNIK** Dawar Blandong, Mojokerto:\n\n" .
                "• **Kapasitas 1 Kg:** Estimasi **14 Hari Kerja** *(Sangat presisi untuk kedai rintisan & sample lab)*\n" .
                "• **Kapasitas 3 Kg:** Estimasi **21 Hari Kerja** *(Pilihan utama kedai kopi aktif harian 15–35 kg/minggu)*\n" .
                "• **Kapasitas 5–10 Kg:** Estimasi **30 Hari Kerja** *(Ideal untuk roastery komersial skala berkembang)*\n" .
                "• **Kapasitas 20–25 Kg (s/d 50 Kg):** Estimasi **45 Hari Kerja** *(Skala pabrik & industri pengolahan kopi)*\n\n" .
                "---\n" .
                "⚙️ **Tahapan Proses Fabrikasi:**\n" .
                "1. Pembuatan rangka besi siku kokoh dan presisi.\n" .
                "2. Pembentukan tabung drum Stainless Steel 304 Food Grade (Double Jacket).\n" .
                "3. Instalasi motor gearbox drum, blower cooling bin, dan sistem pengapian burner LPG.\n" .
                "4. Pengujian uji sangrai (*Quality Control*) secara langsung untuk memastikan profil roasting sempurna sebelum mesin dikirim.\n\n" .
                "🔗 **Katalog Resmi:** Ingin melihat unit mesin siap pesan? Silakan [Buka Halaman Katalog Resmi](/katalog).";

            return [
                'response'        => $text,
                'function'        => 'getRoastingMachineData',
                'payload'         => ['capacity_keyword' => 'all', 'aspect_focus' => 'estimasi'],
                'category'        => 'in_scope_db',
                'is_out_of_scope' => false,
                'needs_admin'     => false,
            ];
        }

        // 1. Deteksi topik yang MELEBAR JAUH (Out-of-Scope Irrelevant)
        $irrelevantKeywords = [
            'espresso', 'mesin kopi rumahan', 'rok presso', 'flair', 'manual brew', 'v60',
            'biji kopi', 'green bean', 'roasted bean', 'roast bean', 'bubuk kopi',
            'cuaca', 'hujan', 'politik', 'presiden', 'pemilu', 'resep masakan', 'makanan', 'kuliner',
            'coding', 'laravel', 'python', 'skripsi', 'kuliah', 'kampus', 'mobil', 'motor', 'laptop', 'hp', 'game',
            'kulkas', 'ac', 'tv', 'mesin cuci', 'ngopi', 'info ngopi', 'tempat ngopi', 'cafe mana', 'kedai mana'
        ];

        foreach ($irrelevantKeywords as $badWord) {
            if (str_contains($lower, $badWord)) {
                return [
                    'response'        => $outOfScopeExact,
                    'function'        => 'LOCAL_FILTER_IRRELEVANT',
                    'payload'         => ['keyword' => $badWord],
                    'category'        => 'out_of_scope_irrelevant',
                    'is_out_of_scope' => true,
                    'needs_admin'     => false,
                ];
            }
        }

        // 2. Deteksi topik MESIN ROASTING TAPI BUTUH KONSULTASI ADMIN (Needs Admin)
        $needsAdminKeywords = [
            'hubungi admin', 'bicara admin', 'panggil admin', 'kontak admin', 'cs manusia',
            'custom', 'kustom', 'bengkel', 'workshop', 'inspeksi', 'survei', 'survey', 'kunjung', 'berkunjung', 'ketemu',
            'diskon', 'potongan', 'nego', 'tawar', 'cast iron', 'besi cor', 'modifikasi drum', 'modifikasi burner',
            'otomasi plc', 'artisan roasting', 'dawar blandong', 'kapasitas 100', 'kapasitas 200', 'pesan khusus',
            'keluhan', 'garansi', 'servis', 'service', 'sparepart khusus', 'permintaan custom'
        ];

        foreach ($needsAdminKeywords as $admWord) {
            if (str_contains($lower, $admWord)) {
                return [
                    'response'        => "Pertanyaan Anda mengenai '{$admWord}' berkaitan dengan rancang bangun atau layanan teknis khusus mesin roasting RJ TEKNIK yang memerlukan konfirmasi langsung dari Admin / Teknisi kami.",
                    'function'        => 'LOCAL_FILTER_NEEDS_ADMIN',
                    'payload'         => ['keyword' => $admWord],
                    'category'        => 'needs_admin',
                    'is_out_of_scope' => false,
                    'needs_admin'     => true,
                ];
            }
        }

        // Jika pertanyaan sama sekali tidak memiliki konteks mesin roasting kopi RJ TEKNIK, tolak secara sopan
        if (!$hasMachineContext) {
            return [
                'response'        => $outOfScopeExact,
                'function'        => 'LOCAL_FILTER_IRRELEVANT',
                'payload'         => ['unrecognized_query' => $userMessage],
                'category'        => 'out_of_scope_irrelevant',
                'is_out_of_scope' => true,
                'needs_admin'     => false,
            ];
        }

        // 3. Deteksi Permintaan Rekomendasi Mesin Sesuai Kebutuhan Customer
        $isRecommendationQuery = str_contains($lower, 'rekomendasi') || str_contains($lower, 'saran') ||
                                str_contains($lower, 'cocok') || str_contains($lower, 'pemula') ||
                                str_contains($lower, 'kedai') || str_contains($lower, 'kafe') ||
                                str_contains($lower, 'cafe') || str_contains($lower, 'bingung') ||
                                str_contains($lower, 'kebutuhan') || str_contains($lower, 'skala');

        // 4. Deteksi Pertanyaan Khusus Estimasi Waktu Pengerjaan / Fabrikasi
        $isTimelineQuery = str_contains($lower, 'estimasi') || str_contains($lower, 'waktu pengerjaan') ||
                           str_contains($lower, 'lama pengerjaan') || str_contains($lower, 'hari kerja');

        // 5. Deteksi Pertanyaan Khusus Harga / Rincian Harga
        $isPriceQuery = str_contains($lower, 'harga') || str_contains($lower, 'biaya') ||
                        str_contains($lower, 'rincian harga') || str_contains($lower, 'daftar harga') ||
                        (str_contains($lower, 'dp') && !str_contains($lower, 'spek') && !str_contains($lower, 'spesifikasi'));

        $isSpecQuery = str_contains($lower, 'spek') || str_contains($lower, 'spesifikasi') ||
                       str_contains($lower, 'drum') || str_contains($lower, 'burner') ||
                       str_contains($lower, 'dinamo') || str_contains($lower, 'cooling') ||
                       str_contains($lower, 'daya') || str_contains($lower, 'watt');

        // Deteksi Kapasitas Mesin Roasting (1 s/d 50 Kg)
        $capacity = 'all';
        if (str_contains($lower, 'pemula') || (str_contains($lower, 'kedai') && str_contains($lower, '1') && str_contains($lower, '3'))) {
            $capacity = '1,3';
        } elseif (str_contains($lower, 'komersial') || (str_contains($lower, '5') && (str_contains($lower, '20') || str_contains($lower, '50')))) {
            $capacity = '5,25';
        } elseif (preg_match('/(\d+)\s*(kg|kilo)/i', $lower, $capMatch)) {
            $capacity = $capMatch[1];
        } elseif (str_contains($lower, 'satu kg') || str_contains($lower, '1kg')) {
            $capacity = '1';
        } elseif (str_contains($lower, 'tiga kg') || str_contains($lower, '3kg')) {
            $capacity = '3';
        } elseif (str_contains($lower, '5-10') || str_contains($lower, '5-15') || str_contains($lower, '5–15') || str_contains($lower, 'lima kg') || str_contains($lower, 'sepuluh kg')) {
            $capacity = '5-10';
        } elseif (str_contains($lower, '20-25') || str_contains($lower, 'dua puluh')) {
            $capacity = '20-25';
        }

        $products = $this->executeFunctionCall($capacity);

        if ($isTimelineQuery && !$isSpecQuery) {
            $text = "Berikut rincian estimasi lama hari pengerjaan fabrikasi & perakitan mesin roasting kopi berskema Pre-Order di workshop **RJ TEKNIK**:\n\n";
            $text .= "• **Mesin Roasting Kopi Kapasitas 1 Kg:** Estimasi **14 Hari Kerja** (Sangat presisi untuk kedai rintisan & sample lab).\n";
            $text .= "• **Mesin Roasting Kopi Kapasitas 3 Kg:** Estimasi **21 Hari Kerja** (Pilihan utama kedai kopi aktif harian 15–35 kg/minggu).\n";
            $text .= "• **Mesin Roasting Kopi Kapasitas 5–10 Kg:** Estimasi **30 Hari Kerja** (Ideal untuk roastery komersial skala berkembang).\n";
            $text .= "• **Mesin Roasting Kopi Kapasitas 20–25 Kg (s/d 50 Kg):** Estimasi **45 Hari Kerja** (Skala pabrik & industri besar).\n\n";
            $text .= "Seluruh mesin dirakit dengan teliti dari rangka siku kokoh, drum stainless steel food grade, instalasi burner LPG, hingga pengujian uji sangrai (*quality control*) sebelum pelunasan dan pengiriman ke pelanggan.\n\nApakah ada kapasitas tertentu yang ingin Anda jadwalkan perakitannya?

🔗 **Katalog Resmi:** Ingin melihat tampilan visual mesin dan rincian lengkapnya? Silakan [Buka Halaman Katalog Resmi](/katalog).";
        } elseif ($isPriceQuery && !$isSpecQuery) {
            $text = "Berikut rincian harga unit dan skema pembayaran Down Payment (DP 20%) mesin roasting kopi **RJ TEKNIK**:\n\n";
            foreach ($products as $p) {
                $harga = number_format($p['price'], 0, ',', '.');
                $dp = number_format($p['price'] * ($p['dp_percentage'] / 100), 0, ',', '.');
                $text .= "• **{$p['name']}**\n";
                $text .= "  - Harga Unit: Rp {$harga}\n";
                $text .= "  - Uang Muka (DP {$p['dp_percentage']}%): Rp {$dp}\n";
                $text .= "  - Estimasi Perakitan: {$p['estimation_days']} Hari Kerja\n\n";
            }
            $text .= "Pemesanan dilakukan berskema Pre-Order dengan pembayaran DP 20% via Midtrans, dan Pelunasan 80% saat mesin selesai dirakit dan siap dikirim.\n\nApakah ada kapasitas tertentu yang ingin Anda ketahui detail spesifikasinya?

🔗 **Katalog Resmi:** Ingin melihat tampilan visual mesin dan rincian lengkapnya? Silakan [Buka Halaman Katalog Resmi](/katalog).";
        } elseif ($isRecommendationQuery) {
            $text = "Berdasarkan standar rancang bangun dan spesifikasi bengkel **RJ TEKNIK**, berikut panduan rekomendasi mesin roasting sesuai kebutuhan usaha Anda:\n\n";
            foreach ($products as $p) {
                $harga = number_format($p['price'], 0, ',', '.');
                $dp = number_format($p['price'] * ($p['dp_percentage'] / 100), 0, ',', '.');
                $rec = !empty($p['recommendation']) ? $p['recommendation'] : 'Cocok untuk kebutuhan komersial standar.';
                $text .= "• **{$p['name']}** (Estimasi: {$p['estimation_days']} Hari Kerja)\n";
                $text .= "  👉 **Rekomendasi:** {$rec}\n";
                $text .= "  👉 **Spesifikasi Kunci:** {$p['drum_material']}, {$p['gearbox_ratio']}, {$p['burner_type']}.\n";
                $text .= "  👉 **Harga Unit:** Rp {$harga} (Uang Muka DP {$p['dp_percentage']}%: Rp {$dp})\n\n";
            }
            $text .= "💡 *Tips:* Seluruh mesin diproduksi dengan skema Pre-Order oleh Tim Profesional RJ TEKNIK GRESIK di Kec. Dawar Blandong, Kab. Mojokerto, Jawa Timur.";
        } else {
            $text = "Berikut informasi resmi dari basis data mesin roasting kopi **RJ TEKNIK**:\n\n";
            foreach ($products as $p) {
                $harga = number_format($p['price'], 0, ',', '.');
                $dp = number_format($p['price'] * ($p['dp_percentage'] / 100), 0, ',', '.');
                $text .= "• **{$p['name']}**\n";
                if (!empty($p['recommendation'])) {
                    $text .= "  - Rekomendasi Kebutuhan: {$p['recommendation']}\n";
                }
                $text .= "  - Material Drum: {$p['drum_material']}\n";
                $text .= "  - Penggerak & Gearbox: {$p['gearbox_ratio']}\n";
                $text .= "  - Burner / Pengapian: {$p['burner_type']}\n";
                $text .= "  - Airflow & Cooling Bin: {$p['airflow_system']} | {$p['cooling_bin_spec']}\n";
                $text .= "  - Daya Listrik: {$p['motor_power_watt']} Watt\n";
                $text .= "  - Timeline Perakitan: {$p['estimation_days']} Hari Kerja\n";
                $text .= "  - Harga Unit: Rp {$harga} (Uang Muka DP {$p['dp_percentage']}%: Rp {$dp})\n\n";
            }
            $text .= "Pemesanan dilakukan berskema Pre-Order dengan pembayaran DP 20% via Midtrans, dan Pelunasan 80% saat mesin selesai dirakit.

🔗 **Katalog Resmi:** Ingin melihat tampilan visual mesin dan rincian lengkapnya? Silakan [Buka Halaman Katalog Resmi](/katalog).";
        }

        return [
            'response' => $text,
            'function' => 'getRoastingMachineData',
            'payload' => ['capacity_keyword' => $capacity],
            'category' => 'in_scope_db',
            'is_out_of_scope' => false,
            'needs_admin' => false,
        ];
    }
}
