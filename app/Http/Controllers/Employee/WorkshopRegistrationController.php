<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Workshop;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkshopRegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $registrationService)
    {
    }

    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $result = $this->registrationService->register($request->user(), $workshop);

        if (isset($result['error'])) {
            return back()->withErrors(['registration' => $result['error']]);
        }

        if (($result['status'] ?? null) === Registration::STATUS_WAITLISTED) {
            return back()->with('success', 'Workshop is full. You have been added to the waiting list.');
        }

        return back()->with('success', 'Successfully registered to workshop.');
    }

    public function destroy(Request $request, Workshop $workshop): RedirectResponse
    {
        $result = $this->registrationService->cancel($request->user(), $workshop);

        if (isset($result['error'])) {
            return back()->withErrors(['registration' => $result['error']]);
        }

        return back()->with('success', 'Registration cancelled.');
    }
}
