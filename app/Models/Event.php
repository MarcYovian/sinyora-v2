<?php

namespace App\Models;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = 'events';

    protected $primaryKey = 'id';

    public $timestamps = true;

    public $incrementing = true;

    public $keyType = 'int';

    protected $dates = [
        'start_recurring',
        'end_recurring',
    ];

    protected $fillable = [
        'name',
        'description',
        'start_recurring',
        'end_recurring',
        'status',
        'recurrence_type',
        'created_by',
        'organization_id',
        'event_category_id',
    ];

    protected $casts = [
        'start_recurring' => 'date',
        'end_recurring' => 'date',
        'status' => EventApprovalStatus::class,
        'recurrence_type' => EventRecurrenceType::class,
    ];

    public function eventRecurrences()
    {
        return $this->hasMany(EventRecurrence::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    #[Scope]
    public function pending($query)
    {
        return $query->where('status', EventApprovalStatus::PENDING);
    }

    #[Scope]
    public function approved($query)
    {
        return $query->where('status', EventApprovalStatus::APPROVED);
    }

    #[Scope]
    public function rejected($query)
    {
        return $query->where('status', EventApprovalStatus::REJECTED);
    }
}
