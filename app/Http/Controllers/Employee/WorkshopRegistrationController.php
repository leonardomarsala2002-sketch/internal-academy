<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopRegistrationController extends Controller
{
    public function store(Request $request, Workshop $workshop)
    {
        $user = $request->user();

        // evita doppia iscrizione
        if ($workshop->users()->where('user_id', $user->id)->exists()) {
            return back();
        }

        // controlla capienza
        if ($workshop->users()->count() >= $workshop->capacity) {
            return back()->withErrors(['Workshop full']);
        }

        $workshop->users()->attach($user->id);

        return back();
    }
}