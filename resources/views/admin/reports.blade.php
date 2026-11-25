{{-- 
    Laporan Penjualan terpusat di resources/views/admin/orders.blade.php (dengan parameter view=sales)
    File ini bertindak sebagai alias/wrapper resmi agar pencarian file admin rapi dan terstruktur.
--}}
@php
    request()->merge(['view' => 'sales']);
@endphp
@include('admin.orders')
