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
        $userSettings = \Qmrp\CustomField\Models\CustomFieldUserSetting::byModule($module)
            ->byUser($this->customFieldsUserId)
            ->visible()
            ->sortOrder()
            ->get(['custom_module_field_id']);
        
        if ($userSettings->isEmpty()) {
            return collect();
        }

        // 获取可见字段的ID列表
        $visibleFieldIds = $userSettings->pluck('custom_module_field_id')->toArray();
        
        $allFields = $this->getCustomFields();
        $moduleFields = $this->getCustomFieldManager()->getModuleFields($module);
        
        // 创建字段ID到字段key的映射
        $fieldIdToKey = collect($moduleFields)->keyBy('id')->map(function ($field) {
            return $field['key'];
        });
        
        // 根据用户设置的排序顺序构建字段key列表
        $sortedKeys = $userSettings->map(function ($setting) use ($fieldIdToKey) {
            return $fieldIdToKey[$setting->custom_module_field_id] ?? null;
        })->filter()->toArray();
        
        // 过滤可见字段
        $visibleFields = $allFields->filter(function ($value, $key) use ($moduleFields, $visibleFieldIds) {
            $field = collect($moduleFields)->firstWhere('key', $key);
            return $field && in_array($field['id'], $visibleFieldIds);
        });
        
        // 根据用户设置的顺序对可见字段进行排序
        $sortedFields = collect();
        foreach ($sortedKeys as $key) {
            if ($visibleFields->has($key)) {
                $sortedFields->put($key, $visibleFields->get($key));
            }
        }
        
        return $sortedFields;
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
