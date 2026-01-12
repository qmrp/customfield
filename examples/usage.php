<?php

use App\Models\Product;
use Qmrp\CustomField\Facades\CustomField;

$service = app('customFieldService');

$service->saveModuleFields('product', [
    [
        'key' => 'total_price',
        'name' => '总价',
        'type' => 'computed',
        'config' => [
            'callback' => function ($model) {
                return $model->price * $model->quantity;
            },
            'default' => 0
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
            'display_field' => 'name',
            'multiple' => false
        ],
        'sort_order' => 2,
        'is_active' => true
    ],
    [
        'key' => 'formatted_price',
        'name' => '格式化价格',
        'type' => 'formatted',
        'config' => [
            'source_field' => 'price',
            'format' => 'currency',
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ','
        ],
        'sort_order' => 3,
        'is_active' => true
    ],
    [
        'key' => 'status_text',
        'name' => '状态文本',
        'type' => 'conditional',
        'config' => [
            'default' => '未知',
            'conditions' => [
                [
                    'field' => 'status',
                    'operator' => '==',
                    'expected' => 'active',
                    'value' => function ($model) {
                        return '激活';
                    }
                ],
                [
                    'field' => 'status',
                    'operator' => '==',
                    'expected' => 'inactive',
                    'value' => function ($model) {
                        return '未激活';
                    }
                },
                [
                    'field' => 'status',
                    'operator' => '==',
                    'expected' => 'draft',
                    'value' => function ($model) {
                        return '草稿';
                    }
                }
            ]
        ],
        'sort_order' => 4,
        'is_active' => true
    ],
    [
        'key' => 'price_with_tax',
        'name' => '含税价格',
        'type' => 'computed',
        'config' => [
            'callback' => function ($model) {
                return $model->price * 1.1;
            },
            'default' => 0
        ],
        'sort_order' => 5,
        'is_active' => true
    ]
]);

$products = Product::withCustomFields()->get();

foreach ($products as $product) {
    echo "产品名称: {$product->name}\n";
    echo "总价: {$product->getCustomField('total_price')}\n";
    echo "分类: {$product->getCustomField('category_name')}\n";
    echo "价格: {$product->getCustomField('formatted_price')}\n";
    echo "状态: {$product->getCustomField('status_text')}\n";
    echo "含税价格: {$product->getCustomField('price_with_tax')}\n";
    echo "-------------------\n";
}

$productsWithCustomFields = $products->map(function ($product) {
    return $product->toArrayWithCustomFields();
});

return $productsWithCustomFields->toArray();
