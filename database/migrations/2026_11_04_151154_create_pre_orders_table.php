<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_price', 15, 2);
            $table->decimal('dp_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->enum('production_status', [
                'Menunggu Pembayaran DP',
                'Proses Perakitan',
                'Siap Pelunasan',
                'Dikirim'
            ])->default('Menunggu Pembayaran DP');
            $table->enum('payment_status', [
                'Pending',
                'DP Lunas',
                'Lunas'
            ])->default('Pending');
            $table->text('shipping_address');
            $table->text('customer_notes')->nullable();
            $table->date('estimated_completion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};