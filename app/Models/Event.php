<?php

namespace App\Models;

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
        'organization_id',
        'event_category_id',
    ];

    protected $casts = [
        'start_recurring' => 'date',
        'end_recurring' => 'date',
        'status' => EventApprovalStatus::class,
        'recurrence_type' => EventRecurrenceType::class,
    ];

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function eventRecurrences(): HasMany
    {
        return $this->hasMany(EventRecurrence::class);
    }

    public function locations(): MorphToMany
    {
        return $this->morphedByMany(
            Location::class,
            'locatable',
            'event_locatables'
        );
    }

    public function customLocations(): MorphToMany
    {
        return $this->morphedByMany(
            CustomLocation::class,
            'locatable',
            'event_locatables'
        );
    }

    public function document_typable(): MorphTo
    {
        return $this->morphTo();
    }

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function borrowings(): MorphMany
    {
        return $this->morphMany(Borrowing::class, 'borrowable');
    }

    public function schedules()
    {
        return $this->morphMany(Schedules::class, 'describable');
    }

    #[Scope]
    public function pending($query): mixed
    {
        return $query->where('status', EventApprovalStatus::PENDING);
    }

    #[Scope]
    public function approved($query): mixed
    {
        return $query->where('status', EventApprovalStatus::APPROVED);
    }

    #[Scope]
    public function rejected($query): mixed
    {
        return $query->where('status', EventApprovalStatus::REJECTED);
    }

    public function firstRecurrence(): HasOne
    {
        return $this->hasOne(EventRecurrence::class)
            ->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc');
    }

    public function getContinuousEndDate(): ?Carbon
    {
        // Ambil semua jadwal terurut untuk dianalisis
        $recurrences = $this->eventRecurrences()->orderBy('date', 'asc')->orderBy('time_start', 'asc')->get();

        if ($recurrences->isEmpty()) {
            return null;
        }

        // Mulai dengan asumsi jadwal terakhir adalah jadwal pertama
        $lastContinuousRecurrence = $recurrences->first();

        // Loop mulai dari item kedua untuk membandingkan dengan item sebelumnya
        for ($i = 1; $i < $recurrences->count(); $i++) {
            $previous = $recurrences->get($i - 1);
            $current = $recurrences->get($i);

            // Cek apakah jadwal berkelanjutan (continuous)
            // Kondisi: Waktu akhir sebelumnya adalah 23:59:59 DAN
            // Waktu mulai saat ini adalah 00:00:00 PADA HARI BERIKUTNYA.
            $previousEndTime = $previous->time_end->format('H:i:s');
            $currentStartTime = $current->time_start->format('H:i:s');
            $isNextDay = $current->date->isSameDay($previous->date->copy()->addDay());

            if ($previousEndTime === '23:59:59' && $currentStartTime === '00:00:00' && $isNextDay) {
                // Jika ya, perbarui jadwal terakhir yang kita lacak dan lanjutkan loop
                $lastContinuousRecurrence = $current;
            } else {
                // Jika tidak, berarti blok sudah terputus. Hentikan loop.
                break;
            }
        }

        // Gabungkan tanggal dan waktu dari jadwal terakhir yang berkelanjutan
        return Carbon::parse(
            $lastContinuousRecurrence->date->format('Y-m-d') . ' ' . $lastContinuousRecurrence->time_end->format('H:i:s')
        );
    }
}
