<?php

namespace Qmrp\CustomField\Traits;

use Qmrp\CustomField\Config\FieldConfigManager;
use Qmrp\CustomField\Types\FieldTypeRegistry;
use Illuminate\Support\Collection;

trait HasCustomFields
{
    protected $customFieldManager;

    protected $customFieldsCache = [];

    protected $withCustomFields = false;

    protected $customFieldsModule = null;

    protected $customFieldsUserId = null;

    protected $customFieldsOnlyVisible = true;

    public function scopeWithCustomFields($query, string $module = null, $userId = null, bool $onlyVisible = true)
    {
        $module = $module ?? $this->getCustomFieldsModule();
        $this->withCustomFields = true;
        $this->customFieldsModule = $module;
        $this->customFieldsUserId = $userId;
        $this->customFieldsOnlyVisible = $onlyVisible;

        return $query;
    }

    public function getCustomFieldsModule(): string
    {
        return $this->customFieldsModule ?? strtolower(class_basename($this));
    }

    public function getCustomFieldManager(): FieldConfigManager
    {
        if (!$this->customFieldManager) {
            $this->customFieldManager = app(FieldConfigManager::class);
        }
        return $this->customFieldManager;
    }

    public function getCustomFields(): Collection
    {
        if ($this->customFieldsCache) {
            return collect($this->customFieldsCache);
        }

        $module = $this->getCustomFieldsModule();
        $fields = $this->getCustomFieldManager()->getModuleFields($module);

        $customFields = collect();

        foreach ($fields as $fieldConfig) {
            $value = $this->getCustomFieldValue($fieldConfig);
            $customFields->put($fieldConfig['key'], $value);
        }

        $this->customFieldsCache = $customFields->toArray();

        return $customFields;
    }

    public function getCustomFieldValue(array $fieldConfig)
    {
        $type = $fieldConfig['type'];
        $config = $fieldConfig['config'] ?? [];

        $fieldType = FieldTypeRegistry::get($type);

        if (!$fieldType) {
            return $config['default'] ?? null;
        }

        return $fieldType->getValue($this, $config);
    }

    public function getCustomField(string $key)
    {
        $module = $this->getCustomFieldsModule();
        $fieldConfig = $this->getCustomFieldManager()->getFieldConfig($module, $key);

        if (!$fieldConfig) {
            return null;
        }

        return $this->getCustomFieldValue($fieldConfig);
    }

    public function getCustomFieldsArray(): array
    {
        return $this->getCustomFields()->toArray();
    }

    public function toArrayWithCustomFields(): array
    {
        $array = $this->toArray();
        $customFields = $this->getCustomFieldsArray();

        return array_merge($array, $customFields);
    }

    public function getVisibleCustomFields(): Collection
    {
        if (!$this->customFieldsUserId) {
            return $this->getCustomFields();
        }

        $module = $this->getCustomFieldsModule();
        $visibleFields = \Qmrp\CustomField\Models\CustomFieldUserSetting::byModule($module)
            ->byUser($this->customFieldsUserId)
            ->visible()
            ->pluck('custom_module_field_id')
            ->toArray();

        if (empty($visibleFields)) {
            return collect();
        }

        $allFields = $this->getCustomFields();
        $moduleFields = $this->getCustomFieldManager()->getModuleFields($module);

        return $allFields->filter(function ($value, $key) use ($moduleFields, $visibleFields) {
            $field = collect($moduleFields)->firstWhere('key', $key);
            return $field && in_array($field['id'], $visibleFields);
        });
    }

    protected function initializeHasCustomFields()
    {
        FieldTypeRegistry::registerDefaults();
    }

    public function newCollection(array $models = [])
    {
        $collection = parent::newCollection($models);

        if ($this->withCustomFields) {
            $collection->transform(function ($model) {
                $model->withCustomFields = true;
                $model->customFieldsModule = $this->customFieldsModule;
                $model->customFieldsUserId = $this->customFieldsUserId;
                $model->customFieldsOnlyVisible = $this->customFieldsOnlyVisible;
                return $model;
            });
        }

        return $collection;
    }

    public function getCustomFieldsDefinition(): array
    {
        $module = $this->getCustomFieldsModule();
        return $this->getCustomFieldManager()->getModuleFields($module);
    }

    public function hasCustomField(string $key): bool
    {
        $module = $this->getCustomFieldsModule();
        $fieldConfig = $this->getCustomFieldManager()->getFieldConfig($module, $key);
        return $fieldConfig !== null;
    }
}
