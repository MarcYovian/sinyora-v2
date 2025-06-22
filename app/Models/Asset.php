<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $table = 'assets';

    protected $fillable = [
        'asset_category_id',
        'name',
        'slug',
        'code',
        'description',
        'quantity',
        'storage_location',
        'image',
        'is_active',
        'created_by',
    ];

    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function borrowings()
    {
        return $this->belongsToMany(Borrowing::class, 'asset_borrowing')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    #[Scope]
    protected function active(Builder $query)
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function withBorrowedQuantityBetween(Builder $query, $startTime, $endTime)
    {
        // Pengecekan untuk memastikan waktu tidak null
        if (!$startTime || !$endTime) {
            return $query; // Kembalikan query asli jika waktu tidak valid
        }

        return $query->with(['borrowings' => function ($borrowingQuery) use ($startTime, $endTime) {
            $borrowingQuery->where('status', BorrowingStatus::APPROVED)
                ->where(function ($q) use ($startTime, $endTime) {
                    // Kondisi 1: Peminjaman dimulai di dalam rentang waktu yang dicek.
                    $q->whereBetween('start_datetime', [$startTime, $endTime])
                        // Kondisi 2: Peminjaman berakhir di dalam rentang waktu yang dicek.
                        ->orWhereBetween('end_datetime', [$startTime, $endTime])
                        // Kondisi 3: Rentang waktu yang dicek berada sepenuhnya di dalam waktu peminjaman.
                        ->orWhere(function ($sub) use ($startTime, $endTime) {
                            $sub->where('start_datetime', '<=', $startTime)
                                ->where('end_datetime', '>=', $endTime);
                        });
                });
        }]);
    }
}
