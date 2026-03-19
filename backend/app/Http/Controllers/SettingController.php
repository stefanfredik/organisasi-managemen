<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            $setting = Setting::where('key', $item['key'])->first();
            if (!$setting) continue;

            // Skip image fields here — they are handled separately
            if ($setting->type === 'image') continue;

            $setting->update(['value' => $item['value']]);
        }

        // Handle image uploads
        $imageFields = ['organization_logo', 'organization_favicon', 'qris_image', 'portal_hero_image'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $request->validate([
                    $field => 'file|mimes:png,jpg,jpeg,gif,ico,svg,webp|max:2048',
                ]);

                $setting = Setting::where('key', $field)->first();
                if (!$setting) continue;

                // Delete old file
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }

                $path = $request->file($field)->store('settings', 'public');
                $setting->update(['value' => $path]);
            }

            // Handle image removal
            if ($request->input("remove_{$field}") === '1') {
                $setting = Setting::where('key', $field)->first();
                if ($setting && $setting->value) {
                    if (Storage::disk('public')->exists($setting->value)) {
                        Storage::disk('public')->delete($setting->value);
                    }
                    $setting->update(['value' => null]);
                }
            }
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
