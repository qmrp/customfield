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
    protected $successResponse = [];

    protected $errorResponse = [];

    public function __construct(CustomFieldService $service)
    {
        $this->service = $service;
        $this->successResponse = config('customfield.response.success');
        $this->errorResponse = config('customfield.response.error');
    }

    public function index(Request $request): JsonResponse
    {
        $module = $request->input('module');
        $userId = $request->input('user_id');

        if (!$module) {
            return response()->json(['error' => 'Module is required'], 422);
        }

        $fields = $this->service->getFieldsForTable($module, $userId);

        $this->successResponse['data'] = $fields;

        return response()->json($this->successResponse);
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
            $this->successResponse['message'] = 'Custom fields saved successfully';
            $this->successResponse['data'] = $this->service->getModuleFields($module);

            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to save custom fields';
        return response()->json($this->errorResponse, 200);
    }

    public function show(Request $request, string $module): JsonResponse
    {
        $fields = $this->service->getModuleFields($module);

        $this->successResponse['data'] = $fields;
        return response()->json($this->successResponse, 200);
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
            $this->successResponse['message'] = 'Field updated successfully';
            $this->successResponse['data'] = $field->toArray();
            return response()->json($this->successResponse, 200);
        } catch (\Exception $e) {
            $this->errorResponse['message'] = $e->getMessage();
            return response()->json($this->errorResponse, 200);
        }
    }

    public function destroy(string $module, string $key): JsonResponse
    {
        $success = $this->service->deleteField($module, $key);

        if ($success) {
            $this->successResponse['message'] = 'Field deleted successfully';
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to delete field';
        return response()->json($this->errorResponse, 200);
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

        $this->successResponse['data'] = $settings;
        return response()->json($this->successResponse, 200);
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
            $this->successResponse['message'] = 'User settings saved successfully';
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to save user settings';
        return response()->json($this->errorResponse, 200);
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

        $this->successResponse['data'] = $templates;
        return response()->json($this->successResponse, 200);
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

        if ($template) {
            $this->successResponse['message'] = 'Template created successfully';
            $this->successResponse['data'] = new CustomFieldTemplateResource($template);
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to create template';
        return response()->json($this->errorResponse, 200);
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
            $this->successResponse['message'] = 'Template applied successfully';
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to apply template';
        return response()->json($this->errorResponse, 200);
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
            $this->successResponse['message'] = 'Template duplicated successfully';
            $this->successResponse['data'] = new CustomFieldTemplateResource($template);
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to duplicate template';
        return response()->json($this->errorResponse, 200);
    }

    public function deleteTemplate(int $templateId): JsonResponse
    {
        $success = $this->service->deleteTemplate($templateId);

        if ($success) {
            $this->successResponse['message'] = 'Template deleted successfully';
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to delete template';
        return response()->json($this->errorResponse, 200);
    }

    public function getAvailableFieldTypes(): JsonResponse
    {
        $types = $this->service->getAvailableFieldTypes();

        $this->successResponse['data'] = $types;
        return response()->json($this->successResponse, 200);
    }

    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50'
        ]);

        $data = $this->service->exportModuleFields($validated['module']);

        $this->successResponse['data'] = $data;
        return response()->json($this->successResponse, 200);
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
            $this->successResponse['message'] = 'Fields imported successfully';
            return response()->json($this->successResponse, 200);
        }

        $this->errorResponse['message'] = 'Failed to import fields';
        return response()->json($this->errorResponse, 200);
    }
}
