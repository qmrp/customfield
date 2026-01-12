<?php

namespace Qmrp\CustomField\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Qmrp\CustomField\Services\CustomFieldService;

class CustomFieldPermission
{
    protected $service;

    public function __construct(CustomFieldService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $request, Closure $next, string $permission)
    {
        $module = $request->input('module');
        $key = $request->route('key');
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->hasPermission($module, $key, $permission, $user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    protected function hasPermission(string $module, ?string $key, string $permission, $user): bool
    {
        if ($user->is_admin ?? false) {
            return true;
        }

        $service = app('customFieldService');
        $field = $service->getField($module, $key);

        if (!$field) {
            return false;
        }

        $permissionModel = \Qmrp\CustomField\Models\CustomFieldPermission::where('custom_module_field_id', $field['id'])
            ->where('permission_type', $permission)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('role_id', $user->roles->pluck('id')->toArray());
            })
            ->first();

        return $permissionModel && $permissionModel->is_allowed;
    }
}
