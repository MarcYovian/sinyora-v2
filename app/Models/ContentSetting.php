<?php

namespace App\Models;

use App\Observers\ContentSettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ContentSettingObserver::class)]
class ContentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'section',
        'key',
        'value',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
