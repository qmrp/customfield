<?php

namespace Qmrp\CustomField\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldUserSetting extends Model
{
    protected $table = 'custom_fields_user_settings';

    protected $fillable = [
        'module',
        'custom_module_field_id',
        'user_id',
        'sort_order',
        'is_show',
        'is_fixed'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_show' => 'boolean',
        'is_fixed' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
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
        return $query->where('is_show', 1);
    }

    public function scopeSortOrder($query)
    {
        return $query->orderBy('sort_order');
    }
}
