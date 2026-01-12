<?php

namespace Qmrp\CustomField\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomFieldResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'module' => $this->module,
            'key' => $this->key,
            'name' => $this->name,
            'type' => $this->type,
            'config' => $this->config,
            'is_fixed' => $this->is_fixed,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
