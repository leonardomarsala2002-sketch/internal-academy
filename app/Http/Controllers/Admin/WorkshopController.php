<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use Inertia\Inertia;

class WorkshopController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Workshops/Index', [
            'workshops' => Workshop::orderBy('starts_at')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Workshops/Create');
    }

    public function store(StoreWorkshopRequest $request)
{
    \App\Models\Workshop::create($request->validated());

    return redirect()->route('admin.workshops.index');
}

    public function edit(Workshop $workshop)
    {
        return Inertia::render('Admin/Workshops/Edit', [
            'workshop' => $workshop,
        ]);
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop)
    {
        $workshop->update($request->validated());

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop updated successfully.');
    }

    public function destroy(Workshop $workshop)
    {
        $workshop->delete();

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }
}