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
        return DB::transaction(function () use ($user, $workshop): array {
            $lockedWorkshop = Workshop::query()
                ->whereKey($workshop->id)
                ->lockForUpdate()
                ->firstOrFail();

            $alreadyRegistered = Registration::query()
                ->where('user_id', $user->id)
                ->where('workshop_id', $lockedWorkshop->id)
                ->exists();

            if ($alreadyRegistered) {
                return ['error' => 'You are already registered for this workshop.'];
            }

            if ($this->hasConfirmedOverlap($user, $lockedWorkshop)) {
                return ['error' => 'You are already confirmed for an overlapping workshop.'];
            }

            $confirmedCount = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->lockForUpdate()
                ->count();

            if ($confirmedCount < $lockedWorkshop->capacity) {
                Registration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $lockedWorkshop->id,
                    'status' => Registration::STATUS_CONFIRMED,
                ]);

                return ['status' => Registration::STATUS_CONFIRMED];
            }

            $nextWaitlistPosition = ((int) Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('status', Registration::STATUS_WAITLISTED)
                ->lockForUpdate()
                ->max('waitlist_position')) + 1;

            Registration::create([
                'user_id' => $user->id,
                'workshop_id' => $lockedWorkshop->id,
                'status' => Registration::STATUS_WAITLISTED,
                'waitlist_position' => $nextWaitlistPosition,
            ]);

            return [
                'status' => Registration::STATUS_WAITLISTED,
                'waitlist_position' => $nextWaitlistPosition,
            ];
        });
    }

    public function cancel(User $user, Workshop $workshop): array
    {
        return DB::transaction(function () use ($user, $workshop): array {
            $lockedWorkshop = Workshop::query()
                ->whereKey($workshop->id)
                ->lockForUpdate()
                ->firstOrFail();

            $registration = Registration::query()
                ->where('user_id', $user->id)
                ->where('workshop_id', $lockedWorkshop->id)
                ->lockForUpdate()
                ->first();

            if (! $registration) {
                return ['error' => 'Registration not found.'];
            }

            $wasConfirmed = $registration->status === Registration::STATUS_CONFIRMED;
            $registration->delete();

            if (! $wasConfirmed) {
                return ['cancelled' => true];
            }

            $nextWaitlistedRegistration = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('status', Registration::STATUS_WAITLISTED)
                ->orderBy('waitlist_position')
                ->lockForUpdate()
                ->first();

            if ($nextWaitlistedRegistration) {
                $nextWaitlistedRegistration->update([
                    'status' => Registration::STATUS_CONFIRMED,
                    'waitlist_position' => null,
                ]);

                return [
                    'cancelled' => true,
                    'promoted_user_id' => $nextWaitlistedRegistration->user_id,
                ];
            }

            return ['cancelled' => true];
        });
    }

    private function hasConfirmedOverlap(User $user, Workshop $targetWorkshop): bool
    {
        return Registration::query()
            ->where('user_id', $user->id)
            ->where('status', Registration::STATUS_CONFIRMED)
            ->whereHas('workshop', function ($query) use ($targetWorkshop): void {
                $query->where('starts_at', '<', $targetWorkshop->ends_at)
                    ->where('ends_at', '>', $targetWorkshop->starts_at);
            })
            ->exists();
    }
}
