<?php

namespace App\Models;

use App\Observers\EventCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(EventCategoryObserver::class)]
class EventCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'keywords',
        'is_active',
        'is_mass_category',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'is_mass_category' => 'boolean',
    ];


    public function events()
    {
        return $this->hasMany(Event::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
