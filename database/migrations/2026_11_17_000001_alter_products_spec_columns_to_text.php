<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            try {
                DB::statement("ALTER TABLE `products` 
                    MODIFY COLUMN `name` VARCHAR(255) NOT NULL,
                    MODIFY COLUMN `drum_material` TEXT NULL,
                    MODIFY COLUMN `gearbox_ratio` TEXT NULL,
                    MODIFY COLUMN `burner_type` TEXT NULL,
                    MODIFY COLUMN `airflow_system` TEXT NULL,
                    MODIFY COLUMN `cooling_bin_spec` TEXT NULL");
            } catch (\Throwable $e) {
                // Fallback
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            try {
                DB::statement("ALTER TABLE `products` 
                    MODIFY COLUMN `name` VARCHAR(150) NOT NULL,
                    MODIFY COLUMN `drum_material` VARCHAR(100) NOT NULL,
                    MODIFY COLUMN `gearbox_ratio` VARCHAR(50) NOT NULL,
                    MODIFY COLUMN `burner_type` VARCHAR(100) NOT NULL,
                    MODIFY COLUMN `airflow_system` VARCHAR(100) NOT NULL,
                    MODIFY COLUMN `cooling_bin_spec` VARCHAR(100) NOT NULL");
            } catch (\Throwable $e) {
                // Fallback
            }
        }
    }
};
