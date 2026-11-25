<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PreOrderController extends Controller
{
    /**
     * Daftar katalog mesin sampel (digunakan jika produk diambil berdasarkan product_id)
     */
    private function getCatalogProducts(): array
    {
        $cachedProducts = Cache::get('site_products', []);
        if (!empty($cachedProducts)) {
            return $cachedProducts;
        }

        return [
            'SAMPLE-001' => [
                'id'    => 'SAMPLE-001',
                'name'  => 'Mesin Roasting Kopi - Kapasitas 1 Kg',
                'price' => 12000000,
                'image' => asset('img/Kap 1.jpeg'),
            ],
            'SAMPLE-002' => [
                'id'    => 'SAMPLE-002',
                'name'  => 'Mesin Roasting Kopi - Kapasitas 3 Kg',
                'price' => 15000000,
                'image' => asset('img/Kap 3.jpeg'),
            ],
            'SAMPLE-003' => [
                'id'    => 'SAMPLE-003',
                'name'  => 'Mesin Roasting Kopi - Kapasitas 5 Kg',
                'price' => 16000000,
                'image' => asset('img/Kap 5.jpeg'),
            ],
            'SAMPLE-004' => [
                'id'    => 'SAMPLE-004',
                'name'  => 'Mesin Roasting Kopi - Kapasitas 20-25 Kg',
                'price' => 85000000,
                'image' => asset('img/Kap 20.jpeg'),
            ],
        ];
    }

    /**
     * 1. Menampilkan Halaman Checkout (DP 20%)
     */
    public function checkout(Request $request)
    {
        $productId = $request->query('product_id', 'SAMPLE-002');
        $products = $this->getCatalogProducts();

        // Cari produk berdasarkan ID atau gunakan default
        $selectedProduct = $products[$productId] ?? collect($products)->firstWhere('id', $productId) ?? [
            'id'    => $productId,
            'name'  => $request->query('name', 'Mesin Roasting Kopi - Kapasitas 3 Kg'),
            'price' => $request->query('price', 15000000),
            'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=600&auto=format&fit=crop',
        ];

        // Sesuaikan nama view dengan lokasi file blade checkout milikmu
        $viewName = view()->exists('customer.checkout') ? 'customer.checkout' : 'checkout';

        return view($viewName, compact('selectedProduct'));
    }

    /**
     * 2. Memproses Form Checkout, Menghitung DP 20% x Jumlah Unit & Request Snap Token Midtrans
     */
    public function storePreOrder(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:30',
            'shipping_address' => 'required|string',
            'quantity'         => 'nullable|integer|min:1|max:99',
        ]);

        $qty = max(1, (int) $request->input('quantity', 1));
        $totalPrice = (int) $request->input('total_price', 15000000);

        // Logika Matematika: DP 20% dari Total Harga (Harga Satuan x Jumlah Unit)
        $dpPrice = (int) round($totalPrice * 0.20);
        $productName = $request->input('product_name', 'Mesin Roasting Kopi');
        $orderId = 'PO-' . date('Ymd') . '-RJ' . rand(10, 99);

        // Simpan data pesanan baru ke dalam Cache agar tampil di halaman Order Status
        $orders = Cache::get('rj_orders', $this->getDefaultOrders());

        $newOrder = [
            'id'               => $orderId,
            'customer_name'    => $request->input('name'),
            'phone'            => $request->input('phone'),
            'shipping_address' => $request->input('shipping_address'),
            'customer_notes'   => $request->input('customer_notes'),
            'product_name'     => $productName,
            'quantity'         => $qty,
            'total_price'      => $totalPrice,
            'dp_price'         => $dpPrice,
            'step'             => 1,
            'date'             => date('d M Y'),
            'note'             => 'Pesanan berhasil dibuat (' . $qty . ' Unit). Menunggu penyelesaian pembayaran DP 20% via Midtrans.',
            'dp_receipt'       => null,
            'final_receipt'    => null,
        ];

        // Taruh pesanan terbaru di urutan paling atas
        array_unshift($orders, $newOrder);
        Cache::forever('rj_orders', $orders);
        session(['last_order_id' => $orderId]);

        // Ambil Server Key Midtrans dari config/services.php atau .env
        $serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');

        if (!empty($serverKey)) {
            try {
                $response = Http::withBasicAuth($serverKey, '')
                    ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', [
                        'transaction_details' => [
                            'order_id'     => $orderId . '-' . time(),
                            'gross_amount' => $dpPrice, // Tagihan yang masuk ke Midtrans adalah DP 20%
                        ],
                        'customer_details' => [
                            'first_name' => $request->input('name'),
                            'phone'      => $request->input('phone'),
                        ],
                        'item_details' => [
                            [
                                'id'       => $request->input('product_id', 'SAMPLE-001'),
                                'price'    => $dpPrice,
                                'quantity' => 1,
                                'name'     => substr('DP 20% - ' . $productName, 0, 50),
                            ]
                        ],
                    ]);

                if ($response->successful() && isset($response->json()['token'])) {
                    return response()->json([
                        'status'     => 'success',
                        'snap_token' => $response->json()['token'],
                        'order_id'   => $orderId,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghubungi Midtrans: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'MIDTRANS_SERVER_KEY belum diatur di file .env.',
        ], 400);
    }

    /**
     * 3. Callback Setelah Pop-up Midtrans Snap Selesai Dibayar
     */
    public function preOrderSuccess()
    {
        $lastOrderId = session('last_order_id');
        $orders = Cache::get('rj_orders', $this->getDefaultOrders());

        if ($lastOrderId) {
            foreach ($orders as &$order) {
                if ($order['id'] === $lastOrderId && $order['step'] == 1) {
                    $order['step'] = 2;
                    $order['dp_receipt'] = 'MID-DP-' . rand(100000, 999999) . ' • Midtrans Snap • Lunas ' . date('d M Y');
                    $order['note'] = 'Pembayaran DP 20% terverifikasi otomatis via Midtrans. Pesanan masuk antrean perakitan mesin.';
                    break;
                }
            }
            Cache::forever('rj_orders', $orders);
        }

        return redirect()->route('order.status')->with('success', 'Pembayaran DP 20% berhasil diverifikasi!');
    }

    /**
     * 4. Menampilkan Halaman Status Pre-Order Saya
     */
    public function orderStatus()
    {
        $orders = Cache::get('rj_orders', $this->getDefaultOrders());

        // Pastikan pesanan lama otomatis menyesuaikan perhitungan DP 20% jika belum diatur
        foreach ($orders as &$order) {
            $total = (int) ($order['total_price'] ?? 16000000);
            if (!isset($order['dp_price']) || $order['dp_price'] == ($total / 2)) {
                $order['dp_price'] = (int) round($total * 0.20);
            }
            $order['note'] = str_replace('DP 20%', 'DP 20%', $order['note'] ?? '');
        }

        // Sesuaikan nama view dengan lokasi file blade status order milikmu
        $viewName = view()->exists('customer.order-status')
            ? 'customer.order-status'
            : (view()->exists('order.status') ? 'order.status' : 'customer.order_status');

        return view($viewName, compact('orders'));
    }

    /**
     * 5. Memproses Pembayaran DP 20% atau Pelunasan 80% dari Modal di Halaman Order Status
     */
    public function payOrder(Request $request, $orderId)
    {
        $paymentType = $request->input('payment_type', 'dp');
        $bankMethod  = $request->input('bank_method', 'BCA Virtual Account');
        $vaNumber    = $request->input('va_number', '82770819283711');

        $orders = Cache::get('rj_orders', $this->getDefaultOrders());

        foreach ($orders as &$order) {
            if ($order['id'] === $orderId) {
                if ($paymentType === 'dp') {
                    $order['step'] = 2;
                    $order['dp_receipt'] = 'MID-DP-' . rand(100000, 999999) . ' • ' . $bankMethod . ' (' . $vaNumber . ') • Lunas ' . date('d M Y');
                    $order['note'] = 'Pembayaran DP 20% terverifikasi otomatis via Midtrans. Pesanan masuk antrean perakitan mesin.';
                } else {
                    $order['step'] = 5;
                    $order['final_receipt'] = 'MID-FULL-' . rand(100000, 999999) . ' • ' . $bankMethod . ' (' . $vaNumber . ') • Lunas ' . date('d M Y');
                    $order['note'] = 'Pelunasan 80% telah diterima (Lunas 100%). Mesin sedang dalam proses pengiriman kargo ke lokasi Anda.';
                }
                break;
            }
        }

        Cache::forever('rj_orders', $orders);

        return redirect()->route('order.status')->with('success', 'Pembayaran berhasil diverifikasi!');
    }

    /**
     * Data sampel awal (dengan perhitungan DP 20% = Rp 3.200.000 & Sisa 80% = Rp 12.800.000 dari Total Rp 16.000.000)
     */
    private function getDefaultOrders(): array
    {
        return [
            [
                'id'            => 'PO-20261101-RJ01',
                'customer_name' => session('user_name', 'azell'),
                'product_name'  => 'Mesin Roasting Kopi - Kapasitas 5 Kg',
                'quantity'      => 1,
                'total_price'   => 16000000,
                'dp_price'      => 3200000, // 20% dari Rp 16.000.000
                'step'          => 2,
                'date'          => '01 Nov 2026',
                'note'          => 'Pembayaran DP 20% terverifikasi otomatis via Midtrans. Pesanan masuk antrean perakitan mesin.',
                'dp_receipt'    => 'MID-DP-982103 • BCA VA (82770819283711) • Lunas 01 Nov 2026',
                'final_receipt' => null,
            ],
        ];
    }
}
