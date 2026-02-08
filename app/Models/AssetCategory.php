<?php

namespace App\Models;

use App\Observers\AssetCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([AssetCategoryObserver::class])]
class AssetCategory extends Model
{
    use HasFactory;

    protected $table = 'asset_categories';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
