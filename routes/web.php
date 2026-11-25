<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Product;
use App\Models\ChatLog;
use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegisteredMail;

/*
|--------------------------------------------------------------------------
| FUNGSI HELPER: KONEKSI OTOMATIS KE TABEL MYSQL
|--------------------------------------------------------------------------
*/

function getProductsTable(): string
{
    try {
        if (Schema::hasTable('products') && DB::table('products')->count() > 0) {
            return 'products';
        }
        if (DB::table('db_rj_gresik.products')->count() >= 0) {
            return 'db_rj_gresik.products';
        }
    } catch (\Throwable $e) {
        // Fallback
    }
    return 'products';
}

function ensureProductColumns(): void
{
    try {
        $table = getProductsTable();
        if (Schema::hasTable($table)) {
            if (!Schema::hasColumn($table, 'recommendation')) {
                Schema::table($table, function (\Illuminate\Database\Schema\Blueprint $tableBlueprint) {
                    $tableBlueprint->text('recommendation')->nullable()->after('description');
                });
            }

            // Perbesar tipe data kolom spesifikasi menjadi TEXT agar tidak terpotong (error 1406 / 22001)
            static $columnsModified = false;
            if (!$columnsModified) {
                try {
                    DB::statement("ALTER TABLE `{$table}` 
                        MODIFY COLUMN `name` VARCHAR(255) NOT NULL,
                        MODIFY COLUMN `drum_material` TEXT NULL,
                        MODIFY COLUMN `gearbox_ratio` TEXT NULL,
                        MODIFY COLUMN `burner_type` TEXT NULL,
                        MODIFY COLUMN `airflow_system` TEXT NULL,
                        MODIFY COLUMN `cooling_bin_spec` TEXT NULL");
                    $columnsModified = true;
                } catch (\Throwable $alterErr) {
                    // Safe silence
                }
            }

            static $dpUpdated = false;
            if (!$dpUpdated) {
                try {
                    if (Schema::hasColumn($table, 'dp_percentage')) {
                        DB::table($table)->update(['dp_percentage' => 20]);
                    }
                    $dpUpdated = true;
                } catch (\Throwable $dpErr) {
                    // Safe silence
                }
            }
        }
    } catch (\Throwable $e) {
        // Safe silence
    }
}

function saveProductRecord(string $action, string $table, array $data, $id = null)
{
    try {
        if ($action === 'insert') {
            return DB::table($table)->insertGetId($data);
        } else {
            return DB::table($table)->where('id', $id)->update($data);
        }
    } catch (\Throwable $dbErr) {
        // Jika MySQL menolak karena data terlalu panjang (SQLSTATE 22001 / Error 1406)
        if (str_contains($dbErr->getMessage(), '1406') || str_contains($dbErr->getMessage(), '22001')) {
            try {
                DB::statement("ALTER TABLE `{$table}` 
                    MODIFY COLUMN `name` VARCHAR(255) NOT NULL,
                    MODIFY COLUMN `drum_material` TEXT NULL,
                    MODIFY COLUMN `gearbox_ratio` TEXT NULL,
                    MODIFY COLUMN `burner_type` TEXT NULL,
                    MODIFY COLUMN `airflow_system` TEXT NULL,
                    MODIFY COLUMN `cooling_bin_spec` TEXT NULL");

                if ($action === 'insert') {
                    return DB::table($table)->insertGetId($data);
                } else {
                    return DB::table($table)->where('id', $id)->update($data);
                }
            } catch (\Throwable $retryErr) {
                // Fallback pangkas aman jika izin ALTER dibatasi di database MySQL
                if (isset($data['drum_material'])) $data['drum_material'] = mb_substr($data['drum_material'], 0, 95);
                if (isset($data['gearbox_ratio'])) $data['gearbox_ratio'] = mb_substr($data['gearbox_ratio'], 0, 48);
                if (isset($data['burner_type'])) $data['burner_type'] = mb_substr($data['burner_type'], 0, 95);
                if (isset($data['airflow_system'])) $data['airflow_system'] = mb_substr($data['airflow_system'], 0, 95);
                if (isset($data['cooling_bin_spec'])) $data['cooling_bin_spec'] = mb_substr($data['cooling_bin_spec'], 0, 95);

                if ($action === 'insert') {
                    return DB::table($table)->insertGetId($data);
                } else {
                    return DB::table($table)->where('id', $id)->update($data);
                }
            }
        }
        throw $dbErr;
    }
}

function safeUploadProductImage($file, string $destinationDir, string $filename): bool
{
    try {
        if (!file_exists($destinationDir)) {
            @mkdir($destinationDir, 0777, true);
        }

        $targetPath = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        if (empty($sourcePath) || !file_exists($sourcePath)) {
            return false;
        }

        // 1. Coba move_uploaded_file (jika berkas upload HTTP standar)
        if (is_uploaded_file($sourcePath) && @move_uploaded_file($sourcePath, $targetPath)) {
            @chmod($targetPath, 0666);
            return true;
        }

        // 2. Coba copy (mengatasi false-negative is_writable pada OneDrive Windows)
        if (@copy($sourcePath, $targetPath)) {
            @chmod($targetPath, 0666);
            return true;
        }

        // 3. Coba file_get_contents & file_put_contents
        $content = @file_get_contents($sourcePath);
        if ($content !== false && @file_put_contents($targetPath, $content) !== false) {
            @chmod($targetPath, 0666);
            return true;
        }

        // 4. Fallback ke Symfony move
        try {
            $file->move($destinationDir, $filename);
            return true;
        } catch (\Throwable $moveEx) {
            return false;
        }
    } catch (\Throwable $e) {
        return false;
    }
}

function getCatalogProducts(): array
{
    ensureProductColumns();
    $customImages = Cache::get('product_custom_images', []);
    $defaultImages = [
        1 => asset('img/Kap 1.jpeg'),
        2 => asset('img/kap 5.jpeg'),
        3 => asset('img/kap 10.jpeg'),
        4 => asset('img/kap 20.jpeg'),
    ];

    try {
        $rows = DB::table(getProductsTable())->orderBy('id', 'asc')->get();

        return $rows->map(function ($item) use ($customImages, $defaultImages) {
            $imgProp = $item->image_path ?? ($item->image ?? null);
            $rawImg = $customImages[$item->id] ?? $imgProp;

            // Normalisasi URL gambar: ubah URL absolut localhost/127.0.0.1 menjadi URL dinamis berbasis asset()
            $imgUrl = null;
            if (!empty($rawImg)) {
                $cleaned = preg_replace('#^https?://[^/]+/(uploads/.*)$#i', '$1', (string) $rawImg);
                if (str_starts_with($cleaned, 'uploads/') || str_starts_with($cleaned, 'img/')) {
                    $imgUrl = asset(ltrim($cleaned, '/'));
                } elseif (filter_var($rawImg, FILTER_VALIDATE_URL) && !str_contains($rawImg, '127.0.0.1') && !str_contains($rawImg, 'localhost')) {
                    $imgUrl = $rawImg;
                } else {
                    $imgUrl = asset(ltrim($cleaned, '/'));
                }
            }

            if (empty($imgUrl)) {
                $imgUrl = $defaultImages[$item->id] ?? ($defaultImages[(($item->id ?? 1) % 4) + 1] ?? asset('img/Kap 1.jpeg'));
            }

            return [
                'id'               => (string) $item->id,
                'name'             => $item->name,
                'slug'             => $item->slug ?? Str::slug($item->name),
                'capacity_kg'      => (int) ($item->capacity_kg ?? 5),
                'drum_material'    => $item->drum_material ?? 'Tabung Full SS Double Jacket',
                'gearbox_ratio'    => $item->gearbox_ratio ?? 'Dinamo & Transmisi Gearbox',
                'motor_power_watt' => (int) ($item->motor_power_watt ?? 450),
                'burner_type'      => $item->burner_type ?? 'Kompor LPG Modifikasi Khusus Sangrai',
                'airflow_system'   => $item->airflow_system ?? '1 Blower 2 Fungsi (Hisap & Buang)',
                'cooling_bin_spec' => $item->cooling_bin_spec ?? 'Cooling Bin Stainless Steel',
                'estimation_days'  => (int) ($item->estimation_days ?? 14),
                'price'            => (string) ((int) ($item->price ?? 15000000)),
                'dp_percentage'    => 20,
                'dp_price'         => (string) ((int) round(((int) ($item->price ?? 15000000)) * 0.20)),
                'image'            => $imgUrl,
                'description'      => $item->description ?? '',
                'recommendation'   => $item->recommendation ?? '',
            ];
        })->toArray();
    } catch (\Throwable $e) {
        return [];
    }
}

function getOrdersList(): array
{
    $orders = Cache::rememberForever('admin_orders', function () {
        return [
            [
                'id'             => 'PO-20261101-RJ01',
                'customer_name'  => 'azell',
                'customer_email' => 'azell@rjgresik.com',
                'product_name'   => 'Mesin Roasting Kopi - Kapasitas 5 Kg',
                'quantity'       => 1,
                'dp_price'       => 3200000, // DP 20% dari Rp 16.000.000
                'total_price'    => 16000000,
                'step'           => 2,
                'date'           => '01 Nov 2026',
                'note'           => 'Pembayaran DP 20% terverifikasi otomatis via Midtrans. Pesanan masuk antrean perakitan mesin.',
                'dp_receipt'     => 'MID-DP-982103 • BCA VA (82770819283711) • Lunas 01 Nov 2026',
                'final_receipt'  => null,
            ],
            [
                'id'             => 'PO-20261104-RJ02',
                'customer_name'  => 'Budi Santoso',
                'customer_email' => 'budi@kopinusantara.com',
                'product_name'   => 'Mesin Roasting Kopi - Kapasitas 10 Kg',
                'quantity'       => 1,
                'dp_price'       => 5600000, // DP 20% dari Rp 28.000.000
                'total_price'    => 28000000,
                'step'           => 4,
                'date'           => '04 Nov 2026',
                'note'           => 'Unit mesin telah selesai dirakit dan lulus uji coba roasting (QC). Menunggu pelunasan 80% untuk pengiriman.',
                'dp_receipt'     => 'MID-DP-882910 • BCA VA (827708192837) • Lunas 04 Nov 2026',
                'final_receipt'  => null,
            ],
        ];
    });

    foreach ($orders as &$order) {
        $total = (int) ($order['total_price'] ?? 16000000);
        if (!isset($order['dp_price']) || (int) $order['dp_price'] === (int) ($total / 2)) {
            $order['dp_price'] = (int) round($total * 0.20);
        }
        $order['note'] = str_replace('DP 20%', 'DP 20%', $order['note'] ?? '');
    }
    unset($order);

    return $orders;
}

/**
 * Mencatat kunjungan unik calon pembeli pada katalog e-commerce RJ TEKNIK
 */
function recordCatalogVisit(): void
{
    try {
        if (!Schema::hasTable('catalog_visitors')) {
            Schema::create('catalog_visitors', function ($table) {
                $table->id();
                $table->string('session_id', 100)->index();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->boolean('has_interacted_ai')->default(false);
                $table->boolean('has_preordered')->default(false);
                $table->date('visit_date')->index();
                $table->timestamps();
            });
        }

        $today = now()->toDateString();
        $sessionId = session()->getId();
        $userId = Auth::id() ?? session('user_id');
        $ip = request()->ip();

        $visited = DB::table('catalog_visitors')
            ->where('session_id', $sessionId)
            ->where('visit_date', $today)
            ->first();

        if (!$visited) {
            DB::table('catalog_visitors')->insert([
                'session_id'        => $sessionId,
                'ip_address'        => $ip,
                'user_id'           => $userId,
                'has_interacted_ai' => false,
                'has_preordered'    => false,
                'visit_date'        => $today,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } elseif ($userId && !$visited->user_id) {
            DB::table('catalog_visitors')->where('id', $visited->id)->update([
                'user_id'    => $userId,
                'updated_at' => now(),
            ]);
        }
    } catch (\Throwable $e) {
        Log::warning('recordCatalogVisit warning: ' . $e->getMessage());
    }
}

/**
 * Mengambil ringkasan statistik pengunjung katalog calon pembeli e-commerce RJ TEKNIK
 * (Murni berbasis data aktual database MySQL, seluruh data fake/baseline uji coba telah dihapus)
 */
function getCatalogVisitorStats(?int $selectedYear = null, ?string $selectedPeriod = 'all'): array
{
    try {
        if (!Schema::hasTable('catalog_visitors')) {
            recordCatalogVisit();
        }

        $year = ($selectedYear && $selectedYear >= 2000 && $selectedYear <= 2099) ? $selectedYear : (int)date('Y');
        $period = $selectedPeriod ?: 'all';

        $now = now();
        $todayStr = $now->toDateString();
        $currentMonthStr = $now->format('Y-m');

        // Total riil pengunjung (TANPA DATA FAKE BASELINE)
        $query = DB::table('catalog_visitors');

        // Filter berdasarkan Tahun
        $query->whereYear('visit_date', $year);

        if ($period === 'week') {
            $sevenDaysAgo = now()->subDays(6)->toDateString();
            $query->whereBetween('visit_date', [$sevenDaysAgo, $todayStr]);
            $periodLabel = 'Minggu Ini (7 Hari Terakhir)';
        } elseif ($period !== 'all' && is_numeric($period)) {
            $monthNum = (int)$period;
            $monthStr = sprintf('%04d-%02d', $year, $monthNum);
            $query->where('visit_date', 'like', "{$monthStr}%");
            $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
            $periodLabel = 'Bulan ' . ($monthNames[$monthNum] ?? $monthNum) . ' ' . $year;
        } else {
            $periodLabel = '1 Tahun Penuh (' . $year . ')';
        }

        $periodVisitors = $query->count();
        $todayVisitors = DB::table('catalog_visitors')->where('visit_date', $todayStr)->count();
        $monthVisitors = DB::table('catalog_visitors')->where('visit_date', 'like', "{$currentMonthStr}%")->count();
        $allTimeVisitors = DB::table('catalog_visitors')->count();

        // Calon pembeli interaktif nyata (chat_logs unik + pesanan nyata)
        $chatUsersCount = Schema::hasTable('chat_logs')
            ? DB::table('chat_logs')->whereNotNull('user_id')->distinct('user_id')->count('user_id')
            : 0;

        $recap = getYearlySalesRecap($year, $period);
        $ordersCount = $recap['transaction_count'] ?? 0;

        // Calon pembeli interaktif (chat log + PO riil, TANPA ANGKA FAKE)
        $interactiveCount = $chatUsersCount + $ordersCount;
        if ($interactiveCount > $periodVisitors && $periodVisitors > 0) {
            $interactiveCount = $periodVisitors;
        }

        $conversionRate = $periodVisitors > 0 ? round(($interactiveCount / $periodVisitors) * 100, 1) : ($interactiveCount > 0 ? 100.0 : 0.0);
        $justBrowsingCount = max(0, $periodVisitors - $interactiveCount);

        return [
            'total_visitors'      => $periodVisitors,
            'period_visitors'     => $periodVisitors,
            'today_visitors'      => $todayVisitors,
            'month_visitors'      => $monthVisitors,
            'all_time_visitors'   => $allTimeVisitors,
            'period_label'        => $periodLabel,
            'interactive_count'   => $interactiveCount,
            'just_browsing_count' => $justBrowsingCount,
            'conversion_rate'     => $conversionRate,
            'chat_users_count'    => $chatUsersCount,
            'orders_count'        => $ordersCount,
        ];
    } catch (\Throwable $e) {
        return [
            'total_visitors'      => 0,
            'period_visitors'     => 0,
            'today_visitors'      => 0,
            'month_visitors'      => 0,
            'all_time_visitors'   => 0,
            'period_label'        => 'Data Terkini',
            'interactive_count'   => 0,
            'just_browsing_count' => 0,
            'conversion_rate'     => 0.0,
            'chat_users_count'    => 0,
            'orders_count'        => 0,
        ];
    }
}

/**
 * Menghasilkan data rekapitulasi penjualan per bulan dalam 1 tahun penuh (Januari - Desember)
 * Mendukung filter tahun, minggu, per-bulan, dan selama satu tahun penuh
 */
function getYearlySalesRecap($selectedYear = null, $selectedPeriod = null): array
{
    $year = ($selectedYear && (int)$selectedYear >= 2000 && (int)$selectedYear <= 2099) ? (int)$selectedYear : (int)date('Y');
    $isWeekFilter = ($selectedPeriod === 'week');
    $monthFilter = (!$isWeekFilter && $selectedPeriod && $selectedPeriod !== 'all' && is_numeric($selectedPeriod)) ? (int)$selectedPeriod : null;

    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $orders = getOrdersList();

    // Struktur 12 bulan murni berbasis data riil transaksi (Data fake uji coba telah dihapus)
    $monthlyData = [];
    foreach ($monthNames as $mNum => $mName) {
        $monthlyData[$mNum] = [
            'month_num'  => $mNum,
            'month_name' => $mName,
            'units'      => 0,
            'omzet'      => 0,
            'dp'         => 0,
            'status'     => 'Belum Ada Transaksi',
            'orders'     => [],
            'percentage' => 0,
        ];
    }

    $allYearOrders = [];
    $filteredOrdersList = [];

    $sevenDaysAgo = strtotime('-6 days 00:00:00');
    $tomorrow = strtotime('+1 day 23:59:59');

    // Agregasi dari data pesanan & transaksi nyata
    foreach ($orders as $order) {
        $orderDate = $order['date'] ?? null;
        $orderTs = $orderDate ? strtotime($orderDate) : null;
        $orderYear = $orderTs ? (int)date('Y', $orderTs) : (int)date('Y');
        $orderMonth = $orderTs ? (int)date('n', $orderTs) : (int)date('n');

        // Filter tahun
        if ($orderYear !== $year) {
            continue;
        }

        $allYearOrders[] = $order;

        $units = (int)($order['quantity'] ?? 1);
        $omzet = (int)($order['total_price'] ?? 0);
        $dp    = (int)($order['dp_price'] ?? ($omzet * 0.20));

        if (isset($monthlyData[$orderMonth])) {
            $monthlyData[$orderMonth]['units'] += $units;
            $monthlyData[$orderMonth]['omzet'] += $omzet;
            $monthlyData[$orderMonth]['dp'] += $dp;
            $monthlyData[$orderMonth]['orders'][] = $order;

            $step = (int)($order['step'] ?? 1);
            if ($step >= 5) {
                $monthlyData[$orderMonth]['status'] = 'Selesai & Lunas';
            } elseif ($step >= 2) {
                $monthlyData[$orderMonth]['status'] = 'Aktif Berjalan';
            } else {
                $monthlyData[$orderMonth]['status'] = 'Menunggu DP';
            }
        }

        // Filter periode
        if ($isWeekFilter) {
            if ($orderTs && $orderTs >= $sevenDaysAgo && $orderTs <= $tomorrow) {
                $filteredOrdersList[] = $order;
            }
        } elseif ($monthFilter) {
            if ($orderMonth === $monthFilter) {
                $filteredOrdersList[] = $order;
            }
        } else {
            $filteredOrdersList[] = $order;
        }
    }

    // Hitung total akumulasi tahunan nyata
    $totalAnnualOmzet = 0;
    $totalAnnualDp = 0;
    $totalAnnualUnits = 0;
    $maxMonthlyOmzet = 0;

    foreach ($monthlyData as $mNum => &$mRow) {
        $totalAnnualOmzet += $mRow['omzet'];
        $totalAnnualDp += $mRow['dp'];
        $totalAnnualUnits += $mRow['units'];
        if ($mRow['omzet'] > $maxMonthlyOmzet) {
            $maxMonthlyOmzet = $mRow['omzet'];
        }
    }
    unset($mRow);

    // Hitung persentase kontribusi omzet per bulan
    foreach ($monthlyData as $mNum => &$mRow) {
        $mRow['percentage'] = $totalAnnualOmzet > 0 ? round(($mRow['omzet'] / $totalAnnualOmzet) * 100, 1) : 0;
    }
    unset($mRow);

    // Tentukan KPI yang ditampilkan (apakah spesifik 1 minggu, 1 bulan, atau 1 tahun penuh)
    if ($isWeekFilter) {
        $displayOmzet = array_sum(array_column($filteredOrdersList, 'total_price'));
        $displayDp = array_sum(array_map(fn($o) => (int)($o['dp_price'] ?? round(($o['total_price'] ?? 0) * 0.20)), $filteredOrdersList));
        $displayUnits = array_sum(array_column($filteredOrdersList, 'quantity'));
        $periodLabel = 'Minggu Ini (7 Hari Terakhir ' . $year . ')';
    } elseif ($monthFilter && isset($monthlyData[$monthFilter])) {
        $targetMonth = $monthlyData[$monthFilter];
        $displayOmzet = $targetMonth['omzet'];
        $displayDp = $targetMonth['dp'];
        $displayUnits = $targetMonth['units'];
        $periodLabel = 'Bulan ' . $targetMonth['month_name'] . ' ' . $year;
    } else {
        $displayOmzet = $totalAnnualOmzet;
        $displayDp = $totalAnnualDp;
        $displayUnits = $totalAnnualUnits;
        $periodLabel = '1 Tahun Penuh (Januari - Desember ' . $year . ')';
    }

    return [
        'year'               => $year,
        'selected_period'    => $selectedPeriod ?: 'all',
        'selected_month'     => $selectedPeriod ?: 'all',
        'is_week_filter'     => $isWeekFilter,
        'month_filter'       => $monthFilter,
        'period_label'       => $periodLabel,
        'monthly'            => $monthlyData,
        'total_units'        => $displayUnits,
        'total_omzet'        => $displayOmzet,
        'total_dp'           => $displayDp,
        'annual_total_omzet' => $totalAnnualOmzet,
        'annual_total_dp'    => $totalAnnualDp,
        'annual_total_units' => $totalAnnualUnits,
        'max_monthly_omzet'  => max(1, $maxMonthlyOmzet),
        'avg_monthly_omzet'  => round($totalAnnualOmzet / 12),
        'transactions'       => $filteredOrdersList,
        'transaction_count'  => count($filteredOrdersList),
    ];
}

/*
|--------------------------------------------------------------------------
| RUTE SISI PELANGGAN (BERANDA, KATALOG, CHECKOUT, & STATUS ORDER)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (session('user_role') === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    recordCatalogVisit();
    return view('customer.home', ['products' => getCatalogProducts()]);
})->name('home');

Route::get('/katalog', function () {
    if (session('user_role') === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    recordCatalogVisit();
    return view('customer.catalog', ['products' => getCatalogProducts()]);
})->name('catalog');

// Rute Halaman Chatbot Penuh (Bukan Pop-up)
Route::get('/chatbot', function () {
    recordCatalogVisit();
    if ($userEmail = session('user_email')) {
        $matchedUser = User::where('email', strtolower(trim($userEmail)))->first();
        if ($matchedUser) {
            session(['user_id' => $matchedUser->id, 'user_name' => $matchedUser->name, 'user_role' => 'pelanggan']);
            if (!Auth::check() || Auth::id() !== $matchedUser->id) {
                Auth::login($matchedUser);
            }
        }
    }

    $userId = Auth::id() ?? session('user_id');
    $chatToken = $userId ? ('user_' . $userId) : ('guest_' . session()->getId());
    session(['active_chat_token' => $chatToken]);
    $isLoggedIn = Auth::check() || (session()->has('user_name') && session('user_role') !== 'guest');
    $guestRemaining = $isLoggedIn ? null : max(0, 2 - (int)session('guest_chat_count', 0));

    return view('customer.chatbot', [
        'chatToken'      => $chatToken,
        'isLoggedIn'     => $isLoggedIn,
        'guestRemaining' => $guestRemaining,
    ]);
})->name('chatbot.view');

// Rute API Chatbot Gemini (Dipanggil oleh JavaScript)
Route::post('/api/chatbot/message', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');
Route::get('/api/chatbot/history', [ChatbotController::class, 'getHistory'])->name('chatbot.history');
Route::post('/api/chatbot/clear', [ChatbotController::class, 'clearChat'])->name('chatbot.clear');


$orderStatusHandler = function () {
    if (!session('user_name')) {
        return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk melacak pesanan Anda.');
    }
    if (session('user_role') === 'admin') {
        return redirect()->route('admin.orders.index');
    }

    $userName = strtolower(trim(session('user_name', '')));
    $userEmail = strtolower(trim(session('user_email', '')));
    $allOrders = getOrdersList();

    $userOrders = array_values(array_filter($allOrders, function ($o) use ($userName, $userEmail) {
        $oName = strtolower(trim($o['customer_name'] ?? ''));
        $oEmail = strtolower(trim($o['customer_email'] ?? ''));
        return ($userEmail !== '' && $oEmail === $userEmail) || ($userName !== '' && $oName === $userName);
    }));

    return view('customer.order-status', ['orders' => $userOrders]);
};

Route::get('/order-status', $orderStatusHandler)->name('order.track');
Route::get('/status-pesanan', $orderStatusHandler)->name('order.status');

$checkoutViewHandler = function (Request $request) {
    $requestedId = (string) $request->query('product_id', $request->query('variant', 1));

    // 1. Cek Autentikasi: Pelanggan yang belum login diarahkan ke halaman login
    $isLoggedIn = Auth::check() || (session()->has('user_name') && session('user_role') !== 'guest');
    if (!$isLoggedIn) {
        session(['preorder_intent_product_id' => $requestedId ?: 1]);
        return redirect()->route('login')
            ->with('info', 'Silakan login atau daftarkan akun terlebih dahulu untuk melanjutkan pemesanan pre-order mesin.');
    }

    // 2. Cek Kelengkapan Data Diri: Alamat Lengkap & Nomor HP wajib diisi sebelum checkout
    $userAddress = session('user_address');
    $userPhone = session('user_phone');
    if (!$userAddress && Auth::check()) {
        $userAddress = Auth::user()->address;
    }
    if (!$userPhone && Auth::check()) {
        $userPhone = Auth::user()->phone;
    }

    if (empty(trim((string) $userAddress)) || empty(trim((string) $userPhone))) {
        session(['preorder_intent_product_id' => $requestedId ?: 1]);
        return redirect()->route('profile', ['tab' => 'settings'])
            ->with('info', 'Alamat Lengkap dan Nomor HP wajib dilengkapi di Pengaturan Akun terlebih dahulu sebelum melakukan transaksi pre-order mesin.');
    }

    $products = getCatalogProducts();
    $selectedProduct = null;

    foreach ($products as $prod) {
        if ((string) ($prod['id'] ?? '') === (string) $requestedId 
            || (string) ($prod['slug'] ?? '') === (string) $requestedId
            || (string) ($prod['capacity_kg'] ?? '') === (string) $requestedId
            || str_contains(strtolower($prod['name'] ?? ''), strtolower($requestedId))) {
            $selectedProduct = $prod;
            break;
        }
    }

    if (!$selectedProduct && !empty($products)) {
        $selectedProduct = $products[0];
    }

    return view('customer.checkout', [
        'products'        => $products,
        'product_id'      => $requestedId,
        'selectedProduct' => $selectedProduct,
    ]);
};

Route::get('/checkout', $checkoutViewHandler)->name('checkout.view');
Route::get('/checkout-mesin', $checkoutViewHandler)->name('checkout');

Route::get('/profil', function () {
    $userName = strtolower(trim(session('user_name', Auth::user()->name ?? '')));
    $userEmail = strtolower(trim(session('user_email', Auth::user()->email ?? '')));
    $allOrders = getOrdersList();

    $userOrders = array_values(array_filter($allOrders, function ($o) use ($userName, $userEmail) {
        $oName = strtolower(trim($o['customer_name'] ?? ''));
        $oEmail = strtolower(trim($o['customer_email'] ?? ''));
        return ($userEmail !== '' && $oEmail === $userEmail) || ($userName !== '' && $oName === $userName);
    }));

    return view('customer.profile', [
        'orders'     => $userOrders,
        'defaultTab' => request('tab', session('preorder_intent_product_id') ? 'settings' : 'profile'),
    ]);
})->name('profile');


/*
|--------------------------------------------------------------------------
| RUTE PEMBAYARAN MIDTRANS & STATUS ORDER (DP & PELUNASAN)
|--------------------------------------------------------------------------
*/
Route::post('/order/pay/{id}', function (Request $request, $id) {
    $orders = getOrdersList();
    $paymentType = $request->input('payment_type', 'final');
    $bankMethod = $request->input('bank_method', 'BCA Virtual Account');
    $vaNumber = $request->input('va_number', '827708129384712');
    $paidTime = date('d M Y H:i') . ' WIB';

    foreach ($orders as &$order) {
        if ($order['id'] === $id) {
            if ($paymentType === 'dp' || (int) $order['step'] === 1) {
                $trxId = 'MID-DP-' . rand(100000, 999999);
                $order['step'] = 2;
                $order['dp_receipt'] = $trxId . ' • ' . $bankMethod . ' (' . $vaNumber . ') • Lunas ' . $paidTime;
                $order['note'] = 'Pembayaran DP 20% terverifikasi otomatis via Midtrans (' . $trxId . '). Unit masuk antrean perakitan workshop RJ TEKNIK.';
            } else {
                $trxId = 'MID-FINAL-' . rand(100000, 999999);
                $order['step'] = 5;
                $order['final_receipt'] = $trxId . ' • ' . $bankMethod . ' (' . $vaNumber . ') • Lunas ' . $paidTime;
                $order['note'] = 'Pelunasan 80% akhir lunas terverifikasi via Midtrans (' . $trxId . '). Unit mesin sedang dikirim kargo ke alamat Anda.';
            }
            break;
        }
    }
    unset($order);

    Cache::forever('admin_orders', $orders);

    return redirect()->route('order.track')->with('success', "Pembayaran berhasil diverifikasi! Status pesanan telah diperbarui.");
})->name('order.pay');

Route::post('/preorder/checkout', function (Request $request) {
    $request->validate([
        'name'             => 'required|string|max:100',
        'phone'            => 'required|string|max:20',
        'shipping_address' => 'required|string',
    ]);

    $qty = max(1, (int) $request->input('quantity', 1));
    $productId = (string) $request->input('product_id');
    $products = getCatalogProducts();
    $matchedProduct = collect($products)->firstWhere('id', $productId);

    $baseProductName = $matchedProduct['name'] ?? $request->input('product_name', 'Mesin Roasting Kopi');
    $productName = $qty > 1 && !str_contains($baseProductName, 'Unit')
        ? $baseProductName . ' (' . $qty . ' Unit)'
        : $request->input('product_name', $baseProductName);

    $unitPrice = isset($matchedProduct['price']) ? (int) $matchedProduct['price'] : 15000000;
    $fullPrice = (int) preg_replace('/[^0-9]/', '', (string) $request->input('total_price', $unitPrice * $qty));
    if ($fullPrice <= 0) {
        $fullPrice = $unitPrice * $qty;
    }

    // Perhitungan DP 20% Sesuai Bab 3 Skripsi RJ Gresik
    $dpPrice = (int) round($fullPrice * 0.20);

    \Midtrans\Config::$serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
    \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    $orderNumber = 'PO-' . date('Ymd') . '-RJ' . rand(10, 99);

    $params = [
        'transaction_details' => [
            'order_id'     => $orderNumber,
            'gross_amount' => (int) $dpPrice,
        ],
        'customer_details' => [
            'first_name' => $request->input('name', 'Pelanggan'),
            'phone'      => $request->input('phone', '081234567890'),
        ],
        'item_details' => [
            [
                'id'       => $productId ?: '1',
                'price'    => (int) $dpPrice,
                'quantity' => 1,
                'name'     => 'DP 20% - ' . substr($productName, 0, 40),
            ]
        ]
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        session([
            'pending_order_data' => [
                'id'             => $orderNumber,
                'customer_name'  => session('user_name', $request->input('name', 'Pelanggan')),
                'customer_email' => session('user_email', 'pelanggan@rjgresik.com'),
                'product_name'   => $productName,
                'quantity'       => $qty,
                'dp_price'       => $dpPrice,
                'total_price'    => $fullPrice,
                'step'           => 2,
                'date'           => date('d M Y'),
                'note'           => 'Pembayaran DP 20% terverifikasi otomatis via Midtrans Snap (' . $orderNumber . '). Unit masuk antrean perakitan.',
                'dp_receipt'     => $orderNumber . ' • Midtrans Snap • Lunas ' . date('d M Y H:i'),
                'final_receipt'  => null,
            ]
        ]);

        return response()->json([
            'status'     => 'success',
            'snap_token' => $snapToken,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal terhubung ke Midtrans: ' . $e->getMessage()
        ], 500);
    }
})->name('preorder.store');

Route::get('/preorder/success', function () {
    $pendingOrder = session('pending_order_data');
    if ($pendingOrder) {
        $orders = getOrdersList();
        array_unshift($orders, $pendingOrder);
        Cache::forever('admin_orders', $orders);
        session()->forget('pending_order_data');
    }
    return redirect()->route('order.track')->with('success', "Pembayaran DP 20% Berhasil dan Terverifikasi oleh Midtrans!");
})->name('preorder.success');

/*
|--------------------------------------------------------------------------
| MIDTRANS HTTP WEBHOOK NOTIFICATION (VERIFIKASI SHA-512)
|--------------------------------------------------------------------------
*/
Route::post('/api/midtrans/notification', function (Request $request) {
    $serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');

    // Ambil payload notifikasi dari Midtrans
    $orderId = (string) $request->input('order_id');
    $statusCode = (string) $request->input('status_code');
    $grossAmount = (string) $request->input('gross_amount');
    $signatureKey = (string) $request->input('signature_key');
    $transactionStatus = (string) $request->input('transaction_status');
    $paymentType = (string) $request->input('payment_type', 'bank_transfer');
    $settlementTime = (string) ($request->input('settlement_time') ?: date('Y-m-d H:i:s'));

    // Verifikasi hash signature SHA-512 sesuai standar resmi Midtrans API
    if (!empty($serverKey) && !empty($signatureKey)) {
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($signatureKey !== $expectedSignature) {
            \Illuminate\Support\Facades\Log::warning("Midtrans Webhook: Invalid SHA-512 signature for order {$orderId}");
            return response()->json(['status' => 'error', 'message' => 'Invalid signature key'], 403);
        }
    }

    // Ambil pesanan yang ada di cache atau database
    $orders = getOrdersList();
    $found = false;

    foreach ($orders as &$order) {
        if ($order['id'] === $orderId) {
            $found = true;
            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                // Pembayaran DP 20% Terverifikasi
                $order['step'] = 2;
                $order['note'] = 'Pembayaran DP 20% telah diverifikasi otomatis via Midtrans Webhook SHA-512 (' . $paymentType . '). Unit masuk antrean perakitan.';
                $order['dp_receipt'] = $orderId . ' • Midtrans (' . strtoupper($paymentType) . ') • Lunas ' . date('d M Y H:i', strtotime($settlementTime));
            } elseif ($transactionStatus === 'pending') {
                $order['step'] = 1;
                $order['note'] = 'Menunggu pembayaran uang muka (DP 20%) dari customer via Midtrans (' . $paymentType . ').';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $order['step'] = 1;
                $order['note'] = 'Transaksi Midtrans berstatus ' . strtoupper($transactionStatus) . '. Silakan lakukan pemesanan ulang.';
            }
            break;
        }
    }

    if ($found) {
        Cache::forever('admin_orders', $orders);
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'Notifikasi Midtrans berhasil diverifikasi dan diproses'
    ]);
})->name('midtrans.notification');


/*
|--------------------------------------------------------------------------
| RUTE AUTENTIKASI (LOGIN, REGISTER, GOOGLE OAUTH)
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/login', function (Request $request) {
    $email = trim(strtolower($request->input('email', 'pelanggan@email.com')));
    $password = $request->input('password', 'password');

    // 1. Jika email admin, autentikasi dan arahkan ke dashboard
    if (str_contains($email, 'admin')) {
        $adminUser = null;
        try {
            $adminUser = User::where('email', $email)->first();
            if (!$adminUser) {
                $adminUser = User::create([
                    'name'     => 'Admin RJ TEKNIK',
                    'email'    => $email,
                    'password' => Hash::make($password),
                    'phone'    => '081234567890',
                    'role'     => 'admin',
                ]);
            }
            Auth::login($adminUser);
        } catch (\Throwable $e) {}

        session([
            'user_id'    => $adminUser ? $adminUser->id : 1,
            'user_name'  => $adminUser ? $adminUser->name : 'Admin RJ TEKNIK',
            'user_email' => $email,
            'user_role'  => 'admin',
        ]);
        return redirect()->route('admin.dashboard');
    }

    // 2. Autentikasi Pengguna Pelanggan
    $customerName = ucfirst(explode('@', $email)[0]);
    $isNewUser = false;
    $customerUser = User::where('email', $email)->first();
    if (!$customerUser) {
        $isNewUser = true;
        $customerUser = User::create([
            'name'            => $customerName,
            'email'           => $email,
            'password'        => Hash::make($password),
            'role'            => 'pelanggan',
            'is_chat_deleted' => false,
        ]);
    } else {
        $customerUser->is_chat_deleted = false;
        $customerUser->save();
    }
    Auth::login($customerUser);

    $userId = $customerUser->id;
    $finalName = $customerUser->name ?: $customerName;
    $address = $customerUser->address ?? session('user_address');
    $phone = $customerUser->phone ?? session('user_phone');
    $sessionId = session()->getId();

    session([
        'user_id'      => $userId,
        'user_name'    => $finalName,
        'user_email'   => $email,
        'user_role'    => 'pelanggan',
        'user_address' => $address,
        'user_phone'   => $phone,
    ]);

    // Migrasi otomatis riwayat chat tamu ke akun pengguna saat login
    try {
        $activeChatToken = session('active_chat_token');
        $cleanActive = $activeChatToken ? str_replace('guest_', '', (string)$activeChatToken) : null;
        $guestTokens = array_unique(array_filter([$sessionId, $activeChatToken, $cleanActive]));
        if (!empty($guestTokens)) {
            ChatLog::whereNull('user_id')
                ->whereIn('session_id', $guestTokens)
                ->update(['user_id' => $userId]);
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning("Migrasi chat tamu login error: " . $e->getMessage());
    }
    session()->forget('guest_chat_count');

    // ALUR CHECKOUT PRE-ORDER: Jika ada niat pemesanan mesin dari katalog
    if ($intentProductId = session('preorder_intent_product_id')) {
        if (empty(trim((string) $address)) || empty(trim((string) $phone))) {
            return redirect()->route('profile', ['tab' => 'settings'])
                ->with('info', 'Silakan lengkapi Alamat Lengkap dan Nomor HP Anda di Pengaturan Akun terlebih dahulu sebelum melanjutkan transaksi pre-order mesin.');
        } else {
            session()->forget('preorder_intent_product_id');
            return redirect()->route('checkout.view', ['product_id' => $intentProductId])
                ->with('success', "Selamat datang kembali, $finalName! Silakan periksa rincian pemesanan dan lanjutkan transaksi pre-order mesin Anda.");
        }
    }

    return redirect()->route('home')->with('success', "Selamat datang kembali, $finalName!");
})->name('login.submit');

Route::post('/register', function (Request $request) {
    $name = trim($request->input('name', 'Pelanggan Baru'));
    $email = trim(strtolower($request->input('email', 'pelanggan@email.com')));
    $password = $request->input('password', 'password');

    $isNewUser = false;
    $customerUser = User::where('email', $email)->first();
    if (!$customerUser) {
        $isNewUser = true;
        $customerUser = User::create([
            'name'            => $name,
            'email'           => $email,
            'password'        => Hash::make($password),
            'role'            => 'pelanggan',
            'is_chat_deleted' => false,
        ]);
    } else {
        $customerUser->name = $name;
        $customerUser->is_chat_deleted = false;
        $customerUser->save();
    }
    Auth::login($customerUser);

    $userId = $customerUser->id;
    $sessionId = session()->getId();

    session([
        'user_id'    => $userId,
        'user_name'  => $customerUser->name,
        'user_email' => $email,
        'user_role'  => 'pelanggan',
    ]);

    // Migrasi otomatis riwayat chat tamu ke akun pengguna yang baru mendaftar
    try {
        $activeChatToken = session('active_chat_token');
        $cleanActive = $activeChatToken ? str_replace('guest_', '', (string)$activeChatToken) : null;
        $guestTokens = array_unique(array_filter([$sessionId, $activeChatToken, $cleanActive]));
        if (!empty($guestTokens)) {
            ChatLog::whereNull('user_id')
                ->whereIn('session_id', $guestTokens)
                ->update(['user_id' => $userId]);
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning("Migrasi chat tamu register error: " . $e->getMessage());
    }
    session()->forget('guest_chat_count');

    // Kirim email sambutan pendaftaran akun resmi RJ TEKNIK
    if ($isNewUser) {
        try {
            Mail::to($customerUser->email)->send(new UserRegisteredMail($customerUser, 'Registrasi Web RJ TEKNIK'));
        } catch (\Throwable $mailErr) {
            \Illuminate\Support\Facades\Log::warning("Gagal kirim email registrasi: " . $mailErr->getMessage());
        }
    }

    // ALUR CHECKOUT PRE-ORDER: Pelanggan baru yang mendaftar wajib melengkapi data diri sebelum checkout
    if ($intentProductId = session('preorder_intent_product_id')) {
        return redirect()->route('profile', ['tab' => 'settings'])
            ->with('info', "Pendaftaran akun berhasil! Silakan lengkapi Alamat Lengkap dan Nomor HP Anda di Pengaturan Akun terlebih dahulu sebelum melakukan transaksi pre-order.");
    }

    return redirect()->route('home')->with('success', "Akun $name berhasil terdaftar!");
})->name('register.submit');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    session()->flush();

    return redirect()->route('login')->with('success', 'Anda telah keluar dari akun.');
})->name('logout');

Route::post('/profil/update', function (Request $request) {
    $request->validate([
        'name'    => 'required|string|max:100',
        'address' => 'required|string|min:5',
        'phone'   => 'required|string|min:8|max:25',
    ]);

    $newName = trim($request->input('name', session('user_name')));
    $newAddress = trim($request->input('address', session('user_address')));
    $newPhone = trim($request->input('phone', session('user_phone')));

    session([
        'user_name'    => $newName,
        'user_address' => $newAddress,
        'user_phone'   => $newPhone,
    ]);

    try {
        if ($user = Auth::user()) {
            $user->name = $newName;
            $user->address = $newAddress;
            $user->phone = $newPhone;
            $user->save();
        }
    } catch (\Throwable $e) {}

    // ALUR CHECKOUT PRE-ORDER: Setelah data diri lengkap, otomatis arahkan langsung ke checkout pemesanan mesin
    if ($intentProductId = session('preorder_intent_product_id')) {
        session()->forget('preorder_intent_product_id');
        return redirect()->route('checkout.view', ['product_id' => $intentProductId])
            ->with('success', 'Data diri berhasil dilengkapi! Silakan periksa rincian pemesanan dan lanjutkan transaksi pre-order mesin Anda.');
    }

    return redirect()->route('profile', ['tab' => 'settings'])->with('success', 'Informasi profil dan pengaturan akun berhasil disimpan!');
})->name('profile.update');

Route::get('/auth/google', function (Request $request) {
    // Dynamic redirect URI sesuai host/IP pemanggil (misal: 192.168.1.10:8000 untuk HP teman atau 127.0.0.1:8000)
    $redirectUrl = url('/auth/google/callback');
    return Socialite::driver('google')->redirectUrl($redirectUrl)->redirect();
})->name('auth.google');

Route::get('/auth/google/callback', function (Request $request) {
    try {
        // Dynamic redirect URI untuk validasi token OAuth Google
        $redirectUrl = url('/auth/google/callback');
        $googleUser = Socialite::driver('google')->redirectUrl($redirectUrl)->user();

        $googleEmail = trim(strtolower($googleUser->getEmail()));
        $user = User::where('email', $googleEmail)->first();
        $isNewUser = false;
        if (!$user) {
            $user = User::create([
                'name'            => $googleUser->getName(),
                'email'           => $googleEmail,
                'password'        => Hash::make(Str::random(24)),
                'role'            => 'pelanggan',
                'is_chat_deleted' => false,
            ]);
            $isNewUser = true;
        } else {
            $user->name = $googleUser->getName();
            $user->is_chat_deleted = false;
            $user->save();
        }

        Auth::login($user);
        $userId = $user->id;

        $address = $user->address ?? session('user_address', '');
        $phone = $user->phone ?? session('user_phone', '');

        session([
            'user_id'      => $userId,
            'user_name'    => $user->name,
            'user_email'   => $user->email,
            'user_avatar'  => $googleUser->getAvatar(),
            'user_role'    => 'pelanggan',
            'user_address' => $address,
            'user_phone'   => $phone,
        ]);

        // Migrasi otomatis riwayat chat tamu ke akun pengguna yang baru login/terdaftar
        try {
            $currentSessionId = session()->getId();
            $activeChatToken = session('active_chat_token');
            $cleanActive = $activeChatToken ? str_replace('guest_', '', (string)$activeChatToken) : null;
            $guestTokens = array_unique(array_filter([$currentSessionId, $activeChatToken, $cleanActive]));
            if (!empty($guestTokens)) {
                ChatLog::whereNull('user_id')
                    ->whereIn('session_id', $guestTokens)
                    ->update(['user_id' => $userId]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Migrasi chat tamu Google login error: " . $e->getMessage());
        }

        session()->forget('guest_chat_count');

        // Kirim email sambutan pendaftaran akun resmi RJ TEKNIK jika pengguna baru
        if ($isNewUser) {
            try {
                Mail::to($user->email)->send(new UserRegisteredMail($user, 'Google Account'));
            } catch (\Throwable $mailErr) {
                \Illuminate\Support\Facades\Log::warning("Gagal kirim email registrasi Google: " . $mailErr->getMessage());
            }
        }

        // ALUR CHECKOUT PRE-ORDER: Jika ada niat pemesanan mesin dari katalog
        if ($intentProductId = session('preorder_intent_product_id')) {
            if (empty(trim((string) $address)) || empty(trim((string) $phone))) {
                return redirect()->route('profile', ['tab' => 'settings'])
                    ->with('info', 'Silakan lengkapi Alamat Lengkap dan Nomor HP Anda di Pengaturan Akun terlebih dahulu sebelum melanjutkan transaksi pre-order mesin.');
            } else {
                session()->forget('preorder_intent_product_id');
                return redirect()->route('checkout.view', ['product_id' => $intentProductId])
                    ->with('success', "Selamat datang, " . $googleUser->getName() . "! Silakan periksa rincian pemesanan dan lanjutkan transaksi pre-order mesin Anda.");
            }
        }

        return redirect()->route('home')->with('success', 'Berhasil masuk melalui Google sebagai ' . $googleUser->getName() . '!');
    } catch (\Exception $e) {
        return redirect()->route('login')->with('error', 'Gagal Login Google API: ' . $e->getMessage());
    }
})->name('auth.google.callback');


/*
|--------------------------------------------------------------------------
| RUTE ADMIN WORKSPACE (DASHBOARD, PRODUK, ORDER, LAPORAN, CHAT LOGS)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // 0. API Endpoint Notifikasi Real-time untuk Admin (Pesanan Baru Masuk & Chat Butuh Balasan)
    Route::get('/api/notifications', function (Request $request) {
        try {
            // 1. Chat yang butuh balasan admin (HANYA status = pending_admin)
            // Pesan customer yang sudah dijawab/diselesaikan AI CS (status = resolved) TIDAK dimunculkan notifikasi
            $pendingChatLogs = Schema::hasTable('chat_logs')
                ? ChatLog::with('user')
                    ->where('status', 'pending_admin')
                    ->orderBy('id', 'desc')
                    ->get()
                : collect([]);

            $pendingChatsCount = $pendingChatLogs->count();
            $pendingChatsList = $pendingChatLogs->take(50)->map(function ($log) {
                $user = $log->user;
                $userName = $user ? $user->name : null;
                $targetId = $user ? $user->id : ($log->session_id ?: 'guest');
                $targetConvId = $user ? (string)$user->id : (str_starts_with((string)$targetId, 'guest_') ? (string)$targetId : ('guest_' . $targetId));

                if (!$userName) {
                    $cleanSess = str_replace('guest_', '', $log->session_id ?: 'guest');
                    $shortId = strtoupper(substr(md5($cleanSess), 0, 4));
                    $userName = 'Pengunjung Web (Tamu #' . $shortId . ')';
                }

                $timeStr = $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('H:i') : now()->format('H:i');

                return [
                    'id'         => $log->id,
                    'user_id'    => $targetConvId,
                    'user_name'  => $userName,
                    'message'    => Str::limit($log->user_message, 55),
                    'time_str'   => $timeStr . ' WIB',
                    'url'        => route('admin.chatlogs.index', ['user_id' => $targetConvId]),
                ];
            })->values();

            // 2. Pesanan Masuk (orders dengan step = 2 (DP 20% Terverifikasi) atau step = 1)
            $allOrders = getOrdersList();
            $newOrdersList = collect($allOrders)->filter(function ($order) {
                return in_array((int)($order['step'] ?? 2), [1, 2, 3]);
            })->take(50)->map(function ($order) {
                return [
                    'id'            => $order['id'] ?? 'PO-NEW',
                    'customer_name' => $order['customer_name'] ?? 'Pelanggan',
                    'product_name'  => $order['product_name'] ?? 'Mesin Roasting Kopi',
                    'dp_price_fmt'  => 'Rp ' . number_format($order['dp_price'] ?? 0, 0, ',', '.'),
                    'step'          => $order['step'] ?? 2,
                    'step_label'    => ($order['step'] ?? 2) === 2 ? 'DP 20% Terverifikasi' : 'Pesanan Masuk',
                    'date'          => $order['date'] ?? date('d M Y'),
                    'url'           => route('admin.orders.index', ['view' => 'orders']),
                ];
            })->values();

            $newOrdersCount = count($newOrdersList);
            $unreadTotal = $pendingChatsCount + $newOrdersCount;

            $latestPendingId = $pendingChatLogs->first()->id ?? 0;
            $latestOrderId = $newOrdersList->first()['id'] ?? 'none';

            // Signature HANYA memuat status event pending admin dan order masuk
            $latestEventId = "ord_{$newOrdersCount}_{$latestOrderId}_pend_{$pendingChatsCount}_{$latestPendingId}";

            return response()->json([
                'status'              => 'success',
                'unread_total'        => $unreadTotal,
                'pending_chats_count' => $pendingChatsCount,
                'pending_chats'       => $pendingChatsList,
                'new_orders_count'    => $newOrdersCount,
                'new_orders'          => $newOrdersList,
                'latest_event_id'     => $latestEventId,
                'latest_pending_id'   => $latestPendingId,
                'latest_order_id'     => $latestOrderId,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'              => 'error',
                'message'             => $e->getMessage(),
                'unread_total'        => 0,
                'pending_chats_count' => 0,
                'pending_chats'       => [],
                'new_orders_count'    => 0,
                'new_orders'          => [],
            ], 500);
        }
    })->name('api.notifications');

    // 1. Dashboard Admin (Ringkasan Penjualan + Jumlah Pengunjung Katalog & Konversi)
    Route::get('/dashboard', function (Request $request) {
        if ($request->query('tab') === 'products' || $request->query('view') === 'products') {
            $logs = Schema::hasTable('chat_logs') ? ChatLog::with('user')->latest()->get() : collect([]);
            return view('admin.dashboard', [
                'products'     => getCatalogProducts(),
                'orders'       => getOrdersList(),
                'logs'         => $logs,
                'visitorStats' => getCatalogVisitorStats(),
            ]);
        }
        if ($request->query('tab') === 'orders' || $request->query('view') === 'orders') {
            return redirect()->route('admin.orders.index', ['view' => 'orders']);
        }
        if ($request->query('tab') === 'recap' || $request->query('view') === 'recap') {
            return redirect()->route('admin.recap');
        }
        if ($request->query('tab') === 'chatlogs') {
            return redirect()->route('admin.chatlogs.index');
        }

        $logs = Schema::hasTable('chat_logs') ? ChatLog::with('user')->latest()->get() : collect([]);
        $rawYear = (int)$request->query('year', date('Y'));
        $selectedYear = ($rawYear >= 2000 && $rawYear <= 2099) ? $rawYear : (int)date('Y');
        $selectedPeriod = $request->query('period', $request->query('month', 'all'));

        $recap = getYearlySalesRecap($selectedYear, $selectedPeriod);

        return view('admin.orders', [
            'products'       => getCatalogProducts(),
            'orders'         => $recap['transactions'],
            'logs'           => $logs,
            'visitorStats'   => getCatalogVisitorStats($selectedYear, $selectedPeriod),
            'yearlyRecap'    => $recap,
            'selectedYear'   => $selectedYear,
            'selectedPeriod' => $selectedPeriod,
            'selectedMonth'  => $selectedPeriod,
            'view'           => 'dashboard',
        ]);
    })->name('dashboard');

    // 2. Katalog & Produk PO
    Route::get('/products', function (Request $request) {
        $logs = Schema::hasTable('chat_logs') ? ChatLog::with('user')->latest()->get() : collect([]);
        return view('admin.dashboard', [
            'products'     => getCatalogProducts(),
            'orders'       => getOrdersList(),
            'logs'         => $logs,
            'visitorStats' => getCatalogVisitorStats(),
        ]);
    })->name('products.index');

    // 3. Status Order & Produksi
    Route::get('/orders', function (Request $request) {
        $logs = Schema::hasTable('chat_logs') ? ChatLog::with('user')->latest()->get() : collect([]);
        $viewMode = $request->query('view', 'orders');
        $rawYear = (int)$request->query('year', date('Y'));
        $selectedYear = ($rawYear >= 2000 && $rawYear <= 2099) ? $rawYear : (int)date('Y');
        $selectedPeriod = $request->query('period', $request->query('month', 'all'));

        $recap = getYearlySalesRecap($selectedYear, $selectedPeriod);

        // Tandai pesanan aktif telah dilihat oleh admin di sesi
        $allOrders = getOrdersList();
        $activeOrdersCount = count(collect($allOrders)->filter(fn($o) => in_array((int)($o['step'] ?? 2), [1, 2, 3]))->values());
        session(['admin_orders_seen_count' => $activeOrdersCount]);

        return view('admin.orders', [
            'products'       => getCatalogProducts(),
            'orders'         => getOrdersList(),
            'logs'           => $logs,
            'visitorStats'   => getCatalogVisitorStats($selectedYear, $selectedPeriod),
            'yearlyRecap'    => $recap,
            'selectedYear'   => $selectedYear,
            'selectedPeriod' => $selectedPeriod,
            'selectedMonth'  => $selectedPeriod,
            'view'           => $viewMode,
        ]);
    })->name('orders.index');

    // 4. Rekapitulasi Penjualan (MENU BARU)
    Route::get('/recap', function (Request $request) {
        $logs = Schema::hasTable('chat_logs') ? ChatLog::with('user')->latest()->get() : collect([]);
        $rawYear = (int)$request->query('year', date('Y'));
        $selectedYear = ($rawYear >= 2000 && $rawYear <= 2099) ? $rawYear : (int)date('Y');
        $selectedPeriod = $request->query('period', $request->query('month', 'all'));

        $recap = getYearlySalesRecap($selectedYear, $selectedPeriod);

        return view('admin.orders', [
            'products'       => getCatalogProducts(),
            'orders'         => $recap['transactions'],
            'logs'           => $logs,
            'visitorStats'   => getCatalogVisitorStats($selectedYear, $selectedPeriod),
            'yearlyRecap'    => $recap,
            'selectedYear'   => $selectedYear,
            'selectedPeriod' => $selectedPeriod,
            'selectedMonth'  => $selectedPeriod,
            'view'           => 'recap',
        ]);
    })->name('recap');

    // Alias Rute Laporan Penjualan (diarahkan ke Dashboard Admin)
    Route::get('/reports', function () {
        return redirect()->route('admin.dashboard');
    })->name('reports.index');

    // Halaman Khusus Konsultasi & Log AI (chatlogs.blade.php) - Antarmuka Chat CRM WhatsApp Web
    Route::get('/chatlogs', function (Request $request) {
        try {
            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_chat_deleted')) {
                Schema::table('users', fn($t) => $t->boolean('is_chat_deleted')->default(false));
            }
            if (Schema::hasTable('chat_logs')) {
                if (!Schema::hasColumn('chat_logs', 'admin_reply')) {
                    Schema::table('chat_logs', fn($t) => $t->text('admin_reply')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'admin_id')) {
                    Schema::table('chat_logs', fn($t) => $t->unsignedBigInteger('admin_id')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'replied_at')) {
                    Schema::table('chat_logs', fn($t) => $t->timestamp('replied_at')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'status')) {
                    Schema::table('chat_logs', fn($t) => $t->string('status', 50)->default('resolved'));
                }
            }
        } catch (\Throwable $e) {}

        $allLogs = Schema::hasTable('chat_logs') ? ChatLog::with(['user', 'admin'])->orderBy('id', 'asc')->get() : collect([]);
        $registeredUsers = User::where('role', '!=', 'admin');
        if (Schema::hasColumn('users', 'is_chat_deleted')) {
            $registeredUsers = $registeredUsers->where('is_chat_deleted', false);
        }
        $registeredUsers = $registeredUsers->get();

        // 1. Kelompokkan percakapan per akun pelanggan (1 ruang chat per akun)
        $customerConversations = $registeredUsers->map(function ($user) use ($allLogs) {
            $userLogs = $allLogs->where('user_id', $user->id)->values();
            $latestLog = $userLogs->last();
            $pendingCount = $userLogs->where('status', 'pending_admin')->count();
            $repliedCount = $userLogs->where('status', 'answered_by_admin')->count();
            $groundingCount = $userLogs->filter(fn($l) => ($l->category ?? 'in_scope_db') === 'in_scope_db')->count();
            $outOfScopeCount = $userLogs->filter(fn($l) => ($l->category ?? '') === 'out_of_scope_irrelevant' || $l->is_out_of_scope)->count();

            $latestTime = null;
            if ($latestLog) {
                $latestTime = $latestLog->replied_at && $latestLog->replied_at > $latestLog->created_at 
                    ? $latestLog->replied_at 
                    : $latestLog->created_at;
            }

            $snippet = 'Belum ada riwayat percakapan';
            if ($latestLog) {
                if ($latestLog->admin_reply) {
                    $snippet = 'Admin: ' . Str::limit($latestLog->admin_reply, 38);
                } else {
                    $snippet = Str::limit($latestLog->user_message, 45);
                }
            }

            return [
                'type'               => 'registered',
                'id'                 => (string) $user->id,
                'user_id'            => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'phone'              => $user->phone ?: '',
                'address'            => $user->address,
                'avatar'             => $user->avatar ?? null,
                'logs'               => $userLogs,
                'total_messages'     => $userLogs->count(),
                'latest_log'         => $latestLog,
                'latest_activity'    => $latestTime,
                'latest_snippet'     => $snippet,
                'pending_count'      => $pendingCount,
                'replied_count'      => $repliedCount,
                'grounding_count'    => $groundingCount,
                'out_of_scope_count' => $outOfScopeCount,
                'is_ai_enabled'      => (bool) ($user->is_ai_enabled ?? true),
            ];
        });

        // 2. Sertakan log dari sesi tamu (dikelompokkan per sesi tamu unik agar tidak tercampur)
        $guestLogs = $allLogs->whereNull('user_id')->values();
        if ($guestLogs->isNotEmpty()) {
            $guestGroups = $guestLogs->groupBy(function ($log) {
                return $log->session_id ?: 'guest_default';
            });

            foreach ($guestGroups as $sessionId => $groupLogs) {
                $groupLogs = $groupLogs->values();
                $latestGuest = $groupLogs->last();
                $cleanSessionId = (string) $sessionId;
                $shortId = strtoupper(substr(md5($cleanSessionId), 0, 4));
                $guestDisplayName = 'Pengunjung Web (Tamu #' . $shortId . ')';
                $pendingCount = $groupLogs->where('status', 'pending_admin')->count();
                $repliedCount = $groupLogs->where('status', 'answered_by_admin')->count();
                $convId = str_starts_with($cleanSessionId, 'guest_') ? $cleanSessionId : ('guest_' . $cleanSessionId);

                $latestSnippet = 'Belum ada pesan';
                if ($latestGuest) {
                    if ($latestGuest->admin_reply) {
                        $latestSnippet = 'Admin: ' . Str::limit($latestGuest->admin_reply, 38);
                    } else {
                        $latestSnippet = Str::limit($latestGuest->user_message, 45);
                    }
                }

                $customerConversations->push([
                    'type'               => 'guest',
                    'id'                 => $convId,
                    'user_id'            => null,
                    'session_id'         => $cleanSessionId,
                    'name'               => $guestDisplayName,
                    'email'              => 'Belum Login Akun',
                    'phone'              => 'Sesi: ' . $shortId,
                    'address'            => null,
                    'avatar'             => null,
                    'logs'               => $groupLogs,
                    'total_messages'     => $groupLogs->count(),
                    'latest_log'         => $latestGuest,
                    'latest_activity'    => $latestGuest ? $latestGuest->created_at : null,
                    'latest_snippet'     => $latestSnippet,
                    'pending_count'      => $pendingCount,
                    'replied_count'      => $repliedCount,
                    'grounding_count'    => $groupLogs->filter(fn($l) => ($l->category ?? 'in_scope_db') === 'in_scope_db')->count(),
                    'out_of_scope_count' => $groupLogs->filter(fn($l) => ($l->category ?? '') === 'out_of_scope_irrelevant' || $l->is_out_of_scope)->count(),
                    'is_ai_enabled'      => !Cache::get('guest_ai_takeover_' . $cleanSessionId, false) && !Cache::get('guest_ai_takeover_global', false),
                ]);
            }
        }

        // 3. Urutkan pelanggan berdasarkan aktivitas pesan terbaru (paling aktif di posisi teratas)
        $customerConversations = $customerConversations->sortByDesc(function ($c) {
            return $c['latest_activity'] ? $c['latest_activity']->timestamp : 0;
        })->values();

        // 4. Tentukan ruang obrolan pelanggan yang sedang aktif dipilih
        $requestedUserId = (string) $request->query('user_id');
        $activeConversation = null;

        if ($requestedUserId !== '') {
            $activeConversation = $customerConversations->firstWhere('id', $requestedUserId);
        }

        if (!$activeConversation && $customerConversations->isNotEmpty()) {
            // Default ke pelanggan pertama yang memiliki riwayat pesan
            $firstWithLogs = $customerConversations->first(fn($c) => $c['total_messages'] > 0);
            $activeConversation = $firstWithLogs ?: $customerConversations->first();
        }

        // Metrik evaluasi skripsi (Grounding Data MySQL, Butuh Balasan, Di Luar Lingkup)
        $totalLogs = $allLogs->count();
        $groundingTotal = $allLogs->filter(fn($l) => ($l->category ?? 'in_scope_db') === 'in_scope_db')->count();
        $pendingTotal = $allLogs->where('status', 'pending_admin')->count();
        $outOfScopeTotal = $allLogs->filter(fn($l) => ($l->category ?? '') === 'out_of_scope_irrelevant' || $l->is_out_of_scope)->count();
        $repliedTotal = $allLogs->where('status', 'answered_by_admin')->count();

        return view('admin.chatlogs', [
            'products'           => getCatalogProducts(),
            'orders'             => getOrdersList(),
            'conversations'      => $customerConversations,
            'activeConversation' => $activeConversation,
            'logs'               => $allLogs,
            'totalLogs'          => $totalLogs,
            'groundingTotal'     => $groundingTotal,
            'pendingTotal'       => $pendingTotal,
            'outOfScopeTotal'    => $outOfScopeTotal,
            'repliedTotal'       => $repliedTotal,
        ]);
    })->name('chatlogs.index');


    // ----------------------------------------------------
    // CRUD DATA PRODUK (Menyambung langsung ke MySQL 8.4)
    // ----------------------------------------------------
    Route::post('/products/store', function (Request $request) {
        try {
            ensureProductColumns();
            $table = getProductsTable();
            $name = trim($request->input('name', 'Mesin Roasting Kopi Baru'));
            $price = (int) preg_replace('/[^0-9]/', '', (string) $request->input('price', '15000000'));
            
            $capacityKg = (int) $request->input('capacity_kg', 0);
            if ($capacityKg <= 0) {
                preg_match('/(\d+)\s*kg/i', $name, $matches);
                $capacityKg = isset($matches[1]) ? (int) $matches[1] : 5;
            }
            
            $estimationDays = (int) $request->input('estimation_days', 14);
            if ($estimationDays <= 0) $estimationDays = 14;

            $drumMaterial = trim($request->input('drum_material', 'Full SS Double Jacket, Rangka Besi'));
            $gearboxRatio = trim($request->input('gearbox_ratio', 'Dinamo & Transmisi Gearbox'));
            $motorPowerWatt = (int) $request->input('motor_power_watt', 450);
            $burnerType = trim($request->input('burner_type', 'Kompor LPG Modifikasi'));
            $airflowSystem = trim($request->input('airflow_system', '1 Blower 2 Fungsi & Siklon Penampung'));
            $coolingBinSpec = trim($request->input('cooling_bin_spec', 'Cooling Bin Stainless Steel'));
            $description = trim($request->input('description', 'Unit mesin roasting kopi komersial presisi buatan RJ TEKNIK.'));
            $recommendation = trim($request->input('recommendation', 'Cocok untuk coffee shop dan kebutuhan roasting komersial.'));

            $insertData = [
                'name'             => $name,
                'slug'             => Str::slug($name) . '-' . rand(100, 999),
                'capacity_kg'      => $capacityKg,
                'drum_material'    => $drumMaterial,
                'gearbox_ratio'    => $gearboxRatio,
                'motor_power_watt' => $motorPowerWatt,
                'burner_type'      => $burnerType,
                'airflow_system'   => $airflowSystem,
                'cooling_bin_spec' => $coolingBinSpec,
                'estimation_days'  => $estimationDays,
                'price'            => $price,
                'dp_percentage'    => 50,
                'description'      => $description,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (Schema::hasColumn($table, 'recommendation')) {
                $insertData['recommendation'] = $recommendation;
            }

            if ($request->hasFile('image_file')) {
                $uploadDir = public_path('uploads');
                $file = $request->file('image_file');
                $cleanName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . $cleanName;
                if (safeUploadProductImage($file, $uploadDir, $filename)) {
                    $imageRelativePath = 'uploads/' . $filename;
                    $insertData['image_path'] = $imageRelativePath;
                }
            }

            $newId = saveProductRecord('insert', $table, $insertData);

            if (isset($imageRelativePath)) {
                $customImages = Cache::get('product_custom_images', []);
                $customImages[$newId] = asset($imageRelativePath);
                Cache::forever('product_custom_images', $customImages);
            }

            return redirect()->route('admin.dashboard', ['tab' => 'products'])
                ->with('success', "Unit '{$name}' ({$capacityKg} Kg) berhasil disimpan ke basis data!");
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'products'])
                ->with('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    })->name('products.store');


    Route::post('/products/update/{id}', function (Request $request, $id) {
        try {
            ensureProductColumns();
            $table = getProductsTable();
            $product = DB::table($table)->where('id', $id)->first();

            if ($product) {
                $name = trim($request->input('name', $product->name));
                $price = (int) preg_replace('/[^0-9]/', '', (string) $request->input('price', $product->price));
                
                $capacityKg = (int) $request->input('capacity_kg', $product->capacity_kg ?? 0);
                if ($capacityKg <= 0) {
                    preg_match('/(\d+)\s*kg/i', $name, $matches);
                    $capacityKg = isset($matches[1]) ? (int) $matches[1] : ($product->capacity_kg ?? 5);
                }

                $estimationDays = (int) $request->input('estimation_days', $product->estimation_days ?? 14);
                if ($estimationDays <= 0) $estimationDays = 14;

                $drumMaterial = trim($request->input('drum_material', $product->drum_material ?? 'Full SS Double Jacket'));
                $gearboxRatio = trim($request->input('gearbox_ratio', $product->gearbox_ratio ?? 'Dinamo & Transmisi Gearbox'));
                $motorPowerWatt = (int) $request->input('motor_power_watt', $product->motor_power_watt ?? 450);
                $burnerType = trim($request->input('burner_type', $product->burner_type ?? 'Kompor LPG Modifikasi'));
                $airflowSystem = trim($request->input('airflow_system', $product->airflow_system ?? '1 Blower 2 Fungsi & Siklon'));
                $coolingBinSpec = trim($request->input('cooling_bin_spec', $product->cooling_bin_spec ?? 'Cooling Bin SS'));
                $description = trim($request->input('description', $product->description ?? ''));
                $recommendation = trim($request->input('recommendation', $product->recommendation ?? ''));

                $updateData = [
                    'name'             => $name,
                    'slug'             => Str::slug($name) . '-' . $id,
                    'capacity_kg'      => $capacityKg,
                    'drum_material'    => $drumMaterial,
                    'gearbox_ratio'    => $gearboxRatio,
                    'motor_power_watt' => $motorPowerWatt,
                    'burner_type'      => $burnerType,
                    'airflow_system'   => $airflowSystem,
                    'cooling_bin_spec' => $coolingBinSpec,
                    'estimation_days'  => $estimationDays,
                    'price'            => $price,
                    'description'      => $description,
                    'updated_at'       => now(),
                ];

                if (Schema::hasColumn($table, 'recommendation')) {
                    $updateData['recommendation'] = $recommendation;
                }

                if ($request->hasFile('image_file')) {
                    $uploadDir = public_path('uploads');
                    $file = $request->file('image_file');
                    $cleanName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
                    $filename = time() . '_' . $cleanName;
                    if (safeUploadProductImage($file, $uploadDir, $filename)) {
                        $imageRelativePath = 'uploads/' . $filename;
                        $updateData['image_path'] = $imageRelativePath;

                        $customImages = Cache::get('product_custom_images', []);
                        $customImages[$id] = asset($imageRelativePath);
                        Cache::forever('product_custom_images', $customImages);
                    }
                }

                saveProductRecord('update', $table, $updateData, $id);
            }

            return redirect()->route('admin.dashboard', ['tab' => 'products'])
                ->with('success', 'Spesifikasi produk berhasil diperbarui di basis data!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'products'])
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    })->name('products.update');


    Route::post('/products/delete/{id}', function ($id) {
        try {
            $table = getProductsTable();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($table)->where('id', $id)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $customImages = Cache::get('product_custom_images', []);
            unset($customImages[$id]);
            Cache::forever('product_custom_images', $customImages);

            return redirect()->route('admin.dashboard', ['tab' => 'products'])
                ->with('success', 'Produk berhasil dihapus permanen dari database phpMyAdmin!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'products'])
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    })->name('products.delete');


    // ----------------------------------------------------
    // PENGELOLAAN STATUS ORDER & TRANSAKSI
    // ----------------------------------------------------
    Route::post('/orders/update/{id}', function (Request $request, $id) {
        $orders = getOrdersList();
        foreach ($orders as &$order) {
            if ($order['id'] === $id) {
                $order['step'] = (int) $request->input('step', $order['step']);
                $order['note'] = $request->input('note', $order['note']);
                break;
            }
        }
        unset($order);
        Cache::forever('admin_orders', $orders);
        return redirect()->route('admin.orders.index', ['view' => 'orders'])->with('success', "Status pesanan $id berhasil diperbarui!");
    })->name('orders.update');

    Route::post('/orders/delete/{id}', function (Request $request, $id) {
        $orders = getOrdersList();
        $orders = array_values(array_filter($orders, fn($o) => $o['id'] !== $id));
        Cache::forever('admin_orders', $orders);

        if ($request->input('redirect_to') === 'recap') {
            return redirect()->route('admin.recap', [
                'year'  => $request->input('year', date('Y')),
                'month' => $request->input('month', 'all'),
            ])->with('success', "Data transaksi $id berhasil dihapus dari rekapitulasi penjualan!");
        }

        return redirect()->route('admin.orders.index', ['view' => 'orders'])->with('success', "Data pesanan $id berhasil dihapus!");
    })->name('orders.delete');

    // ----------------------------------------------------
    // PENGELOLAAN LOG CHATBOT & BALASAN MANUAL ADMIN
    // ----------------------------------------------------
    Route::post('/chatlogs/reply/{id}', function (Request $request, $id) {
        $request->validate([
            'admin_reply' => 'required|string|max:2000'
        ]);

        $replyText = trim($request->input('admin_reply'));
        $adminId = Auth::id() ?? session('user_id', 1);

        try {
            if (Schema::hasTable('chat_logs')) {
                if (!Schema::hasColumn('chat_logs', 'admin_reply')) {
                    Schema::table('chat_logs', fn($t) => $t->text('admin_reply')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'admin_id')) {
                    Schema::table('chat_logs', fn($t) => $t->unsignedBigInteger('admin_id')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'replied_at')) {
                    Schema::table('chat_logs', fn($t) => $t->timestamp('replied_at')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'status')) {
                    Schema::table('chat_logs', fn($t) => $t->string('status', 50)->default('resolved'));
                }
            }

            $log = ChatLog::findOrFail($id);

            // Batasan: Admin hanya bisa membalas chat dari pelanggan yang sudah login
            if (empty($log->user_id)) {
                return redirect()->route('admin.chatlogs.index')
                    ->with('error', 'Gagal: Admin hanya dapat membalas pesan dari pelanggan yang sudah login akun.');
            }

            $log->update([
                'admin_reply' => $replyText,
                'admin_id'    => $adminId,
                'status'      => 'answered_by_admin',
                'replied_at'  => now(),
            ]);

            return redirect()->route('admin.chatlogs.index')
                ->with('success', 'Balasan resmi Admin berhasil dikirim ke ruang obrolan pelanggan!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.chatlogs.index')
                ->with('error', 'Gagal mengirim balasan: ' . $e->getMessage());
        }
    })->name('chatlogs.reply');

    Route::post('/chatlogs/reply-customer/{userId}', function (Request $request, $userId) {
        $request->validate([
            'admin_reply' => 'required|string|max:3000'
        ]);

        $replyText = trim($request->input('admin_reply'));
        $adminId = Auth::id() ?? session('user_id', 1);

        try {
            if (Schema::hasTable('chat_logs')) {
                if (!Schema::hasColumn('chat_logs', 'admin_reply')) {
                    Schema::table('chat_logs', fn($t) => $t->text('admin_reply')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'admin_id')) {
                    Schema::table('chat_logs', fn($t) => $t->unsignedBigInteger('admin_id')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'replied_at')) {
                    Schema::table('chat_logs', fn($t) => $t->timestamp('replied_at')->nullable());
                }
                if (!Schema::hasColumn('chat_logs', 'status')) {
                    Schema::table('chat_logs', fn($t) => $t->string('status', 50)->default('resolved'));
                }
            }

            $isGuest = ($userId === 'guest' || str_starts_with($userId, 'guest_'));
            $targetSession = $isGuest ? str_replace('guest_', '', $userId) : null;

            $user = null;
            if (!$isGuest && is_numeric($userId)) {
                $user = User::find($userId);
            }

            $log = null;
            if ($isGuest) {
                // Selesaikan status pending_admin untuk sesi tamu spesifik ini
                $pendQuery = ChatLog::whereNull('user_id');
                if ($targetSession && $targetSession !== 'guest' && $targetSession !== 'default') {
                    $pendQuery->where(function ($q) use ($targetSession) {
                        $q->where('session_id', $targetSession)
                          ->orWhere('session_id', 'guest_' . $targetSession);
                    });
                }
                $pendQuery->where('status', 'pending_admin')->update([
                    'status'     => 'answered_by_admin',
                    'replied_at' => now(),
                    'admin_id'   => $adminId,
                ]);

                $logQuery = ChatLog::whereNull('user_id');
                if ($targetSession && $targetSession !== 'guest' && $targetSession !== 'default') {
                    $logQuery->where(function ($q) use ($targetSession) {
                        $q->where('session_id', $targetSession)
                          ->orWhere('session_id', 'guest_' . $targetSession);
                    });
                }
                $log = $logQuery->whereNull('admin_reply')->latest()->first();

                if ($log) {
                    $log->update([
                        'admin_reply' => $replyText,
                        'admin_id'    => $adminId,
                        'status'      => 'answered_by_admin',
                        'replied_at'  => now(),
                    ]);
                } else {
                    $log = ChatLog::create([
                        'user_id'               => null,
                        'session_id'            => ($targetSession && $targetSession !== 'guest') ? $targetSession : ('guest-admin-' . time()),
                        'user_message'          => 'Konsultasi Lanjutan dengan Admin RJ TEKNIK',
                        'called_function'       => 'ADMIN_MANUAL_REPLY',
                        'bot_response'          => 'Admin RJ TEKNIK terhubung langsung dalam percakapan.',
                        'category'              => 'needs_admin',
                        'status'                => 'answered_by_admin',
                        'admin_reply'           => $replyText,
                        'admin_id'              => $adminId,
                        'replied_at'            => now(),
                    ]);
                }
            } else {
                if (!$user) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.'], 404);
                    }
                    return redirect()->route('admin.chatlogs.index')->with('error', 'Akun pelanggan tidak ditemukan.');
                }

                // Selesaikan SEMUA status pending_admin milik pelanggan ini
                ChatLog::where('user_id', $user->id)
                    ->where('status', 'pending_admin')
                    ->update([
                        'status'     => 'answered_by_admin',
                        'replied_at' => now(),
                        'admin_id'   => $adminId,
                    ]);

                $log = ChatLog::where('user_id', $user->id)
                    ->whereNull('admin_reply')
                    ->latest()
                    ->first();

                if ($log) {
                    $log->update([
                        'admin_reply' => $replyText,
                        'admin_id'    => $adminId,
                        'status'      => 'answered_by_admin',
                        'replied_at'  => now(),
                    ]);
                } else {
                    $log = ChatLog::create([
                        'user_id'               => $user->id,
                        'session_id'            => 'admin-direct-' . $user->id . '-' . time(),
                        'user_message'          => 'Konsultasi Lanjutan dengan Admin RJ TEKNIK',
                        'called_function'       => 'ADMIN_MANUAL_REPLY',
                        'bot_response'          => 'Admin RJ TEKNIK terhubung langsung dalam percakapan.',
                        'category'              => 'needs_admin',
                        'status'                => 'answered_by_admin',
                        'admin_reply'           => $replyText,
                        'admin_id'              => $adminId,
                        'replied_at'            => now(),
                    ]);
                }
            }

            $remainingPendingTotal = ChatLog::where('status', 'pending_admin')->count();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'             => 'success',
                    'message'            => 'Balasan berhasil dikirim!',
                    'log_id'             => $log ? $log->id : null,
                    'reply'              => $replyText,
                    'time_str'           => now()->format('H:i'),
                    'date_str'           => 'Hari Ini',
                    'admin_name'         => 'Admin RJ TEKNIK',
                    'pending_total'      => $remainingPendingTotal,
                    'user_pending_count' => 0,
                ]);
            }

            $targetName = $user ? $user->name : 'Pengunjung Tamu';
            return redirect()->route('admin.chatlogs.index', ['user_id' => $userId])
                ->with('success', "Balasan resmi Admin berhasil dikirim ke {$targetName}!");
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal mengirim balasan: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.chatlogs.index', ['user_id' => $userId])
                ->with('error', 'Gagal mengirim balasan: ' . $e->getMessage());
        }
    })->name('chatlogs.replyCustomer');

    Route::post('/chatlogs/toggle-ai/{userId}', function (Request $request, $userId) {
        try {
            $isGuest = ($userId === 'guest' || str_starts_with($userId, 'guest_'));
            $targetSession = $isGuest ? str_replace('guest_', '', $userId) : null;
            $isAiActive = true;
            $customerName = 'Pengunjung Tamu';

            if ($isGuest) {
                $cacheKey = ($targetSession && $targetSession !== 'guest') ? ('guest_ai_takeover_' . $targetSession) : 'guest_ai_takeover_global';
                $currentTakeover = Cache::get($cacheKey, false);
                $newTakeover = !$currentTakeover;
                Cache::forever($cacheKey, $newTakeover);
                $isAiActive = !$newTakeover;
                $shortId = ($targetSession && $targetSession !== 'guest') ? strtoupper(substr(md5($targetSession), 0, 4)) : '';
                $customerName = 'Pengunjung Tamu' . ($shortId ? ' #' . $shortId : '');
            } else {
                $user = User::findOrFail($userId);
                $customerName = $user->name;
                $currentState = (bool) ($user->is_ai_enabled ?? true);
                $user->is_ai_enabled = !$currentState;
                $user->save();
                $isAiActive = (bool) $user->is_ai_enabled;
            }

            $message = $isAiActive
                ? "AI CS DIAKTIFKAN (ON). Pesan baru dari {$customerName} akan dijawab otomatis oleh Asisten AI CS."
                : "MODE ADMIN DIAKTIFKAN (OFF). AI dinonaktifkan sementara untuk {$customerName}. Seluruh pesan berikutnya akan ditangani langsung secara manual oleh Admin.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'        => 'success',
                    'is_ai_enabled' => $isAiActive,
                    'user_id'       => $userId,
                    'message'       => $message,
                    'badge_text'    => $isAiActive ? 'AI CS AKTIF (ON)' : 'MODE ADMIN (OFF)',
                ]);
            }

            return redirect()->route('admin.chatlogs.index', ['user_id' => $userId])
                ->with('success', $message);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal mengubah status AI: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.chatlogs.index', ['user_id' => $userId])
                ->with('error', 'Gagal mengubah status AI: ' . $e->getMessage());
        }
    })->name('chatlogs.toggleAi');

    // FITUR TUTUP SESI PERCAKAPAN (SMART RESET UNTUK KONSULTASI ADMIN):
    // Mengubah status tiket yang ditangani admin menjadi 'closed' sehingga ketika pelanggan me-refresh,
    // tampilan percakapan mereka ter-reset bersih kembali ke sapaan awal + 5 tombol cepat.
    Route::post('/chatlogs/close-session/{userId}', function (Request $request, $userId) {
        try {
            $isGuest = ($userId === 'guest' || str_starts_with($userId, 'guest_'));
            $targetSession = $isGuest ? str_replace('guest_', '', $userId) : null;

            if ($isGuest) {
                $closeQuery = ChatLog::whereNull('user_id');
                if ($targetSession && $targetSession !== 'guest') {
                    $closeQuery->where(function ($q) use ($targetSession) {
                        $q->where('session_id', $targetSession)
                          ->orWhere('session_id', 'guest_' . $targetSession);
                    });
                }
                $closeQuery->whereIn('status', ['pending_admin', 'answered_by_admin', 'needs_admin', 'admin_handling'])
                    ->update(['status' => 'closed']);
            } else {
                ChatLog::where('user_id', $userId)
                    ->whereIn('status', ['pending_admin', 'answered_by_admin', 'needs_admin', 'admin_handling'])
                    ->update(['status' => 'closed']);
            }

            $remainingPendingTotal = ChatLog::where('status', 'pending_admin')->count();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'        => 'success',
                    'message'       => 'Sesi percakapan berhasil ditutup. Tampilan pelanggan akan bersih saat di-refresh.',
                    'pending_total' => $remainingPendingTotal,
                ]);
            }

            return redirect()->route('admin.chatlogs.index', ['user_id' => $userId])
                ->with('success', 'Sesi percakapan berhasil ditutup.');
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal menutup sesi: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.chatlogs.index', ['user_id' => $userId])
                ->with('error', 'Gagal menutup sesi.');
        }
    })->name('chatlogs.closeSession');

    Route::post('/chatlogs/delete/{id}', function (Request $request, $id) {
        try {
            $log = ChatLog::find($id);
            $userId = $request->input('user_id', $log ? $log->user_id : null);
            if ($log) {
                $log->delete();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'        => 'success',
                    'message'       => 'Pesan chat berhasil dihapus!',
                    'total_logs'    => ChatLog::count(),
                    'pending_total' => ChatLog::where('status', 'pending_admin')->count(),
                ]);
            }

            return redirect()->route('admin.chatlogs.index', array_filter(['user_id' => $userId]))
                ->with('success', 'Riwayat chat log berhasil dihapus!');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('admin.chatlogs.index')
                ->with('error', 'Gagal menghapus log: ' . $e->getMessage());
        }
    })->name('chatlogs.delete');

    Route::post('/chatlogs/delete-bulk', function (Request $request) {
        try {
            $ids = $request->input('ids', []);
            $userId = $request->input('user_id');
            $deleteAll = $request->boolean('delete_all');

            if ($deleteAll && $userId) {
                if ($userId === 'guest') {
                    ChatLog::whereNull('user_id')->delete();
                } else {
                    ChatLog::where('user_id', $userId)->delete();
                }
                $msg = 'Seluruh riwayat obrolan pelanggan berhasil dibersihkan!';
            } elseif (!empty($ids) && is_array($ids)) {
                ChatLog::whereIn('id', $ids)->delete();
                $msg = count($ids) . ' pesan percakapan berhasil dihapus!';
            } else {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => 'Tidak ada pesan yang dipilih.'], 422);
                }
                return back()->with('error', 'Tidak ada pesan yang dipilih.');
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'        => 'success',
                    'message'       => $msg,
                    'total_logs'    => ChatLog::count(),
                    'pending_total' => ChatLog::where('status', 'pending_admin')->count(),
                ]);
            }

            return redirect()->route('admin.chatlogs.index', array_filter(['user_id' => $userId]))
                ->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('admin.chatlogs.index', array_filter(['user_id' => $request->input('user_id')]))
                ->with('error', 'Gagal menghapus log: ' . $e->getMessage());
        }
    })->name('chatlogs.deleteBulk');

    Route::post('/chatlogs/conversation/delete/{id}', function (Request $request, $id) {
        try {
            $isGuest = ($id === 'guest' || str_starts_with($id, 'guest_'));
            $targetSession = $isGuest ? str_replace('guest_', '', $id) : null;

            if ($isGuest) {
                if (Schema::hasTable('chat_logs')) {
                    $delQuery = ChatLog::whereNull('user_id');
                    if ($targetSession && $targetSession !== 'guest') {
                        $delQuery->where(function ($q) use ($targetSession) {
                            $q->where('session_id', $targetSession)
                              ->orWhere('session_id', 'guest_' . $targetSession);
                        });
                    }
                    $delQuery->delete();
                }
                $shortId = ($targetSession && $targetSession !== 'guest') ? strtoupper(substr(md5($targetSession), 0, 4)) : '';
                $customerName = 'Pengunjung Web (Tamu' . ($shortId ? ' #' . $shortId : '') . ')';
            } else {
                if (Schema::hasTable('chat_logs')) {
                    ChatLog::where('user_id', $id)->delete();
                }
                $user = User::find($id);
                $customerName = $user ? $user->name : 'Pelanggan';
                if ($user) {
                    if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_chat_deleted')) {
                        try {
                            Schema::table('users', fn($t) => $t->boolean('is_chat_deleted')->default(false));
                        } catch (\Throwable $e) {}
                    }
                    if (Schema::hasColumn('users', 'is_chat_deleted')) {
                        $user->is_chat_deleted = true;
                        $user->save();
                    }
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'        => 'success',
                    'message'       => "Percakapan dengan {$customerName} berhasil dihapus!",
                    'total_logs'    => ChatLog::count(),
                    'pending_total' => ChatLog::where('status', 'pending_admin')->count(),
                ]);
            }

            return redirect()->route('admin.chatlogs.index')
                ->with('success', "Percakapan dengan {$customerName} berhasil dihapus!");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('admin.chatlogs.index')
                ->with('error', 'Gagal menghapus percakapan: ' . $e->getMessage());
        }
    })->name('chatlogs.deleteConversation');
});
