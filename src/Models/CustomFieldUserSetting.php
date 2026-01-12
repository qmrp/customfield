<?php

namespace Qmrp\CustomField\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldUserSetting extends Model
{
    protected $table = 'custom_fields_user_settings';

    protected $fillable = [
        'module',
        'custom_module_field_id',
        'sort_order',
        'is_show',
        'is_fixed'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_show' => 'boolean',
        'is_fixed' => 'boolean'
    ];

    public function field()
    {
        return $this->belongsTo(CustomModuleField::class, 'custom_module_field_id');
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_show', true);
    }
}
