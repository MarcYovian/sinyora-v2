<?php

namespace App\Models;

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
}
