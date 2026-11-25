<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Muqoddim (Admin RJ Gresik)',
                'email' => 'admin@rjgresik.com',
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'address' => 'Kec. Dawar Blandong, Kab. Mojokerto',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ahmad Fauzi (Roastery Nusantara)',
                'email' => 'pelanggan@email.com',
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'address' => 'Jl. Raya Darmo No. 45, Kota Surabaya',
                'role' => 'pelanggan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Data 4 Varian Mesin Roasting Kopi (Sesuai Tabel 3.4 & Gambar 3.14 Bab 3)
        DB::table('products')->insert([
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 1 Kg',
                'slug' => 'mesin-roasting-kopi-1-kg',
                'capacity_kg' => 1,
                'drum_material' => 'Tabung Full SS Double Jacket (Ø 20 x 25 cm), Rangka Besi Siku',
                'gearbox_ratio' => '2 Dinamo Listrik & 2 Transmisi Gearbox Rantai',
                'motor_power_watt' => 250,
                'burner_type' => 'Kompor LPG Modifikasi Panjang',
                'airflow_system' => '1 Blower 2 Fungsi + Penampung Asap & Sampah',
                'cooling_bin_spec' => 'Cooling Bin SS + 4 Tombol Kontrol (Drum, Blower, Cooling, Lampu)',
                'estimation_days' => 14,
                'price' => 15000000,
                'dp_percentage' => 50,
                'image_path' => 'images/roaster-1kg.jpg',
                'description' => 'Dilengkapi checker, kaca pengintai, termometer, dan lampu.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 3 Kg',
                'slug' => 'mesin-roasting-kopi-3-kg',
                'capacity_kg' => 3,
                'drum_material' => 'Plat SS 2 mm (Ø 25 x 35 cm), Rangka Besi 4 Roda, Bodi SS',
                'gearbox_ratio' => 'Dinamo Drum Gearbox 1/2 HP & Cooler 1/4 HP',
                'motor_power_watt' => 450,
                'burner_type' => 'Kompor LPG Modifikasi + Selang & Regulator',
                'airflow_system' => '1 Blower 2 Fungsi + Cyclone Separator Filter',
                'cooling_bin_spec' => 'Cooling Bin SS + 4 Tombol Kontrol & Termometer Drum',
                'estimation_days' => 21,
                'price' => 28000000,
                'dp_percentage' => 50,
                'image_path' => 'images/roaster-3kg.jpg',
                'description' => 'Dilengkapi termometer drum, checker tempel, dan kaca pengintai.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 5-10 Kg',
                'slug' => 'mesin-roasting-kopi-5-10-kg',
                'capacity_kg' => 10,
                'drum_material' => 'Tutup Depan SS 2 mm, Tabung Ø 30 x 50 cm, Rangka Besi',
                'gearbox_ratio' => 'Dinamo Tabung Gearbox 1/2 HP & Cooling 1/4 HP',
                'motor_power_watt' => 550,
                'burner_type' => 'Kompor LPG Modifikasi',
                'airflow_system' => 'Blower Hisap & Siklon Penampung',
                'cooling_bin_spec' => 'Cooling Bin SS Ø 60 x 25 cm + 5 Tombol (+Speed Control)',
                'estimation_days' => 30,
                'price' => 48000000,
                'dp_percentage' => 50,
                'image_path' => 'images/roaster-10kg.jpg',
                'description' => 'Dilengkapi termometer suhu manual, checker tempel, kaca pengintai, lampu, dan speed control.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesin Roasting Kopi Kapasitas 20-25 Kg',
                'slug' => 'mesin-roasting-kopi-20-25-kg',
                'capacity_kg' => 25,
                'drum_material' => 'Full SS 4 mm Double Jacket, Corong & Penutup Full SS, 4 Roda',
                'gearbox_ratio' => '2 Unit Penggerak Dinamo Listrik Transmisi Gearbox',
                'motor_power_watt' => 1100,
                'burner_type' => 'Pipa Gas LPG Modifikasi + Selang & Regulator',
                'airflow_system' => '1 Blower 2 Fungsi & Siklon Sampah',
                'cooling_bin_spec' => 'Full SS Cooling + 5 Tombol Kontrol (+Speed Control)',
                'estimation_days' => 45,
                'price' => 85000000,
                'dp_percentage' => 50,
                'image_path' => 'images/roaster-25kg.jpg',
                'description' => 'Dilengkapi termometer pegas, tusuk checker tempel, kaca pengintai, lampu, dan speed control.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}