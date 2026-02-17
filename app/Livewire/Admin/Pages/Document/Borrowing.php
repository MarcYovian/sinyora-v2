<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Livewire\Admin\Pages\Document\Location as LocationModal;
use App\Livewire\Admin\Pages\Document\Organization as OrganizationModal;
use App\Models\Asset;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Borrowing extends Component
{
    public array $borrowingData = [];
    public bool $hasUnmatchedAssets = false;
    public array $removedAssets = [];

    #[On('setDataForBorrowing')]
    public function setData(array $borrowingData)
    {
        // 1. Simpan data yang diterima ke properti komponen
        $this->reset('removedAssets', 'hasUnmatchedAssets', 'borrowingData');
        $this->borrowingData = $borrowingData;

        // 2. Lakukan pengecekan atau persiapan data di sini
        $this->checkForUnmatchedAssets();
    }

    public function linkAsset($kegiatanIndex, $assetIndex, $masterAssetId)
    {
        if (empty($masterAssetId)) {
            return;
        }

        $masterAsset = Asset::find($masterAssetId);
        if ($masterAsset) {
            // Update data di dalam array borrowingData
            $this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$assetIndex]['item'] = $masterAsset->name;
            $this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$assetIndex]['item_id'] = $masterAsset->id;
            $this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$assetIndex]['match_status'] = 'matched';

            // Periksa kembali apakah masih ada aset yang tidak cocok
            $this->checkForUnmatchedAssets();

            flash()->success('Aset berhasil ditautkan.');
        }
    }

    public function removeUnmatchedAsset($kegiatanIndex, $assetIndex)
    {
        if (isset($this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$assetIndex])) {

            // 1. Ambil data aset yang akan dihapus
            $assetToRemove = $this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$assetIndex];

            // 2. Tambahkan ke array penampung
            $this->removedAssets[] = $assetToRemove;

            // 3. Hapus dari daftar peminjaman utama
            unset($this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'][$assetIndex]);

            // 4. Re-index array untuk menjaga konsistensi UI
            $this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'] = array_values(
                $this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam']
            );

            // 5. Periksa kembali apakah masih ada aset yang tidak cocok
            $this->checkForUnmatchedAssets();

            flash()->success('Aset telah dihapus dari daftar.');
        }
    }

    public function checkForUnmatchedAssets()
    {
        $this->hasUnmatchedAssets = false;
        foreach ($this->borrowingData['detail_kegiatan'] ?? [] as $kegiatan) {
            foreach ($kegiatan['barang_dipinjam'] ?? [] as $asset) {
                if (($asset['match_status'] ?? 'unmatched') === 'unmatched') {
                    $this->hasUnmatchedAssets = true;
                    return; // Keluar dari loop jika sudah ditemukan satu
                }
            }
        }
    }

    public function getAvailableAssetsForEvent($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return collect(); // Kembalikan koleksi kosong jika tanggal tidak valid
        }

        $assets = Asset::query()
            ->where('is_active', true)
            ->withBorrowedQuantityBetween(startTime: $startDate, endTime: $endDate)
            ->get();

        return $assets->map(function ($asset) {
            $asset->borrowed_quantity = $asset->borrowings->sum(function ($borrowing) {
                return $borrowing->pivot->quantity ?? 0;
            });

            $asset->available_stock = $asset->quantity - $asset->borrowed_quantity;

            return $asset;
        });
    }

    public function getSelectedAssetIds($kegiatanIndex)
    {
        return collect($this->borrowingData['detail_kegiatan'][$kegiatanIndex]['barang_dipinjam'])
            ->pluck('item_id')
            ->filter()
            ->toArray();
    }

    public function saveBorrowing()
    {
        $type = $this->borrowingData['type'];

        if ($type === 'perizinan') {
            foreach ($this->borrowingData['informasi_umum_dokumen']['organisasi'] as $organization) {
                if (($organization['match_status'] ?? 'unmatched') === 'unmatched') {
                    $this->dispatch('close-modal', 'borrowing-modal');
                    $this->dispatch('setDataForOrganization', data: $this->borrowingData)->to(OrganizationModal::class);
                    $this->dispatch('open-modal', 'organization-modal');
                    return;
                }
            }
            foreach ($this->borrowingData['detail_kegiatan'] as $event) {
                foreach ($event['location_data'] as $location) {
                    if (($location['match_status'] ?? 'unmatched') === 'unmatched') {
                        $this->dispatch('close-modal', 'borrowing-modal');
                        $this->dispatch('setDataForLocation', data: $this->borrowingData)->to(LocationModal::class);
                        $this->dispatch('open-modal', 'location-modal');
                        return;
                    }
                }
            }
            $this->dispatch('close-modal', 'borrowing-modal');
            $this->dispatch('setDataForEvent', data: $this->borrowingData)->to(EventModal::class);
            $this->dispatch('open-modal', 'event-modal');
        } else if ($type === 'peminjaman') {
            $this->dispatch('close-modal', 'borrowing-modal');
            $this->dispatch('setDataForEvent', data: $this->borrowingData)->to(EventModal::class);
            $this->dispatch('open-modal', 'event-modal');
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.document.borrowing');
    }
}
