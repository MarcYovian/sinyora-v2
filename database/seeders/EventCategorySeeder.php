<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventCategory::create([
            'name' => 'Liturgia (Peribadatan)',
            'color' => '#d50000', // Merah Tua
            'is_active' => 1,
        ]);

        // 2. Kategori untuk kegiatan Pewartaan
        EventCategory::create([
            'name' => 'Kerygma (Pewartaan)',
            'color' => '#f57f17', // Kuning/Oranye
            'is_active' => 1,
        ]);

        // 3. Kategori untuk kegiatan Persekutuan
        EventCategory::create([
            'name' => 'Koinonia (Persekutuan)',
            'color' => '#00c853', // Hijau Cerah
            'is_active' => 1,
        ]);

        // 4. Kategori untuk kegiatan Pelayanan
        EventCategory::create([
            'name' => 'Diakonia (Pelayanan)',
            'color' => '#2962ff', // Biru
            'is_active' => 1,
        ]);

        // 5. Kategori untuk kegiatan Kesaksian
        EventCategory::create([
            'name' => 'Martyria (Kesaksian)',
            'color' => '#6200ea', // Ungu
            'is_active' => 1,
        ]);
    }
}
