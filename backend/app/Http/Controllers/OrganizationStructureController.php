<?php

namespace App\Http\Controllers;

use App\Models\OrganizationStructure;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizationStructureController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', OrganizationStructure::class);

        $structures = OrganizationStructure::with(['member', 'parent', 'children'])
            ->when($request->search, function ($query, $search) {
                $query->where('position_name', 'like', "%{$search}%")
                      ->orWhereHas('member', function ($q) use ($search) {
                          $q->where('full_name', 'like', "%{$search}%");
                      });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->active();
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('level')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('OrganizationStructures/Index', [
            'structures' => $structures,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', OrganizationStructure::class);

        return Inertia::render('OrganizationStructures/Create', [
            'members' => Member::select('id', 'full_name')->orderBy('full_name')->get(),
            'parents' => OrganizationStructure::select('id', 'position_name')->orderBy('position_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', OrganizationStructure::class);

        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'position_name' => 'required|string|max:255',
            'level' => 'required|integer|min:0',
            'parent_id' => 'nullable|exists:organization_structures,id',
            'sort_order' => 'required|integer|min:0',
            'period_start' => 'required|integer|min:2000|max:2100',
            'period_end' => 'nullable|integer|min:2000|max:2100|gte:period_start',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('organization-structures', 'public');
        }

        OrganizationStructure::create($validated);

        return redirect()->route('organization-structures.index')
            ->with('success', 'Structure created successfully.');
    }

    public function edit(OrganizationStructure $organizationStructure)
    {
        $this->authorize('update', $organizationStructure);

        return Inertia::render('OrganizationStructures/Edit', [
            'structure' => $organizationStructure->load('member'),
            'members' => Member::select('id', 'full_name')->orderBy('full_name')->get(),
            'parents' => OrganizationStructure::where('id', '!=', $organizationStructure->id)
                ->select('id', 'position_name')
                ->orderBy('position_name')
                ->get(),
        ]);
    }

    public function update(Request $request, OrganizationStructure $organizationStructure)
    {
        $this->authorize('update', $organizationStructure);

        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'position_name' => 'required|string|max:255',
            'level' => 'required|integer|min:0',
            'parent_id' => 'nullable|exists:organization_structures,id',
            'sort_order' => 'required|integer|min:0',
            'period_start' => 'required|integer|min:2000|max:2100',
            'period_end' => 'nullable|integer|min:2000|max:2100|gte:period_start',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($organizationStructure->photo_path) {
                Storage::disk('public')->delete($organizationStructure->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('organization-structures', 'public');
        }

        $organizationStructure->update($validated);

        return redirect()->route('organization-structures.index')
            ->with('success', 'Structure updated successfully.');
    }

    public function destroy(OrganizationStructure $organizationStructure)
    {
        $this->authorize('delete', $organizationStructure);

        if ($organizationStructure->photo_path) {
            Storage::disk('public')->delete($organizationStructure->photo_path);
        }

        $organizationStructure->delete();

        return redirect()->route('organization-structures.index')
            ->with('success', 'Structure deleted successfully.');
    }
}
