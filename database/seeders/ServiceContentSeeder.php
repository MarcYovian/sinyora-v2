<?php

namespace Database\Seeders;

use App\Models\ServiceContentSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceContentSetting::truncate(); // Kosongkan tabel sebelum mengisi

        $services = [
            ['title' => 'Lektor Pemazmur', 'icon_class' => 'fa-solid fa-book-bible', 'description' => 'Melayani dalam pembacaan Kitab Suci dan Mazmur', 'link' => '#', 'order' => 1],
            ['title' => 'Misdinar', 'icon_class' => 'fa-solid fa-cross', 'description' => 'Melayani dalam perayaan Ekaristi', 'link' => '#', 'order' => 2],
            ['title' => 'PUK', 'icon_class' => 'fa-solid fa-users', 'description' => 'Pelayanan Umum dan Kebersihan', 'link' => '#', 'order' => 3],
            ['title' => 'Paguyuban Organis', 'icon_class' => 'fa-solid fa-hands-praying', 'description' => 'Memimpin nyanyian dalam perayaan liturgi', 'link' => '#', 'order' => 4],
        ];

        foreach ($services as $service) {
            ServiceContentSetting::create($service);
        }
    }
}
