<?php

namespace App\Http\Controllers;

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
        $userCounts = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Get current permissions for each non-admin role
        $rolePermissions = [];
        foreach ($roles as $key => $label) {
            if ($key === 'admin') continue;
            $rolePermissions[$key] = Setting::getValue("role_permissions_{$key}", []);
        }

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'userCounts' => $userCounts,
            'rolePermissions' => $rolePermissions,
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
