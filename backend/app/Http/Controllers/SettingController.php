<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
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
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $roles = User::getRoles();

        return Inertia::render('Administration/Settings/Index', [
            'settings' => $settings,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $item) {
            Setting::where('key', $item['key'])->update(['value' => $item['value']]);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
