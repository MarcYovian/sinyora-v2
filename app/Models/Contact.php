<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'status',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }
}
