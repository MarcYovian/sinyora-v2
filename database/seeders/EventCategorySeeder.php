<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Kategori khusus untuk Misa & Jadwal Ekaristi
            [
                'name' => 'Misa & Perayaan Ekaristi',
                'slug' => 'misa-ekaristi',
                'color' => '#FFD700', // Emas/Kuning Emas - warna sakral
                'is_active' => true,
                'is_mass_category' => true, // flag untuk jadwal misa
            ],
            [
                'name' => 'Liturgia (Peribadatan)',
                'slug' => 'liturgia',
                'color' => '#d50000', // Merah Tua
                'is_active' => true,
                'is_mass_category' => false,
            ],
            [
                'name' => 'Kerygma (Pewartaan)',
                'slug' => 'kerygma',
                'color' => '#f57f17', // Kuning/Oranye
                'is_active' => true,
                'is_mass_category' => false,
            ],
            [
                'name' => 'Koinonia (Persekutuan)',
                'slug' => 'koinonia',
                'color' => '#00c853', // Hijau Cerah
                'is_active' => true,
                'is_mass_category' => false,
            ],
            [
                'name' => 'Diakonia (Pelayanan)',
                'slug' => 'diakonia',
                'color' => '#2962ff', // Biru
                'is_active' => true,
                'is_mass_category' => false,
            ],
            [
                'name' => 'Martyria (Kesaksian)',
                'slug' => 'martyria',
                'color' => '#6200ea', // Ungu
                'is_active' => true,
                'is_mass_category' => false,
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
