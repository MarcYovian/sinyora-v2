<?php

namespace App\Livewire\Pages\Event;

use App\Livewire\Forms\EventProposalForm;
use App\Models\EventCategory as Category;
use App\Models\Location;
use App\Models\Organization;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;
    #[Layout('components.layouts.app')]

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
        toastr()->success('Proposal berhasil diajukan! Terima kasih atas partisipasi Anda.');
    }

    public function render()
    {
        return view('livewire.pages.event.index', [
            'categories' => Category::all(['id', 'name']),
            'organizations' => Organization::all(['id', 'name']),
            'locations' => Location::all(['id', 'name', 'description']),
        ]);
    }
}
