<?php

namespace Qmrp\CustomField\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldTemplate extends Model
{
    protected $table = 'custom_field_templates';

    protected $fillable = [
        'name',
        'description',
        'module',
        'fields',
        'is_public',
        'created_by'
    ];

    protected $casts = [
        'fields' => 'array',
        'is_public' => 'boolean'
    ];

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
