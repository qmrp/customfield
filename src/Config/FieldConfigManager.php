<?php

namespace Qmrp\CustomField\Config;

use Qmrp\CustomField\Models\CustomModuleField;
use Qmrp\CustomField\Models\CustomFieldTemplate;
use Illuminate\Support\Facades\Cache;

class FieldConfigManager
{
    protected $cachePrefix = 'custom_field_config_';

    protected $cacheTtl = 3600;

    public function getFieldConfig(string $module, string $key): ?array
    {
        $cacheKey = $this->getCacheKey($module, $key);
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($module, $key) {
            $field = CustomModuleField::byModule($module)
                ->where('key', $key)
                ->active()
                ->first();

            return $field ? $field->toArray() : null;
        });
    }

    public function getModuleFields(string $module): array
    {
        $cacheKey = $this->cachePrefix . 'module_' . $module;
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($module) {
            return CustomModuleField::byModule($module)
                ->active()
                ->ordered()
                ->get()
                ->toArray();
        });
    }

    public function saveFieldConfig(string $module, string $key, array $config): bool
    {
        $this->clearModuleCache($module);

        return CustomModuleField::updateOrCreate(
            ['module' => $module, 'key' => $key],
            $config
        ) ? true : false;
    }

    public function deleteFieldConfig(string $module, string $key): bool
    {
        $this->clearModuleCache($module);

        return CustomModuleField::byModule($module)
            ->where('key', $key)
            ->delete() > 0;
    }

    public function createTemplate(string $name, string $module, array $fields, bool $isPublic = false, ?int $createdBy = null): CustomFieldTemplate
    {
        return CustomFieldTemplate::create([
            'name' => $name,
            'description' => $fields['description'] ?? '',
            'module' => $module,
            'fields' => $fields,
            'is_public' => $isPublic,
            'created_by' => $createdBy
        ]);
    }

    public function getTemplate(int $templateId): ?CustomFieldTemplate
    {
        return CustomFieldTemplate::find($templateId);
    }

    public function getTemplates(string $module, ?int $userId = null): array
    {
        $query = CustomFieldTemplate::byModule($module);

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('is_public', true)->orWhere('created_by', $userId);
            });
        } else {
            $query->public();
        }

        return $query->get()->toArray();
    }

    public function applyTemplate(string $module, int $templateId): bool
    {
        $template = $this->getTemplate($templateId);

        if (!$template || $template->module !== $module) {
            return false;
        }

        $this->clearModuleCache($module);

        foreach ($template->fields as $fieldConfig) {
            CustomModuleField::updateOrCreate(
                [
                    'module' => $module,
                    'key' => $fieldConfig['key']
                ],
                $fieldConfig
            );
        }

        return true;
    }

    public function duplicateTemplate(int $templateId, string $newName, ?int $createdBy = null): ?CustomFieldTemplate
    {
        $template = $this->getTemplate($templateId);

        if (!$template) {
            return null;
        }

        return CustomFieldTemplate::create([
            'name' => $newName,
            'description' => $template->description,
            'module' => $template->module,
            'fields' => $template->fields,
            'is_public' => false,
            'created_by' => $createdBy
        ]);
    }

    protected function getCacheKey(string $module, string $key): string
    {
        return $this->cachePrefix . $module . '_' . $key;
    }

    protected function clearModuleCache(string $module): void
    {
        Cache::forget($this->cachePrefix . 'module_' . $module);
        
        $fields = CustomModuleField::byModule($module)->get(['key']);
        foreach ($fields as $field) {
            Cache::forget($this->getCacheKey($module, $field->key));
        }
    }

    public function setCacheTtl(int $seconds): void
    {
        $this->cacheTtl = $seconds;
    }
}
