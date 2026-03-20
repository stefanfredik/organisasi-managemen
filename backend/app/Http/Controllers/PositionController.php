<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->role !== 'admin') {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    // Index is now handled by RoleController

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Positions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionRequest $request)
    {
        Position::create($request->validated());

        return redirect()->route('positions.index')
            ->with('success', 'Posisi baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        return Inertia::render('Positions/Edit', [
            'position' => $position
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionRequest $request, Position $position)
    {
        $position->update($request->validated());

        return redirect()->route('positions.index')
            ->with('success', 'Posisi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        if ($position->members()->count() > 0) {
            return back()->withErrors(['message' => 'Posisi ini tidak dapat dihapus karena masih digunakan oleh anggota.']);
        }

        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Posisi berhasil dihapus.');
    }
}
