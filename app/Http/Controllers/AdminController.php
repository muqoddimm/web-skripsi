<?php

namespace App\Http\Controllers;

use App\Models\PreOrder as Order;
use App\Models\Product;
use App\Models\ChatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * ==========================================
     * BAGIAN ADMIN WORKSPACE (TERHUBUNG DATABASE)
     * ==========================================
     */
    public function adminDashboard()
    {
        // Ambil ke-4 produk asli langsung dari tabel `products` di phpMyAdmin
        $dbProducts = Product::orderBy('id', 'asc')->get();

        $products = $dbProducts->map(function ($item) {
            $imgUrl = 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=200&auto=format&fit=crop';
            if (!empty($item->image)) {
                $imgUrl = filter_var($item->image, FILTER_VALIDATE_URL) ? $item->image : asset('storage/' . $item->image);
            }

            return [
                'id'          => $item->id,
                'name'        => $item->name,
                'price'       => (int) $item->price,
                'capacity_kg' => $item->capacity_kg,
                'image'       => $imgUrl,
            ];
        })->toArray();

        // Ambil data pesanan dari tabel `orders`
        $orders = [];
        if (Schema::hasTable('orders')) {
            $orders = Order::latest()->get()->map(function ($item) {
                return [
                    'id'             => $item->order_code,
                    'customer_name'  => $item->customer_name,
                    'customer_email' => $item->user_email ?? '-',
                    'product_name'   => $item->product_name,
                    'quantity'       => $item->quantity,
                    'total_price'    => $item->total_price,
                    'dp_price'       => $item->dp_price,
                    'step'           => $item->step,
                    'date'           => $item->created_at ? $item->created_at->format('d M Y') : date('d M Y'),
                    'note'           => $item->note,
                    'dp_receipt'     => $item->dp_receipt,
                    'final_receipt'  => $item->final_receipt,
                ];
            })->toArray();
        }

        // Ambil data log percakapan dari tabel `chat_logs`
        $logs = Schema::hasTable('chat_logs') ? ChatLog::latest()->get() : collect([]);

        // Cari lokasi view admin yang aktif di proyekmu
        $viewName = view()->exists('admin.dashboard.index')
            ? 'admin.dashboard.index'
            : (view()->exists('admin.dashboard') ? 'admin.dashboard' : 'admin.workspace');

        return view($viewName, compact('products', 'orders', 'logs'));
    }

    /**
     * Tambah Produk Baru ke Tabel `products` di phpMyAdmin
     */
    public function adminStoreProduct(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'price'      => 'required|numeric',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Ekstrak angka kapasitas dari nama jika ada (contoh: "15 Kg" -> 15)
        preg_match('/(\d+)\s*kg/i', $request->name, $matches);
        $capacityKg = isset($matches[1]) ? (int) $matches[1] : 5;

        $data = [
            'name'             => $request->name,
            'slug'             => Str::slug($request->name) . '-' . rand(10, 99),
            'capacity_kg'      => $capacityKg,
            'drum_material'    => $request->input('drum_material', 'Full Stainless Steel'),
            'gearbox_ratio'    => $request->input('gearbox_ratio', 'Dinamo & Transmisi Gearbox'),
            'motor_power_watt' => $request->input('motor_power_watt', 450),
            'burner_type'      => $request->input('burner_type', 'Kompor LPG Modifikasi'),
            'airflow_system'   => $request->input('airflow_system', 'Blower 2 Fungsi & Siklon'),
            'cooling_bin_spec' => $request->input('cooling_bin_spec', 'Cooling Bin Stainless Steel'),
            'estimation_days'  => $request->input('estimation_days', 14),
            'price'            => (int) $request->price,
        ];

        if ($request->hasFile('image_file') && Schema::hasColumn('products', 'image')) {
            $data['image'] = $request->file('image_file')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.dashboard', ['tab' => 'products'])
            ->with('success', 'Produk berhasil ditambahkan ke database phpMyAdmin!');
    }

    /**
     * Edit / Update Produk di Tabel `products` phpMyAdmin
     */
    public function adminUpdateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = [
            'name'  => $request->input('name', $product->name),
            'price' => (int) $request->input('price', $product->price),
        ];

        if ($request->hasFile('image_file') && Schema::hasColumn('products', 'image')) {
            $data['image'] = $request->file('image_file')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.dashboard', ['tab' => 'products'])
            ->with('success', 'Data produk berhasil diperbarui di database!');
    }

    /**
     * Hapus Produk secara Permanen dari Tabel `products` di phpMyAdmin
     */
    public function adminDeleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'products'])
            ->with('success', 'Produk berhasil dihapus dari database phpMyAdmin!');
    }

    /**
     * Update Tahap Pengerjaan Pesanan oleh Admin
     */
    public function adminUpdateOrder(Request $request, $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        $order->update([
            'step' => (int) $request->input('step', $order->step),
            'note' => $request->input('note', $order->note),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'orders'])
            ->with('success', 'Status pesanan ' . $orderCode . ' berhasil diperbarui!');
    }

    /**
     * Hapus Pesanan dari Database oleh Admin
     */
    public function adminDeleteOrder($orderCode)
    {
        Order::where('order_code', $orderCode)->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'orders'])
            ->with('success', 'Pesanan ' . $orderCode . ' berhasil dihapus dari database!');
    }

    /**
     * ==========================================
     * BAGIAN CUSTOMER: CHECKOUT & ORDER STATUS
     * ==========================================
     */
    public function checkout(Request $request)
    {
        $productId = $request->query('product_id');

        // Cari produk di database berdasarkan ID (1, 2, 3, 4) atau berdasarkan slug
        $dbProduct = Product::where('id', $productId)
            ->orWhere('slug', $productId)
            ->first();

        // Jika masih belum ketemu, ambil produk pertama di database
        if (!$dbProduct) {
            $dbProduct = Product::first();
        }

        if ($dbProduct) {
            $imgUrl = 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=600&auto=format&fit=crop';
            if (!empty($dbProduct->image)) {
                $imgUrl = filter_var($dbProduct->image, FILTER_VALIDATE_URL) ? $dbProduct->image : asset('storage/' . $dbProduct->image);
            }

            $selectedProduct = [
                'id'    => $dbProduct->id,
                'name'  => $dbProduct->name,
                'price' => (int) $dbProduct->price,
                'image' => $imgUrl,
            ];
        } else {
            $selectedProduct = [
                'id'    => '1',
                'name'  => 'Mesin Roasting Kopi - Kapasitas 3 Kg',
                'price' => 15000000,
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=600&auto=format&fit=crop',
            ];
        }

        $viewName = view()->exists('customer.checkout') ? 'customer.checkout' : 'checkout';

        return view($viewName, compact('selectedProduct'));
    }

    public function storePreOrder(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:30',
            'shipping_address' => 'required|string',
            'quantity'         => 'nullable|integer|min:1|max:99',
        ]);

        $qty         = max(1, (int) $request->input('quantity', 1));
        $totalPrice  = (int) $request->input('total_price', 15000000);
        $dpPrice     = (int) round($totalPrice * 0.20);
        $productName = $request->input('product_name', 'Mesin Roasting Kopi');
        $orderCode   = 'PO-' . date('Ymd') . '-RJ' . rand(100, 999);

        $order = Order::create([
            'order_code'       => $orderCode,
            'user_email'       => session('user_email'),
            'customer_name'    => $request->input('name'),
            'phone'            => $request->input('phone'),
            'shipping_address' => $request->input('shipping_address'),
            'customer_notes'   => $request->input('customer_notes'),
            'product_id'       => (string) $request->input('product_id', '1'),
            'product_name'     => $productName,
            'quantity'         => $qty,
            'total_price'      => $totalPrice,
            'dp_price'         => $dpPrice,
            'step'             => 1,
            'note'             => 'Pesanan berhasil dibuat (' . $qty . ' Unit). Menunggu penyelesaian pembayaran DP 20% via Midtrans.',
        ]);

        session(['last_order_code' => $order->order_code]);

        $serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');

        if (!empty($serverKey)) {
            try {
                $response = Http::withBasicAuth($serverKey, '')
                    ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', [
                        'transaction_details' => [
                            'order_id'     => $order->order_code . '-' . time(),
                            'gross_amount' => $dpPrice,
                        ],
                        'customer_details' => [
                            'first_name' => $order->customer_name,
                            'phone'      => $order->phone,
                        ],
                        'item_details' => [
                            [
                                'id'       => (string) $order->product_id,
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
                        'order_id'   => $order->order_code,
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

    public function preOrderSuccess()
    {
        $lastOrderCode = session('last_order_code');

        if ($lastOrderCode) {
            $order = Order::where('order_code', $lastOrderCode)->first();
            if ($order && $order->step == 1) {
                $order->update([
                    'step'       => 2,
                    'dp_receipt' => 'MID-DP-' . rand(100000, 999999) . ' • Midtrans Snap • Lunas ' . date('d M Y'),
                    'note'       => 'Pembayaran DP 20% terverifikasi otomatis via Midtrans. Pesanan masuk antrean perakitan mesin.',
                ]);
            }
        }

        return redirect()->route('order.status')->with('success', 'Pembayaran DP 20% berhasil diverifikasi!');
    }

    public function orderStatus()
    {
        $query = Order::latest();

        if (session()->has('user_email')) {
            $query->where(function ($q) {
                $q->where('user_email', session('user_email'))
                    ->orWhereNull('user_email');
            });
        }

        $dbOrders = $query->get();

        $orders = $dbOrders->map(function ($item) {
            return [
                'id'            => $item->order_code,
                'customer_name' => $item->customer_name,
                'product_name'  => $item->product_name,
                'quantity'      => $item->quantity,
                'total_price'   => $item->total_price,
                'dp_price'      => $item->dp_price,
                'step'          => $item->step,
                'date'          => $item->created_at ? $item->created_at->format('d M Y') : date('d M Y'),
                'note'          => $item->note,
                'dp_receipt'    => $item->dp_receipt,
                'final_receipt' => $item->final_receipt,
            ];
        })->toArray();

        $viewName = view()->exists('customer.order-status')
            ? 'customer.order-status'
            : (view()->exists('order.status') ? 'order.status' : 'customer.order_status');

        return view($viewName, compact('orders'));
    }

    public function payOrder(Request $request, $orderCode)
    {
        $paymentType = $request->input('payment_type', 'dp');
        $bankMethod  = $request->input('bank_method', 'BCA Virtual Account');
        $vaNumber    = $request->input('va_number', '82770819283711');

        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if ($paymentType === 'dp') {
            $order->update([
                'step'       => 2,
                'dp_receipt' => 'MID-DP-' . rand(100000, 999999) . ' • ' . $bankMethod . ' (' . $vaNumber . ') • Lunas ' . date('d M Y'),
                'note'       => 'Pembayaran DP 20% terverifikasi otomatis via Midtrans. Pesanan masuk antrean perakitan mesin.',
            ]);
        } else {
            $order->update([
                'step'          => 5,
                'final_receipt' => 'MID-FULL-' . rand(100000, 999999) . ' • ' . $bankMethod . ' (' . $vaNumber . ') • Lunas ' . date('d M Y'),
                'note'          => 'Pelunasan 80% telah diterima (Lunas 100%). Mesin sedang dalam proses pengiriman kargo ke lokasi Anda.',
            ]);
        }

        return redirect()->route('order.status')->with('success', 'Pembayaran berhasil disimpan ke database!');
    }
}
    