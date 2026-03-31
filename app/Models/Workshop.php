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
            ->where('status', 'confirmed');
    }

public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'workshop_user');
}

    public function waitlistedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)
            ->where('status', 'waitlisted')
            ->orderBy('waitlist_position');
    }

    public function availableSeats(): int
    {
        return $this->capacity - $this->confirmedRegistrations()->count();
    }

    public function isFull(): bool
    {
        return $this->availableSeats() <= 0;
    }
}