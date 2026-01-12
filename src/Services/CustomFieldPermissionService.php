<?php

namespace Qmrp\CustomField\Services;

use Qmrp\CustomField\Models\CustomFieldPermission;
use Illuminate\Support\Facades\DB;

class CustomFieldPermissionService
{
    public function grantPermission(int $fieldId, string $permissionType, ?int $userId = null, ?int $roleId = null): bool
    {
        if (!$userId && !$roleId) {
            throw new \InvalidArgumentException('Either user_id or role_id must be provided');
        }

        return CustomFieldPermission::updateOrCreate(
            [
                'custom_module_field_id' => $fieldId,
                'permission_type' => $permissionType,
                'user_id' => $userId,
                'role_id' => $roleId
            ],
            ['is_allowed' => true]
        ) ? true : false;
    }

    public function revokePermission(int $fieldId, string $permissionType, ?int $userId = null, ?int $roleId = null): bool
    {
        $query = CustomFieldPermission::where('custom_module_field_id', $fieldId)
            ->where('permission_type', $permissionType);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->delete() > 0;
    }

    public function checkPermission(int $fieldId, string $permissionType, ?int $userId = null, ?int $roleId = null): bool
    {
        $query = CustomFieldPermission::where('custom_module_field_id', $fieldId)
            ->where('permission_type', $permissionType)
            ->allowed();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->exists();
    }

    public function getFieldPermissions(int $fieldId): array
    {
        return CustomFieldPermission::where('custom_module_field_id', $fieldId)
            ->with('field')
            ->get()
            ->toArray();
    }

    public function getUserPermissions(int $userId, string $module = null): array
    {
        $query = CustomFieldPermission::where('user_id', $userId);

        if ($module) {
            $query->where('module', $module);
        }

        return $query->with('field')->get()->toArray();
    }

    public function getRolePermissions(int $roleId, string $module = null): array
    {
        $query = CustomFieldPermission::where('role_id', $roleId);

        if ($module) {
            $query->where('module', $module);
        }

        return $query->with('field')->get()->toArray();
    }

    public function setDefaultPermissions(int $fieldId, array $permissions): bool
    {
        try {
            DB::beginTransaction();

            foreach ($permissions as $permissionType => $config) {
                if ($config['enabled'] ?? false) {
                    if (isset($config['role_ids'])) {
                        foreach ($config['role_ids'] as $roleId) {
                            $this->grantPermission($fieldId, $permissionType, null, $roleId);
                        }
                    }

                    if (isset($config['user_ids'])) {
                        foreach ($config['user_ids'] as $userId) {
                            $this->grantPermission($fieldId, $permissionType, $userId, null);
                        }
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function copyPermissions(int $sourceFieldId, int $targetFieldId): bool
    {
        try {
            DB::beginTransaction();

            $sourcePermissions = CustomFieldPermission::where('custom_module_field_id', $sourceFieldId)
                ->get();

            foreach ($sourcePermissions as $permission) {
                CustomFieldPermission::updateOrCreate(
                    [
                        'custom_module_field_id' => $targetFieldId,
                        'permission_type' => $permission->permission_type,
                        'user_id' => $permission->user_id,
                        'role_id' => $permission->role_id
                    ],
                    [
                        'is_allowed' => $permission->is_allowed,
                        'module' => $permission->module
                    ]
                );
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getAccessibleFields(string $module, int $userId, string $permissionType = 'view'): array
    {
        $accessibleFieldIds = CustomFieldPermission::where('module', $module)
            ->where('permission_type', $permissionType)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereIn('role_id', function ($subQuery) use ($userId) {
                        $subQuery->select('role_id')
                            ->from('role_user')
                            ->where('user_id', $userId);
                    });
            })
            ->allowed()
            ->pluck('custom_module_field_id')
            ->unique()
            ->toArray();

        return \Qmrp\CustomField\Models\CustomModuleField::whereIn('id', $accessibleFieldIds)
            ->active()
            ->ordered()
            ->get()
            ->toArray();
    }
}
