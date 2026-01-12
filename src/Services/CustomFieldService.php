<?php

namespace Qmrp\CustomField\Services;

use Qmrp\CustomField\Config\FieldConfigManager;
use Qmrp\CustomField\Models\CustomModuleField;
use Qmrp\CustomField\Models\CustomFieldUserSetting;
use Qmrp\CustomField\Models\CustomFieldTemplate;
use Qmrp\CustomField\Types\FieldTypeRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomFieldService
{
    protected $configManager;

    public function __construct(FieldConfigManager $configManager = null)
    {
        $this->configManager = $configManager ?? app(FieldConfigManager::class);
        FieldTypeRegistry::registerDefaults();
    }

    public function saveModuleFields(string $module, array $fields): bool
    {
        try {
            DB::beginTransaction();

            foreach ($fields as $fieldData) {
                $this->saveModuleField($module, $fieldData);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save module fields', [
                'module' => $module,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function saveModuleField(string $module, array $fieldData): CustomModuleField
    {
        $key = $fieldData['key'];
        $config = $fieldData['config'] ?? [];

        $this->validateFieldConfig($fieldData['type'], $config);

        $fieldData['module'] = $module;

        return CustomModuleField::updateOrCreate(
            ['module' => $module, 'key' => $key],
            $fieldData
        );
    }

    protected function validateFieldConfig(string $type, array $config): void
    {
        $fieldType = FieldTypeRegistry::get($type);

        if (!$fieldType) {
            throw new \InvalidArgumentException("Unknown field type: {$type}");
        }

        if (!$fieldType->validate($config)) {
            throw new \InvalidArgumentException("Invalid field configuration for type: {$type}");
        }
    }

    public function getModuleFields(string $module): array
    {
        return $this->configManager->getModuleFields($module);
    }

    public function getField(string $module, string $key): ?array
    {
        return $this->configManager->getFieldConfig($module, $key);
    }

    public function deleteField(string $module, string $key): bool
    {
        return $this->configManager->deleteFieldConfig($module, $key);
    }

    public function saveUserSettings(string $module, int $userId, array $settings): bool
    {
        try {
            DB::beginTransaction();

            foreach ($settings as $setting) {
                $this->saveUserSetting($module, $userId, $setting);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save user settings', [
                'module' => $module,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function saveUserSetting(string $module, int $userId, array $setting): CustomFieldUserSetting
    {
        $fieldId = $setting['custom_module_field_id'];

        // 验证字段是否存在
        $field = CustomModuleField::where('id', $fieldId)->where('module', $module)->first();
        if (!$field) {
            throw new \InvalidArgumentException("Field with ID {$fieldId} not found for module {$module}");
        }

        return CustomFieldUserSetting::updateOrCreate(
            [
                'module' => $module,
                'custom_module_field_id' => $fieldId,
                'user_id' => $userId
            ],
            [
                'sort_order' => $setting['sort_order'] ?? 0,
                'is_show' => $setting['is_show'] ?? true,
                'is_fixed' => $setting['is_fixed'] ?? false
            ]
        );
    }

    public function getUserSettings(string $module, int $userId): array
    {
        return CustomFieldUserSetting::byModule($module)
            ->byUser($userId)
            ->with('field')
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function createTemplate(string $name, string $module, array $fields, bool $isPublic = false, ?int $createdBy = null): CustomFieldTemplate
    {
        return $this->configManager->createTemplate($name, $module, $fields, $isPublic, $createdBy);
    }

    public function getTemplates(string $module, ?int $userId = null): array
    {
        return $this->configManager->getTemplates($module, $userId);
    }

    public function applyTemplate(string $module, int $templateId): bool
    {
        return $this->configManager->applyTemplate($module, $templateId);
    }

    public function duplicateTemplate(int $templateId, string $newName, ?int $createdBy = null): ?CustomFieldTemplate
    {
        return $this->configManager->duplicateTemplate($templateId, $newName, $createdBy);
    }

    public function deleteTemplate(int $templateId): bool
    {
        return CustomFieldTemplate::destroy($templateId) > 0;
    }

    public function registerFieldType(string $type, $fieldType): void
    {
        FieldTypeRegistry::register($type, $fieldType);
    }

    public function getAvailableFieldTypes(): array
    {
        $types = FieldTypeRegistry::all();
        $result = [];

        foreach ($types as $key => $type) {
            $result[$key] = [
                'type' => $type->getType(),
                'name' => $type->getName()
            ];
        }

        return $result;
    }

    public function exportModuleFields(string $module): array
    {
        $fields = $this->getModuleFields($module);

        return [
            'module' => $module,
            'exported_at' => now()->toIso8601String(),
            'fields' => $fields
        ];
    }

    public function importModuleFields(array $data): bool
    {
        try {
            DB::beginTransaction();

            $module = $data['module'] ?? null;
            $fields = $data['fields'] ?? [];

            if (!$module || empty($fields)) {
                throw new \InvalidArgumentException('Invalid import data');
            }

            foreach ($fields as $fieldData) {
                $this->saveModuleField($module, $fieldData);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import module fields', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getFieldsForTable(string $module, ?int $userId = null): array
    {
        // $fields = $this->getModuleFields($module);

        if ($userId) {
            $userSettings = $this->getUserSettings($module, $userId);
            foreach ($userSettings as &$setting) {
                $setting['key'] = $setting['field']['key'];
                $setting['name'] = $setting['field']['name'];
                unset($setting['field']);
            }
            return $userSettings;
        }

        return [];
    }
}
