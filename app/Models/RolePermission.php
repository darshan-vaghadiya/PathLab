<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasUlid;

    protected $fillable = ['role', 'permission_id'];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
