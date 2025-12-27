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
        // ======================================
        // HOME PAGE CONTENT
        // ======================================
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
            ['section' => 'pelayanan', 'key' => 'cta-content', 'type' => 'textarea', 'value' => 'Mari berpartisipasi dalam pelayanan liturgi Kapel St. Yohanes Rasul. Bersama-sama kita membangun komunitas yang hidup dalam iman, harapan, dan kasih.'],
            ['section' => 'pelayanan', 'key' => 'cta-button-text', 'type' => 'text', 'value' => 'Hubungi Kami'],
            ['section' => 'pelayanan', 'key' => 'cta-button-url', 'type' => 'text', 'value' => '#contact'],
            ['section' => 'pelayanan', 'key' => 'image', 'type' => 'image', 'value' => 'images/about.jpg'],

            // Jadwal Misa Section
            ['section' => 'jadwal-misa', 'key' => 'title', 'type' => 'text', 'value' => 'Jadwal Misa'],
            ['section' => 'jadwal-misa', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Temukan jadwal misa mingguan dan perayaan khusus di Kapel St. Yohanes Rasul'],

            // Artikel Section
            ['section' => 'artikel', 'key' => 'title', 'type' => 'text', 'value' => 'Artikel Terbaru'],
            ['section' => 'artikel', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Bacaan rohani dan berita terkini dari Kapel St. Yohanes Rasul'],
            ['section' => 'artikel', 'key' => 'button-text', 'type' => 'text', 'value' => 'Lihat Semua Artikel'],
            ['section' => 'artikel', 'key' => 'button-url', 'type' => 'text', 'value' => '/articles'],

            // Event Section
            ['section' => 'event', 'key' => 'title', 'type' => 'text', 'value' => 'Kegiatan Mendatang'],
            ['section' => 'event', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Jadwal kegiatan dan perayaan di Kapel St. Yohanes Rasul'],
            ['section' => 'event', 'key' => 'button-text', 'type' => 'text', 'value' => 'Lihat Semua Kegiatan'],
            ['section' => 'event', 'key' => 'button-url', 'type' => 'text', 'value' => '/events'],

            // Contact Section
            ['section' => 'contact', 'key' => 'title', 'type' => 'text', 'value' => 'Hubungi Kami'],
            ['section' => 'contact', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Jangan ragu untuk menghubungi kami jika ada pertanyaan atau keperluan pastoral'],
            ['section' => 'contact', 'key' => 'address', 'type' => 'textarea', 'value' => 'Jl. Taman Pondok Jati A04-A04a, Geluran, Kec. Taman, Sidoarjo, Jawa Timur 61257'],
            ['section' => 'contact', 'key' => 'email', 'type' => 'text', 'value' => 'kapel.styohanesrasul@gmail.com'],
            ['section' => 'contact', 'key' => 'phone', 'type' => 'text', 'value' => '-'],
            ['section' => 'contact', 'key' => 'map-embed', 'type' => 'textarea', 'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.4!2d112.7!3d-7.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwMTgnMDAuMCJTIDExMsKwNDInMDAuMCJF!5e0!3m2!1sen!2sid!4v1234567890'],
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

        $this->command->info('Home page content seeded successfully.');

        // Call ServiceContentSeeder
        $this->call([
            ServiceContentSeeder::class
        ]);

        // ======================================
        // EVENTS PAGE CONTENT
        // ======================================
        $eventContents = [
            // Hero Section
            ['section' => 'hero', 'key' => 'title', 'type' => 'text', 'value' => 'Kalender Kegiatan Kapel'],
            ['section' => 'hero', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Temukan jadwal kegiatan, perayaan liturgi, dan acara khusus di Kapel St. Yohanes Rasul'],
            ['section' => 'hero', 'key' => 'background-image', 'type' => 'image', 'value' => 'images/events-hero.jpg'],
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

        $this->command->info('Events page content seeded successfully.');

        // ======================================
        // ARTICLES PAGE CONTENT
        // ======================================
        $articleContents = [
            // Hero Section
            ['section' => 'hero', 'key' => 'title', 'type' => 'text', 'value' => 'Artikel & Berita'],
            ['section' => 'hero', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Bacaan rohani, berita terkini, dan berbagai informasi dari Kapel St. Yohanes Rasul'],
            ['section' => 'hero', 'key' => 'background-image', 'type' => 'image', 'value' => 'images/articles-hero.jpg'],
        ];

        foreach ($articleContents as $content) {
            ContentSetting::updateOrCreate(
                [
                    'page' => 'articles',
                    'section' => $content['section'],
                    'key' => $content['key'],
                ],
                [
                    'type' => $content['type'],
                    'value' => $content['value'],
                ]
            );
        }

        $this->command->info('Articles page content seeded successfully.');

        // ======================================
        // BORROWING PAGE CONTENT
        // ======================================
        $borrowingContents = [
            // Hero Section
            ['section' => 'hero', 'key' => 'title', 'type' => 'text', 'value' => 'Peminjaman Inventaris'],
            ['section' => 'hero', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Layanan peminjaman inventaris Kapel St. Yohanes Rasul untuk kegiatan umat'],
            ['section' => 'hero', 'key' => 'background-image', 'type' => 'image', 'value' => 'images/borrowing-hero.jpg'],

            // Info Section
            ['section' => 'info', 'key' => 'title', 'type' => 'text', 'value' => 'Cara Peminjaman'],
            ['section' => 'info', 'key' => 'content', 'type' => 'textarea', 'value' => 'Untuk meminjam inventaris kapel, silakan hubungi sekretariat atau isi form peminjaman. Pastikan untuk mengembalikan barang dalam kondisi baik sesuai jadwal yang disepakati.'],
            ['section' => 'info', 'key' => 'contact-person', 'type' => 'text', 'value' => 'Sekretariat Kapel'],
            ['section' => 'info', 'key' => 'contact-phone', 'type' => 'text', 'value' => '-'],
        ];

        foreach ($borrowingContents as $content) {
            ContentSetting::updateOrCreate(
                [
                    'page' => 'borrowing',
                    'section' => $content['section'],
                    'key' => $content['key'],
                ],
                [
                    'type' => $content['type'],
                    'value' => $content['value'],
                ]
            );
        }

        $this->command->info('Borrowing page content seeded successfully.');

        // ======================================
        // ABOUT PAGE CONTENT
        // ======================================
        $aboutContents = [
            // Hero Section
            ['section' => 'hero', 'key' => 'title', 'type' => 'text', 'value' => 'Tentang Kapel St. Yohanes Rasul'],
            ['section' => 'hero', 'key' => 'subtitle', 'type' => 'textarea', 'value' => 'Mengenal lebih dekat sejarah dan visi misi Kapel St. Yohanes Rasul'],
            ['section' => 'hero', 'key' => 'background-image', 'type' => 'image', 'value' => 'images/about-hero.jpg'],

            // History Section
            ['section' => 'history', 'key' => 'title', 'type' => 'text', 'value' => 'Sejarah Kapel'],
            ['section' => 'history', 'key' => 'content', 'type' => 'textarea', 'value' => 'Kapel St. Yohanes Rasul berdiri di bawah naungan Paroki Santo Yusup Karangpilang, Surabaya. Kapel ini menjadi tempat berkumpulnya umat Katolik di wilayah Taman, Sidoarjo untuk merayakan iman bersama.'],
            ['section' => 'history', 'key' => 'image', 'type' => 'image', 'value' => 'images/history.jpg'],

            // Vision Mission Section
            ['section' => 'vision-mission', 'key' => 'vision-title', 'type' => 'text', 'value' => 'Visi'],
            ['section' => 'vision-mission', 'key' => 'vision-content', 'type' => 'textarea', 'value' => 'Menjadi komunitas iman yang hidup, bersaksi, dan melayani dalam semangat kasih Kristus.'],
            ['section' => 'vision-mission', 'key' => 'mission-title', 'type' => 'text', 'value' => 'Misi'],
            ['section' => 'vision-mission', 'key' => 'mission-content', 'type' => 'textarea', 'value' => '1. Menghayati dan merayakan iman melalui liturgi yang bermakna. 2. Membangun persaudaraan yang erat antar umat. 3. Melayani sesama dengan kasih yang tulus. 4. Menumbuhkan iman generasi muda.'],
        ];

        foreach ($aboutContents as $content) {
            ContentSetting::updateOrCreate(
                [
                    'page' => 'about',
                    'section' => $content['section'],
                    'key' => $content['key'],
                ],
                [
                    'type' => $content['type'],
                    'value' => $content['value'],
                ]
            );
        }

        $this->command->info('About page content seeded successfully.');
    }
}
