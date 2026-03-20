<?php

namespace App\Http\Controllers;

use App\Models\Arisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ArisanController extends Controller
{
    public function index()
    {
        $arisans = Arisan::withCount('participants')->latest()->get();
        return Inertia::render('Arisans/Index', compact('arisans'));
    }

    public function create()
    {
        $allMembers = \App\Models\Member::where('status', 'active')->get();
        return Inertia::render('Arisans/Create', compact('allMembers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount_per_month' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'participant_ids'   => 'nullable|array',
            'participant_ids.*' => 'exists:members,id',
        ]);

        DB::transaction(function() use ($validated) {
            $arisan = Arisan::create([
                'name' => $validated['name'],
                'amount_per_month' => $validated['amount_per_month'],
                'start_date' => $validated['start_date'],
            ]);

            if (!empty($validated['participant_ids'])) {
                foreach ($validated['participant_ids'] as $memberId) {
                    $arisan->participants()->create([
                        'member_id' => $memberId
                    ]);
                }
            }
        });

        return redirect()->route('arisans.index')->with('success', 'Program Arisan beserta perwakilan pesertanya telah dibuat.');
    }

    public function show(Arisan $arisan)
    {
        // Load dependencies (participants with members, draws with winners, payments)
        $arisan->load([
            'participants' => function($q) {
                $q->with(['member' => function($q2) {
                    $q2->withTrashed();
                }]);
            },
            'draws' => function($q) {
                $q->latest('period_month')->with(['winners' => function($q2) {
                    $q2->with(['member' => function($q3) {
                        $q3->withTrashed();
                    }]);
                }]);
            },
            'payments' => function($q) {
                $q->with(['member' => function($q2) {
                    $q2->withTrashed();
                }]);
            }
        ]);
        
        $allMembers = \App\Models\Member::where('status', 'active')->get();
        
        return Inertia::render('Arisans/Show', [
            'arisan' => $arisan,
            'allMembers' => $allMembers
        ]);
    }

    public function edit(Arisan $arisan)
    {
        return Inertia::render('Arisans/Edit', compact('arisan'));
    }

    public function update(Request $request, Arisan $arisan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount_per_month' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'status' => 'required|in:active,completed',
        ]);

        $arisan->update($validated);

        return redirect()->route('arisans.show', $arisan->id)->with('success', 'Rincian arisan berhasil diperbarui.');
    }

    public function destroy(Arisan $arisan)
    {
        $arisan->delete();
        return redirect()->route('arisans.index')->with('success', 'Program Arisan berhasil dihapus beserta rekam jejaknya.');
    }
}
