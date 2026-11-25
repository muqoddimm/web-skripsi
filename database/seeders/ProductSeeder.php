<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        DB::table('products')->insert([
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 1 Kg',
                'slug' => Str::slug('Mesin Roasting Kopi Kapasitas 1 Kg') . '-' . rand(10, 99),
                'capacity_kg' => 1,
                'drum_material' => 'Full SS Double Jacket Ø 20x25 cm, rangka besi siku',
                'gearbox_ratio' => '2 Dinamo Listrik & 2 Transmisi Gearbox Rantai',
                'motor_power_watt' => 450,
                'burner_type' => 'Kompor LPG modifikasi panjang',
                'airflow_system' => '1 Blower 2 Fungsi',
                'cooling_bin_spec' => 'Cooling bin, penampung asap & sampah, 4 tombol panel',
                'estimation_days' => 14,
                'price' => 15000000,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 3 Kg',
                'slug' => Str::slug('Mesin Roasting Kopi Kapasitas 3 Kg') . '-' . rand(10, 99),
                'capacity_kg' => 3,
                'drum_material' => 'Plat SS 2mm Ø 25x35 cm, rangka besi 4 roda',
                'gearbox_ratio' => 'Dinamo Drum Gearbox 1/2 HP & Cooler 1/4 HP',
                'motor_power_watt' => 500,
                'burner_type' => 'Kompor LPG modifikasi + selang & regulator',
                'airflow_system' => '1 Blower 2 Fungsi + Cyclone Separator Filter',
                'cooling_bin_spec' => 'Termometer drum, 4 tombol kontrol',
                'estimation_days' => 21,
                'price' => 28000000,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 5-10 Kg',
                'slug' => Str::slug('Mesin Roasting Kopi Kapasitas 5-10 Kg') . '-' . rand(10, 99),
                'capacity_kg' => 10,
                'drum_material' => 'Tutup depan SS 2mm, Tabung Ø 30x50 cm',
                'gearbox_ratio' => 'Dinamo tabung Gearbox 1/2 HP & Cooling 1/4 HP',
                'motor_power_watt' => 750,
                'burner_type' => 'Kompor LPG modifikasi',
                'airflow_system' => 'Blower Hisap & Siklon Penampung',
                'cooling_bin_spec' => 'Cooling Bin SS Ø 60x25 cm, 5 tombol + Speed Control',
                'estimation_days' => 30,
                'price' => 48000000,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 20-25 Kg',
                'slug' => Str::slug('Mesin Roasting Kopi Kapasitas 20-25 Kg') . '-' . rand(10, 99),
                'capacity_kg' => 25,
                'drum_material' => 'Full SS 4mm Double Jacket, Corong Full SS',
                'gearbox_ratio' => '2 Unit Penggerak Dinamo Listrik Transmisi Gearbox',
                'motor_power_watt' => 1500,
                'burner_type' => 'Pipa Gas LPG Modifikasi + Selang Regulator',
                'airflow_system' => '1 Blower 2 Fungsi & Siklon Sampah',
                'cooling_bin_spec' => 'Termometer pegas, 5 tombol + Speed Control',
                'estimation_days' => 45,
                'price' => 85000000,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
