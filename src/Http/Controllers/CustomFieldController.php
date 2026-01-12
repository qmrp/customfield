<?php

namespace Qmrp\CustomField\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Qmrp\CustomField\Services\CustomFieldService;
use Qmrp\CustomField\Http\Resources\CustomFieldResource;
use Qmrp\CustomField\Http\Resources\CustomFieldTemplateResource;
use Qmrp\CustomField\Http\Controllers\Controller as BaseController;

class CustomFieldController extends BaseController
{
    protected $service;

    public function __construct(CustomFieldService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $module = $request->input('module');
        $userId = $request->input('user_id');

        if (!$module) {
            return response()->json(['error' => 'Module is required'], 422);
        }

        $fields = $this->service->getFieldsForTable($module, $userId);

        return response()->json([
            'data' => $fields,
            'total' => count($fields)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'fields' => 'required|array',
            'fields.*.key' => 'required|string|max:50',
            'fields.*.name' => 'required|string|max:50',
            'fields.*.type' => 'required|string|max:20',
            'fields.*.config' => 'nullable|array',
            'fields.*.is_fixed' => 'nullable|boolean',
            'fields.*.sort_order' => 'nullable|integer',
            'fields.*.is_active' => 'nullable|boolean'
        ]);

        $module = $validated['module'];
        $fields = $validated['fields'];

        $success = $this->service->saveModuleFields($module, $fields);

        if ($success) {
            return response()->json([
                'message' => 'Custom fields saved successfully',
                'data' => $this->service->getModuleFields($module)
            ], 201);
        }

        return response()->json(['error' => 'Failed to save custom fields'], 500);
    }

    public function show(Request $request, string $module, string $key): JsonResponse
    {
        $field = $this->service->getField($module, $key);

        if (!$field) {
            return response()->json(['error' => 'Field not found'], 404);
        }

        return response()->json(['data' => $field]);
    }

    public function update(Request $request, string $module, string $key): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:20',
            'config' => 'nullable|array',
            'is_fixed' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ]);

        $fieldData = array_merge(['key' => $key], $validated);

        try {
            $field = $this->service->saveModuleField($module, $fieldData);
            return response()->json([
                'message' => 'Field updated successfully',
                'data' => $field->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(string $module, string $key): JsonResponse
    {
        $success = $this->service->deleteField($module, $key);

        if ($success) {
            return response()->json(['message' => 'Field deleted successfully']);
        }

        return response()->json(['error' => 'Failed to delete field'], 500);
    }

    public function getUserSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'user_id' => 'required|integer'
        ]);

        $settings = $this->service->getUserSettings(
            $validated['module'],
            $validated['user_id']
        );

        return response()->json(['data' => $settings]);
    }

    public function saveUserSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'user_id' => 'required|integer',
            'settings' => 'required|array',
            'settings.*.custom_module_field_id' => 'required|integer',
            'settings.*.sort_order' => 'nullable|integer',
            'settings.*.is_show' => 'nullable|boolean',
            'settings.*.is_fixed' => 'nullable|boolean'
        ]);

        $success = $this->service->saveUserSettings(
            $validated['module'],
            $validated['user_id'],
            $validated['settings']
        );

        if ($success) {
            return response()->json(['message' => 'User settings saved successfully']);
        }

        return response()->json(['error' => 'Failed to save user settings'], 500);
    }

    public function getTemplates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'user_id' => 'nullable|integer'
        ]);

        $templates = $this->service->getTemplates(
            $validated['module'],
            $validated['user_id'] ?? null
        );

        return response()->json(['data' => $templates]);
    }

    public function createTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:50',
            'fields' => 'required|array',
            'is_public' => 'nullable|boolean',
            'created_by' => 'nullable|integer'
        ]);

        $template = $this->service->createTemplate(
            $validated['name'],
            $validated['module'],
            $validated['fields'],
            $validated['is_public'] ?? false,
            $validated['created_by'] ?? null
        );

        return response()->json([
            'message' => 'Template created successfully',
            'data' => new CustomFieldTemplateResource($template)
        ], 201);
    }

    public function applyTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'template_id' => 'required|integer'
        ]);

        $success = $this->service->applyTemplate(
            $validated['module'],
            $validated['template_id']
        );

        if ($success) {
            return response()->json(['message' => 'Template applied successfully']);
        }

        return response()->json(['error' => 'Failed to apply template'], 500);
    }

    public function duplicateTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|integer',
            'new_name' => 'required|string|max:255',
            'created_by' => 'nullable|integer'
        ]);

        $template = $this->service->duplicateTemplate(
            $validated['template_id'],
            $validated['new_name'],
            $validated['created_by'] ?? null
        );

        if ($template) {
            return response()->json([
                'message' => 'Template duplicated successfully',
                'data' => new CustomFieldTemplateResource($template)
            ]);
        }

        return response()->json(['error' => 'Failed to duplicate template'], 500);
    }

    public function deleteTemplate(int $templateId): JsonResponse
    {
        $success = $this->service->deleteTemplate($templateId);

        if ($success) {
            return response()->json(['message' => 'Template deleted successfully']);
        }

        return response()->json(['error' => 'Failed to delete template'], 500);
    }

    public function getAvailableFieldTypes(): JsonResponse
    {
        $types = $this->service->getAvailableFieldTypes();

        return response()->json(['data' => $types]);
    }

    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50'
        ]);

        $data = $this->service->exportModuleFields($validated['module']);

        return response()->json($data);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'data.module' => 'required|string|max:50',
            'data.fields' => 'required|array'
        ]);

        $success = $this->service->importModuleFields($validated['data']);

        if ($success) {
            return response()->json(['message' => 'Fields imported successfully']);
        }

        return response()->json(['error' => 'Failed to import fields'], 500);
    }
}
