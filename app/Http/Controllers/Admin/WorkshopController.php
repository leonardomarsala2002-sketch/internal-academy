<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use App\Services\AdminWorkshopStatsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Inertia\Inertia;

class WorkshopController extends Controller
{
    public function __construct(private readonly AdminWorkshopStatsService $statsService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Workshops/Index', [
            'workshops' => Workshop::orderBy('starts_at')->get(),
            'stats' => $this->statsService->summary(),
            'registrationsPerWorkshop' => $this->statsService->registrationsPerWorkshop(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Workshops/Create');
    }

    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        Workshop::create($request->validated());

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop created successfully.');
    }

    public function edit(Workshop $workshop): Response
    {
        return Inertia::render('Admin/Workshops/Edit', [
            'workshop' => $workshop,
        ]);
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop updated successfully.');
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }
}
