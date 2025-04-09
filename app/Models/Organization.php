<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\isTrue;

class Organization extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'code',
        'is_active',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    #[Scope]
    public function Active($query)
    {
        return $query->where('is_active', true);
    }
}
