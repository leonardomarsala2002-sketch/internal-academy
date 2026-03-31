<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workshop extends Model
{
    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'capacity',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'capacity'  => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function confirmedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)
            ->where('status', Registration::STATUS_CONFIRMED);
    }

    public function waitlistedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)
            ->where('status', Registration::STATUS_WAITLISTED)
            ->orderBy('waitlist_position');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'registrations')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function confirmedRegistrationsCount(): int
    {
        if (isset($this->confirmed_registrations_count)) {
            return (int) $this->confirmed_registrations_count;
        }

        return $this->confirmedRegistrations()->count();
    }

    public function availableSeats(): int
    {
        return max(0, $this->capacity - $this->confirmedRegistrationsCount());
    }

    public function isFull(): bool
    {
        return $this->availableSeats() <= 0;
    }
}
