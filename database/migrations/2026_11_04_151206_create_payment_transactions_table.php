<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_order_id')->constrained('pre_orders')->cascadeOnDelete();
            $table->string('midtrans_order_id', 100)->unique();
            $table->enum('payment_phase', ['dp', 'pelunasan']);
            $table->string('payment_type', 50)->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->string('snap_token', 255)->nullable();
            $table->enum('transaction_status', [
                'pending',
                'settlement',
                'capture',
                'expire',
                'cancel'
            ])->default('pending');
            $table->string('signature_key', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};