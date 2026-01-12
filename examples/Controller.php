<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Qmrp\CustomField\Facades\CustomField;
use Qmrp\CustomField\Http\Resources\ModelWithCustomFieldsResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $products = Product::withCustomFields('product', $userId, true)->get();

        return ModelWithCustomFieldsResource::collection($products);
    }

    public function show(Request $request, $id)
    {
        $userId = $request->user()->id;
        $product = Product::withCustomFields('product', $userId, true)->findOrFail($id);

        return new ModelWithCustomFieldsResource($product);
    }

    public function setupFields()
    {
        CustomField::saveModuleFields('product', [
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
            ]
        ]);

        return response()->json(['message' => 'Fields setup successfully']);
    }

    public function getUserSettings(Request $request)
    {
        $userId = $request->user()->id;
        $settings = CustomField::getUserSettings('product', $userId);

        return response()->json(['data' => $settings]);
    }

    public function saveUserSettings(Request $request)
    {
        $userId = $request->user()->id;
        $settings = $request->input('settings', []);

        CustomField::saveUserSettings('product', $userId, $settings);

        return response()->json(['message' => 'Settings saved successfully']);
    }
}
