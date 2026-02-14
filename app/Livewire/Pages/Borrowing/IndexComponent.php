<?php

namespace App\Livewire\Pages\Borrowing;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Borrowing;
use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Services\SEOService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class IndexComponent extends Component
{
    #[Layout('components.layouts.app')]

    public $borrowings;
    public $content = []; // Add content property

    // Properti untuk filter dan pencarian
    public string $search = '';
    public string $selectedCategory = '';
    public string $sortBy = 'latest';

    public string $startDate;
    public string $endDate;

    public function mount(SEOService $seo, \App\Services\ContentService $contentService)
    {
        // Fetch content
        $this->content = $contentService->getPage('borrowing');
        
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $this->borrowings = Borrowing::with(['event', 'assets', 'creator'])
            ->approved()
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit(5) // Batasi untuk performa
            ->get();

        $seo->setTitle($this->content['hero']['title'] ?? 'Peminjaman Aset Kapel St. Yohanes Rasul Surabaya');

        $seo->setDescription(
            $this->content['hero']['subtitle'] ?? 'Layanan peminjaman aset Kapel St. Yohanes Rasul. Cek ketersediaan barang seperti kursi, meja, sound system, dan lainnya untuk mendukung kegiatan dan acara Anda.'
        );

        $seo->setKeywords([
            'peminjaman aset gereja',
            'sewa inventaris kapel',
            'ketersediaan aset',
            'formulir peminjaman barang',
            'aset kapel surabaya',
            'pinjam sound system gereja'
        ]);

        $seo->setOgImage(asset('images/seo/borrowing-assets-page-ogimage.png'));
    }

    public function createRequest()
    {
        $this->dispatch('open-modal', 'proposal-modal');
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->sortBy = 'latest';
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function setDateRange(string $start, string $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
    }

    public function render()
    {
        $startDateTime = Carbon::parse($this->startDate)->startOfDay()->toDateTimeString();
        $endDateTime = Carbon::parse($this->endDate)->endOfDay()->toDateTimeString();

        // Panggil repository untuk mendapatkan aset yang tersedia
        $availableAssets = app(AssetRepositoryInterface::class)->getAvailableAssetsBetween($startDateTime, $endDateTime);

        // Lakukan filtering dan searching di atas collection hasil repository
        $assets = $availableAssets
            ->when($this->search, function ($collection) {
                return $collection->filter(function ($asset) {
                    return str_contains(strtolower($asset->name), strtolower($this->search));
                });
            })
            ->when($this->selectedCategory, function ($collection) {
                return $collection->where('asset_category_id', $this->selectedCategory);
            });

        // Terapkan pengurutan pada collection
        $assets = match ($this->sortBy) {
            'oldest' => $assets->sortBy('created_at'),
            'name_asc' => $assets->sortBy('name'),
            'name_desc' => $assets->sortByDesc('name'),
            default => $assets->sortByDesc('created_at'), // latest
        };

        $assetCategories = AssetCategory::orderBy('name')->get();

        return view('livewire.pages.borrowing.index-component', [
            'assets' => $assets,
            'assetCategories' => $assetCategories,
        ]);
    }
}
