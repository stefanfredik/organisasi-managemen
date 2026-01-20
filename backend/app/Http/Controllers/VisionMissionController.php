<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisionMissionRequest;
use App\Http\Requests\UpdateVisionMissionRequest;
use App\Models\VisionMission;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisionMissionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', VisionMission::class);

        $visionMissions = VisionMission::with('creator')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('vision', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('VisionMissions/Index', [
            'visionMissions' => $visionMissions,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', VisionMission::class);

        return Inertia::render('VisionMissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVisionMissionRequest $request)
    {
        $this->authorize('create', VisionMission::class);

        // If status is active, deactivate all other vision/missions
        if ($request->status === 'active') {
            VisionMission::where('status', 'active')->update(['status' => 'inactive']);
        }

        $visionMission = VisionMission::create($request->validated() + ['created_by' => $request->user()->id]);

        $this->activityLogger->logCreate($visionMission, "Membuat Visi & Misi baru untuk periode {$visionMission->period}");

        return redirect()->route('vision-missions.index')
            ->with('success', 'Visi & Misi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VisionMission $visionMission)
    {
        $this->authorize('view', $visionMission);

        $visionMission->load([
            'creator',
            'histories' => function ($q) {
                $q->with('user')->latest()->limit(10);
            },
        ]);

        return Inertia::render('VisionMissions/Show', [
            'visionMission' => $visionMission,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VisionMission $visionMission)
    {
        $this->authorize('update', $visionMission);

        return Inertia::render('VisionMissions/Edit', [
            'visionMission' => $visionMission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVisionMissionRequest $request, VisionMission $visionMission)
    {
        $this->authorize('update', $visionMission);

        // If status is being changed to active, deactivate all other vision/missions
        if ($request->status === 'active' && $visionMission->status !== 'active') {
            VisionMission::where('status', 'active')
                ->where('id', '!=', $visionMission->id)
                ->update(['status' => 'inactive']);
        }

        $visionMission->update($request->validated());

        $this->activityLogger->logUpdate($visionMission, "Memperbarui Visi & Misi periode {$visionMission->period}");

        return redirect()->route('vision-missions.show', $visionMission)
            ->with('success', 'Visi & Misi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VisionMission $visionMission)
    {
        $this->authorize('delete', $visionMission);

        $period = $visionMission->period;
        $visionMission->delete();

        $this->activityLogger->logDelete($visionMission, "Menghapus Visi & Misi periode {$period}");

        return redirect()->route('vision-missions.index')
            ->with('success', 'Visi & Misi berhasil dihapus.');
    }

    /**
     * Toggle status between active and inactive.
     */
    public function toggleStatus(VisionMission $visionMission)
    {
        $this->authorize('changeStatus', $visionMission);

        $newStatus = $visionMission->status === 'active' ? 'inactive' : 'active';

        // If activating, deactivate all others
        if ($newStatus === 'active') {
            VisionMission::where('status', 'active')
                ->where('id', '!=', $visionMission->id)
                ->update(['status' => 'inactive']);
        }

        $visionMission->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        $this->activityLogger->logUpdate($visionMission, "Visi & Misi periode {$visionMission->period} {$statusText}");

        return back()->with('success', "Visi & Misi berhasil {$statusText}.");
    }

    /**
     * Get active vision/mission for public display.
     */
    public function getActive()
    {
        $visionMission = VisionMission::active()->first();

        return response()->json([
            'visionMission' => $visionMission,
        ]);
    }
}
