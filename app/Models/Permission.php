<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    use HasUlid;

    protected $fillable = ['name', 'slug', 'group', 'description'];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    public static function hasPermission(string $role, string $slug): bool
    {
        $perms = Cache::remember("permissions.{$role}", 300, function () use ($role) {
            return RolePermission::where('role', $role)
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->pluck('permissions.slug')
                ->toArray();
        });

        return in_array($slug, $perms);
    }

    public static function clearCache(): void
    {
        foreach (['admin', 'receptionist', 'technician', 'doctor'] as $role) {
            Cache::forget("permissions.{$role}");
        }
    }
}
