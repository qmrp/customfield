<?php

namespace Qmrp\CustomField\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomFieldTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'module' => $this->module,
            'fields' => $this->fields,
            'is_public' => $this->is_public,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
