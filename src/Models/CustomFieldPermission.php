<?php

namespace Qmrp\CustomField\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldPermission extends Model
{
    protected $table = 'custom_field_permissions';

    protected $fillable = [
        'module',
        'custom_module_field_id',
        'permission_type',
        'role_id',
        'user_id',
        'is_allowed'
    ];

    protected $casts = [
        'is_allowed' => 'boolean'
    ];

    public function field()
    {
        return $this->belongsTo(CustomModuleField::class, 'custom_module_field_id');
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByPermissionType($query, string $type)
    {
        return $query->where('permission_type', $type);
    }

    public function scopeByRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAllowed($query)
    {
        return $query->where('is_allowed', true);
    }
}
