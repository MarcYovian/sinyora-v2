<?php

namespace App\Models;

use App\Observers\LocationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(LocationObserver::class)]
class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'color',
        'image',
        'is_active',
    ];

    public function events()
    {
        return $this->morphedByMany(Event::class, 'locatable', 'event_locatables');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
