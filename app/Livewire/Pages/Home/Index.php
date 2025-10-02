<?php

namespace App\Livewire\Pages\Home;

use App\Livewire\Forms\ContactForm;
use App\Models\ServiceContentSetting;
use App\Services\ContentService;
use App\Services\EventService;
use App\Services\MassScheduleService;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('components.layouts.app')]

    public ContactForm $contactForm;
    public array $content = [];
    public $services;
    // public $regularSchedules;


    public function mount(SEOService $seo, ContentService $contentService, MassScheduleService $massScheduleService)
    {
        $seo->setTitle('Jadwal Misa & Informasi Umat - Kapel St. Yohanes Rasul', false)
            ->setDescription(
                'Selamat datang di situs resmi Kapel Santo Yohanes Rasul, di bawah naungan Paroki Santo Yusup Karangpilang, Surabaya. ' .
                    'Temukan jadwal misa mingguan, informasi event terbaru, artikel rohani, dan berbagai pelayanan liturgis kami.'
            )->setKeywords([
                'Kapel St. Yohanes Rasul',
                'Jadwal Misa Surabaya',
                'Gereja Katolik Taman Sidoarjo',
                'Paroki Santo Yusup Karangpilang',
                'Informasi Umat Katolik',
                'Pelayanan Liturgi',
                'Event Gereja Katolik',
                'Artikel Rohani Katolik',
                'Komunitas Katolik Surabaya',
                'Pelayanan Sakramen',
            ])->setOgImage(asset('images/seo/home-page-ogimage.png'));

        $this->setupChurchSchema($seo);

        $this->content = $contentService->getPage('home');
        $this->services = ServiceContentSetting::where('is_active', true)->orderBy('order')->get();
    }
    public function send()
    {
        $this->contactForm->store();

        toastr()->success('Pesan berhasil dikirim!');
    }

    public function render()
    {
        $massScheduleService = app(MassScheduleService::class);
        $regularSchedules = $massScheduleService->getSchedulesForPublic()->groupBy('day_name');

        $specialMassScheduleEventsService = app(EventService::class);
        $specialSchedules = $specialMassScheduleEventsService->getUpcomingMassSchedule();

        return view('livewire.pages.home.index', [
            'regularSchedules' => $regularSchedules,
            'specialSchedules' => $specialSchedules,
        ]);
    }

    private function setupChurchSchema(SEOService $seo): void
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Church',
            'name' => 'Kapel Santo Yohanes Rasul',
            'url' => route('home.index'),
            'logo' => asset('images/logo-with-text.png'), // <-- TAMBAHAN: Sangat Penting!
            'image' => asset('images/seo/foto-kapel-depan.jpg'), // <-- TAMBAHAN: Foto representatif
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Taman Pondok Jati A04-A04a',
                'addressLocality' => 'Geluran, Kec. Taman',
                'addressRegion' => 'Jawa Timur',
                'postalCode' => '61257', // <-- TAMBAHAN: Kode Pos
                'addressCountry' => 'ID'
            ],
            // TAMBAHAN: Detail Kontak
            // 'contactPoint' => [
            //     '@type' => 'ContactPoint',
            //     'telephone' => '+62-XXX-XXXX-XXXX', // <-- Ganti dengan nomor telepon sekretariat/kontak
            //     'contactType' => 'customer service' // Atau 'public relations'
            // ],
            // TAMBAHAN: Jadwal Misa (Jam Buka)
            'openingHours' => 'Su 17:00-18:00', // Format: Mo,Tu,We,Th,Fr,Sa,Su HH:MM-HH:MM
            'parentOrganization' => [
                '@type' => 'Church',
                'name' => 'Paroki Santo Yusup Karangpilang'
            ]
        ];

        $seo->setSchema($schema);
    }
}
