<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $table = 'borrowings';

    protected $fillable = [
        'asset_id',
        'created_by',
        'event_id',
        'start_datetime',
        'end_datetime',
        'notes',
        'borrower',
        'borrower_phone',
        'status',
    ];

    protected function casts()
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'status' => BorrowingStatus::class,
        ];
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_borrowing')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
