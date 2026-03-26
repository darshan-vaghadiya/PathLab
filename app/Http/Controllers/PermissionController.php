<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        $roles = ['admin', 'receptionist', 'technician', 'doctor'];

        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role] = RolePermission::where('role', $role)
                ->pluck('permission_id')
                ->toArray();
        }

        return Inertia::render('Admin/Permissions', [
            'permissions' => $permissions,
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*' => 'array',
            'assignments.*.*' => 'exists:permissions,id',
        ]);

        foreach ($validated['assignments'] as $role => $permissionIds) {
            RolePermission::where('role', $role)->delete();
            foreach ($permissionIds as $permId) {
                RolePermission::create(['role' => $role, 'permission_id' => $permId]);
            }
        }

        Permission::clearCache();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully.');
    }
}
