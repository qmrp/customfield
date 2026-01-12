<?php

namespace Qmrp\CustomField\Models;

use Illuminate\Database\Eloquent\Model;

class CustomModuleField extends Model
{
    protected $table = 'custom_module_fields';

    protected $fillable = [
        'module',
        'key',
        'name',
        'type',
        'config',
        'is_fixed',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'config' => 'array',
        'is_fixed' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function userSettings()
    {
        return $this->hasMany(CustomFieldUserSetting::class, 'custom_module_field_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
