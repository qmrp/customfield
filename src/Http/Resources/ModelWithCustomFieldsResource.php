<?php

namespace Qmrp\CustomField\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModelWithCustomFieldsResource extends JsonResource
{
    protected $module;

    protected $userId;

    public function __construct($resource, string $module = null, int $userId = null)
    {
        parent::__construct($resource);
        $this->module = $module;
        $this->userId = $userId;
    }

    public function toArray($request)
    {
        $data = $this->resource->toArray();

        if ($this->resource && method_exists($this->resource, 'getCustomFields')) {
            $customFields = $this->userId 
                ? $this->resource->getVisibleCustomFields()
                : $this->resource->getCustomFields();
            
            $data['custom_fields'] = $customFields->toArray();
        }

        return $data;
    }
}
