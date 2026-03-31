<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Workshop;

class AdminWorkshopStatsService
{
    public function summary(): array
    {
        $totalWorkshops = Workshop::query()->count();

        $totalConfirmedRegistrations = Registration::query()
            ->where('status', Registration::STATUS_CONFIRMED)
            ->count();

        $totalWaitlistedRegistrations = Registration::query()
            ->where('status', Registration::STATUS_WAITLISTED)
            ->count();

        $mostPopularWorkshop = Workshop::query()
            ->withCount([
                'confirmedRegistrations',
            ])
            ->orderByDesc('confirmed_registrations_count')
            ->orderBy('id')
            ->first();

        return [
            'total_workshops' => $totalWorkshops,
            'total_confirmed_registrations' => $totalConfirmedRegistrations,
            'total_waitlisted_registrations' => $totalWaitlistedRegistrations,
            'most_popular_workshop' => $mostPopularWorkshop && $mostPopularWorkshop->confirmed_registrations_count > 0
                ? [
                    'id' => $mostPopularWorkshop->id,
                    'title' => $mostPopularWorkshop->title,
                    'confirmed_registrations_count' => $mostPopularWorkshop->confirmed_registrations_count,
                ]
                : null,
        ];
    }

    public function registrationsPerWorkshop(): array
    {
        return Workshop::query()
            ->withCount([
                'confirmedRegistrations',
                'waitlistedRegistrations',
            ])
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Workshop $workshop) => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'starts_at' => $workshop->starts_at,
                'capacity' => $workshop->capacity,
                'confirmed_registrations_count' => $workshop->confirmed_registrations_count,
                'waitlisted_registrations_count' => $workshop->waitlisted_registrations_count,
                'seats_left' => max(0, $workshop->capacity - $workshop->confirmed_registrations_count),
            ])
            ->all();
    }
}
