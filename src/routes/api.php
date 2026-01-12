<?php

use Illuminate\Support\Facades\Route;
use Qmrp\CustomField\Http\Controllers\CustomFieldController;

Route::prefix('api/customfield')->group(function () {
    Route::get('/fields', [CustomFieldController::class, 'index']);
    Route::post('/fields', [CustomFieldController::class, 'store']);
    Route::get('/fields/{module}/{key}', [CustomFieldController::class, 'show']);
    Route::put('/fields/{module}/{key}', [CustomFieldController::class, 'update']);
    Route::delete('/fields/{module}/{key}', [CustomFieldController::class, 'destroy']);

    Route::get('/user-settings', [CustomFieldController::class, 'getUserSettings']);
    Route::post('/user-settings', [CustomFieldController::class, 'saveUserSettings']);

    Route::get('/templates', [CustomFieldController::class, 'getTemplates']);
    Route::post('/templates', [CustomFieldController::class, 'createTemplate']);
    Route::post('/templates/apply', [CustomFieldController::class, 'applyTemplate']);
    Route::post('/templates/duplicate', [CustomFieldController::class, 'duplicateTemplate']);
    Route::delete('/templates/{templateId}', [CustomFieldController::class, 'deleteTemplate']);

    Route::get('/types', [CustomFieldController::class, 'getAvailableFieldTypes']);

    Route::post('/export', [CustomFieldController::class, 'export']);
    Route::post('/import', [CustomFieldController::class, 'import']);
});
