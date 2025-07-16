<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingDocument extends Model
{
    protected $fillable = [
        'start_borrowing',
        'end_borrowing'
    ];

    public function documents()
    {
        return $this->morphToMany(Document::class, 'detailable');
    }

    public function borrowings()
    {
        return $this->morphMany(Borrowing::class, 'document_typable');
    }
}
