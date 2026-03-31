<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function register(User $user, Workshop $workshop): array
    {
        // Già iscritto?
        if ($workshop->registrations()->where('user_id', $user->id)->exists()) {
            return ['error' => 'You are already registered for this workshop.'];
        }

        // Controllo overlap (No Ubiquity)
        $overlap = Registration::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->whereHas('workshop', function ($q) use ($workshop) {
                $q->where('starts_at', '<', $workshop->ends_at)
                  ->where('ends_at', '>', $workshop->starts_at);
            })->exists();

        if ($overlap) {
            return ['error' => 'You already have a workshop at this time.'];
        }

        // Workshop pieno → waitlist
        if ($workshop->isFull()) {
            $position = ($workshop->waitlistedRegistrations()->max('waitlist_position') ?? 0) + 1;

            Registration::create([
                'user_id'           => $user->id,
                'workshop_id'       => $workshop->id,
                'status'            => 'waitlisted',
                'waitlist_position' => $position,
            ]);

            return ['waitlisted' => true];
        }

        // Posti disponibili → confermato
        Registration::create([
            'user_id'     => $user->id,
            'workshop_id' => $workshop->id,
            'status'      => 'confirmed',
        ]);

        return ['confirmed' => true];
    }

    public function cancel(User $user, Workshop $workshop): array
    {
        $registration = Registration::where('user_id', $user->id)
            ->where('workshop_id', $workshop->id)
            ->first();

        if (! $registration) {
            return ['error' => 'Registration not found.'];
        }

        DB::transaction(function () use ($registration, $workshop) {
            $wasConfirmed = $registration->status === 'confirmed';
            $registration->delete();

            if ($wasConfirmed) {
                $next = $workshop->waitlistedRegistrations()
                    ->lockForUpdate()
                    ->first();

                if ($next) {
                    $next->update([
                        'status'            => 'confirmed',
                        'waitlist_position' => null,
                    ]);
                }
            }
        });

        return ['cancelled' => true];
    }
}