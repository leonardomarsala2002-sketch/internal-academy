<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $workshops = Workshop::query()
            ->where('starts_at', '>', now())
            ->withCount('confirmedRegistrations')
            ->with([
                'registrations' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->select('id', 'user_id', 'workshop_id', 'status'),
            ])
            ->orderBy('starts_at')
            ->get()
            ->map(function (Workshop $workshop) {
                $registration = $workshop->registrations->first();
                $seatsLeft = $workshop->availableSeats();
                $registrationStatus = $registration?->status;

                $state = match (true) {
                    $registrationStatus === Registration::STATUS_CONFIRMED => 'confirmed',
                    $registrationStatus === Registration::STATUS_WAITLISTED => 'waitlisted',
                    $seatsLeft <= 0 => 'full',
                    default => 'available',
                };

                return [
                    'id' => $workshop->id,
                    'title' => $workshop->title,
                    'description' => $workshop->description,
                    'starts_at' => $workshop->starts_at,
                    'ends_at' => $workshop->ends_at,
                    'capacity' => $workshop->capacity,
                    'seats_left' => $seatsLeft,
                    'registration_status' => $registrationStatus,
                    'is_registered' => (bool) $registration,
                    'is_full' => $seatsLeft <= 0,
                    'state' => $state,
                ];
            });

        return Inertia::render('Employee/Dashboard', [
            'workshops' => $workshops,
        ]);
    }
}
