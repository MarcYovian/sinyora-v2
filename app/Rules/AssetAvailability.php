<?php

namespace App\Rules;

use App\Enums\BorrowingStatus;
use App\Models\Asset;
use App\Models\Borrowing;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AssetAvailability implements ValidationRule
{
    public function __construct(
        protected int $assetId,
        protected string $startDate,
        protected string $endDate,
        protected ?int $excludeBorrowingId = null
    ) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $asset = Asset::find($this->assetId);

        if (!$asset) {
            $fail('Asset tidak ditemukan.');
            return;
        }

        $totalBorrowed = Borrowing::where('status', BorrowingStatus::APPROVED)
            ->where(function ($query) {
                $query->whereBetween('start_datetime', [$this->startDate, $this->endDate])
                    ->orWhereBetween('end_datetime', [$this->startDate, $this->endDate])
                    ->orWhere(function ($q) {
                        $q->where('start_datetime', '<=', $this->startDate)
                            ->where('end_datetime', '>=', $this->endDate);
                    });
            })
            ->whereHas('assets', function ($q) {
                $q->where('asset_id', $this->assetId);
            })
            ->when($this->excludeBorrowingId, function ($query) {
                $query->where('id', '!=', $this->excludeBorrowingId);
            })
            ->with(['assets' => function ($q) {
                $q->where('asset_id', $this->assetId);
            }])
            ->get()
            ->sum(function ($borrowing) {
                return $borrowing->assets->firstWhere('id', $this->assetId)->pivot->quantity;
            });

        $availableQuantity = $asset->quantity - $totalBorrowed;
        if ($value > $availableQuantity) {
            $fail("Jumlah aset {$asset->name} tidak tersedia. Stok tersisa: {$availableQuantity}");
        }
    }
}
