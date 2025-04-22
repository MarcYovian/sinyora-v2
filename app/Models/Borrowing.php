<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    use HasFactory;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }


    public function scopeApproved($query)
    {
        return $query->where('status', BorrowingStatus::APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', BorrowingStatus::REJECTED);
    }
}
