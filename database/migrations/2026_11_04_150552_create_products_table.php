<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->integer('capacity_kg');
            $table->string('drum_material', 100);
            $table->string('gearbox_ratio', 50);
            $table->integer('motor_power_watt');
            $table->string('burner_type', 100);
            $table->string('airflow_system', 100);
            $table->string('cooling_bin_spec', 100);
            $table->integer('estimation_days');
            $table->decimal('price', 15, 2);
            $table->integer('dp_percentage')->default(50);
            $table->string('image_path', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};