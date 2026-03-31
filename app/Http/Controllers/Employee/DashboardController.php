<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $workshops = Workshop::where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->get()
            ->map(function ($workshop) use ($user) {
                $isRegistered = $workshop->users()->where('user_id', $user->id)->exists();
                $registeredCount = $workshop->users()->count();

                return [
                    'id' => $workshop->id,
                    'title' => $workshop->title,
                    'description' => $workshop->description,
                    'starts_at' => $workshop->starts_at,
                    'ends_at' => $workshop->ends_at,
                    'capacity' => $workshop->capacity,
                    'seats_left' => $workshop->capacity - $registeredCount,
                    'is_full' => $registeredCount >= $workshop->capacity,
                    'is_registered' => $isRegistered,
                ];
            });

        return Inertia::render('Employee/Dashboard', [
            'workshops' => $workshops,
        ]);
    }
}