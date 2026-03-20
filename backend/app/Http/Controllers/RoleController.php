<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
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

    public function index()
    {
        $roles = User::getRoles();
        $userCounts = \App\Models\Position::withCount('members')->pluck('members_count', 'code')->toArray();

        // Get current permissions for each position
        $positions = \App\Models\Position::orderBy('id')->get()->pluck('name', 'code')->toArray();
        $rolePermissions = [];
        foreach ($positions as $key => $label) {
            $rolePermissions[$key] = Setting::getValue("role_permissions_{$key}", []);
        }

        // Get full position models for the unified management tab
        $positionsList = \App\Models\Position::withCount('members')->orderBy('id', 'asc')->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'userCounts' => $userCounts,
            'rolePermissions' => $rolePermissions,
            'positions' => $positions,
            'positionsList' => $positionsList,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'array',
        ]);

        foreach ($validated['permissions'] as $role => $perms) {
            // Only allow updating non-admin roles
            if ($role === 'admin') continue;

            $key = "role_permissions_{$role}";
            Setting::where('key', $key)->update([
                'value' => json_encode(array_values($perms)),
            ]);
        }

        return back()->with('success', 'Permission role berhasil diperbarui.');
    }
}
