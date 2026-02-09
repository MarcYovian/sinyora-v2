<?php

namespace App\Livewire\Pages\Event;

use App\Livewire\Forms\EventProposalForm;
use App\Models\EventCategory as Category;
use App\Models\Location;
use App\Models\Organization;
use App\Services\SEOService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    #[Layout('components.layouts.app')]

    /**
     * Cache TTL in seconds (10 minutes for dropdown data).
     */
    private const DROPDOWN_CACHE_TTL = 600;

    public EventProposalForm $form;
    public $document;

    public function mount(SEOService $seo)
    {
        $seo->setTitle('Kalender Kegiatan Kapel St. Yohanes Rasul Surabaya');
        $seo->setDescription(
            'Temukan jadwal lengkap kegiatan, perayaan liturgi, acara komunitas, dan agenda khusus lainnya di Kapel St. Yohanes Rasul. ' .
                'Jangan lewatkan setiap momen kebersamaan umat.'
        );

        $seo->setKeywords([
            'kalender kegiatan gereja',
            'jadwal acara kapel',
            'event katolik surabaya',
            'perayaan liturgi',
            'kegiatan komunitas katolik',
            'agenda gereja',
            'acara rohani',
            'kegiatan umat katolik',
            'event keagamaan',
            'jadwal misa',
            'acara khusus gereja',
            'kegiatan rohani surabaya',
            'event komunitas kapel',
            'agenda liturgi kapel',
            'kegiatan keagamaan surabaya',
            'acara umat katolik',
            'jadwal perayaan gereja',
        ]);

        $seo->setOgImage(asset('images/seo/events-page-ogimage.png'));
    }

    public function updatedDocument()
    {
        $this->form->document = $this->document;
        $this->document = $this->form->document;
    }

    public function create()
    {
        $this->dispatch('open-modal', 'proposal-modal');
    }

    public function submitProposal()
    {
        flash()->success('Proposal berhasil diajukan! Terima kasih atas partisipasi Anda.');
    }

    /**
     * Get cached categories for dropdown.
     */
    private function getCategories()
    {
        return Cache::remember('event_categories_dropdown', self::DROPDOWN_CACHE_TTL, function () {
            return Category::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get cached organizations for dropdown.
     */
    private function getOrganizations()
    {
        return Cache::remember('organizations_dropdown', self::DROPDOWN_CACHE_TTL, function () {
            return Organization::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get cached locations for dropdown.
     */
    private function getLocations()
    {
        return Cache::remember('locations_dropdown', self::DROPDOWN_CACHE_TTL, function () {
            return Location::query()
                ->select(['id', 'name', 'description'])
                ->orderBy('name')
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.pages.event.index', [
            'categories' => $this->getCategories(),
            'organizations' => $this->getOrganizations(),
            'locations' => $this->getLocations(),
        ]);
    }
}
