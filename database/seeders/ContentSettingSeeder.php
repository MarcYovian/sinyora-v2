<?php

namespace Database\Seeders;

use App\Models\ContentSetting;
use Illuminate\Database\Seeder;

class ContentSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $homeContent = [
            // Hero Section
            ['section' => 'hero', 'key' => 'title', 'type' => 'text', 'value' => 'Selamat Datang di Kapel St. Yohanes Rasul'],
            ['section' => 'hero', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Di bawah naungan Paroki Santo Yusup Karangpilang, Surabaya'],
            ['section' => 'hero', 'key' => 'button-text', 'type' => 'text', 'value' => 'Lihat Jadwal Misa'],
            ['section' => 'hero', 'key' => 'button-url', 'type' => 'text', 'value' => '#jadwal-misa'],
            ['section' => 'hero', 'key' => 'background-image', 'type' => 'image', 'value' => 'images/1.jpg'],

            // Welcome Section
            ['section' => 'welcome', 'key' => 'title', 'type' => 'text', 'value' => 'Kapel St. Yohanes Rasul'],
            ['section' => 'welcome', 'key' => 'content', 'type' => 'textarea', 'value' => 'Selamat datang di situs resmi Kapel St. Yohanes Rasul. Kami berharap informasi di sini dapat membantu umat semakin dekat dengan Kristus. Salam damai dalam Kristus. Semoga Kapel St. Yohanes Rasul menjadi tempat yang membawa berkat dan sukacita bagi umat semua.'],
            ['section' => 'welcome', 'key' => 'button-text', 'type' => 'text', 'value' => 'Pelayanan Kami'],
            ['section' => 'welcome', 'key' => 'button-url', 'type' => 'text', 'value' => '#pelayanan'],
            ['section' => 'welcome', 'key' => 'image', 'type' => 'image', 'value' => 'images/about.jpg'],

            // Pelayanan Section
            ['section' => 'pelayanan', 'key' => 'title', 'type' => 'text', 'value' => 'Pelayanan Liturgis Kapela'],
            ['section' => 'pelayanan', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Jelajahi dan Temukan Pelayanan Liturgis Kapela St Yohanes Rasul'],
            ['section' => 'pelayanan', 'key' => 'cta-title', 'type' => 'text', 'value' => 'Bergabunglah Dengan Kami'],
            ['section' => 'pelayanan', 'key' => 'cta-content', 'type' => 'textarea', 'value' => 'Mari berpartisipasi dalam pelayanan liturgi Kapel St. Yohanes Rasul'],
            ['section' => 'pelayanan', 'key' => 'cta-button-text', 'type' => 'text', 'value' => 'Hubungi Kami'],
            ['section' => 'pelayanan', 'key' => 'cta-button-url', 'type' => 'text', 'value' => '#contact'],
            ['section' => 'pelayanan', 'key' => 'image', 'type' => 'image', 'value' => 'images/about.jpg'],
        ];

        foreach ($homeContent as $content) {
            ContentSetting::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => $content['section'],
                    'key' => $content['key'],
                ],
                [
                    'type' => $content['type'],
                    'value' => $content['value'],
                ]
            );
        }

        $this->call([
            ServiceContentSeeder::class
        ]);
        $this->command->info('Home page content seeded successfully.');

        $eventContents = [
            // Hero Section
            [
                ['section' => 'hero', 'key' => 'title', 'type' => 'text', 'value' => 'Kalender Kegiatan Kapel'],
                ['section' => 'hero', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Temukan jadwal kegiatan, perayaan liturgi, dan acara khusus di Kapel St. Yohanes Rasul'],
                ['section' => 'hero', 'key' => 'button-text', 'type' => 'text', 'value' => 'Lihat Jadwal Misa'],
                ['section' => 'hero', 'key' => 'button-url', 'type' => 'text', 'value' => '#jadwal-misa'],
                ['section' => 'hero', 'key' => 'background-image', 'type' => 'image', 'value' => 'images/1.jpg'],
            ],
        ];

        foreach ($eventContents as $content) {
            ContentSetting::updateOrCreate(
                [
                    'page' => 'events',
                    'section' => $content['section'],
                    'key' => $content['key'],
                ],
                [
                    'type' => $content['type'],
                    'value' => $content['value'],
                ]
            );
        }
    }
}
