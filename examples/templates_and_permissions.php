<?php

use Qmrp\CustomField\Services\CustomFieldService;
use Qmrp\CustomField\Services\CustomFieldPermissionService;

$service = app('customFieldService');
$permissionService = app(CustomFieldPermissionService::class);

$template = $service->createTemplate(
    '产品标准模板',
    'product',
    [
        [
            'key' => 'total_price',
            'name' => '总价',
            'type' => 'computed',
            'config' => [
                'callback' => function ($model) {
                    return $model->price * $model->quantity;
                }
            ],
            'sort_order' => 1,
            'is_active' => true
        ],
        [
            'key' => 'category_name',
            'name' => '分类名称',
            'type' => 'relation',
            'config' => [
                'relation' => 'category',
                'display_field' => 'name'
            ],
            'sort_order' => 2,
            'is_active' => true
        ]
    ],
    true,
    1
);

$service->applyTemplate('product', $template->id);

$newTemplate = $service->duplicateTemplate($template->id, '产品模板副本', 2);

$templates = $service->getTemplates('product', 1);

$exportData = $service->exportModuleFields('product');

$service->importModuleFields($exportData);

$field = $service->getField('product', 'total_price');

$permissionService->grantPermission($field['id'], 'view', 1);
$permissionService->grantPermission($field['id'], 'edit', null, 1);

$hasPermission = $permissionService->checkPermission($field['id'], 'view', 1);

$accessibleFields = $permissionService->getAccessibleFields('product', 1, 'view');

$permissionService->setDefaultPermissions($field['id'], [
    'view' => [
        'enabled' => true,
        'role_ids' => [1, 2],
        'user_ids' => [1, 2, 3]
    ],
    'edit' => [
        'enabled' => true,
        'role_ids' => [1]
    ],
    'delete' => [
        'enabled' => false
    ]
]);
