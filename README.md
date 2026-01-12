# Laravel 自定义字段模块

为模型添加自定义显示字段的 Laravel 扩展包。

## 功能优势

1. 支持多种字段类型（计算字段、关系字段、格式化字段等）
2. 可重用字段配置
3. 与现有查询无缝集成
4. 支持 Blade 和 API
5. 高度可定制化用户可自定义字段显示
6. 支持字段权限管理

## 安装

```bash
composer require qmrp/customfield
```

### 发布配置和迁移文件

```bash
php artisan vendor:publish --provider="Qmrp\CustomField\CustomFieldServiceProvider"
```

### 运行迁移

```bash
php artisan migrate
```

## 快速开始

### 1. 在模型中使用 Trait

```php
use Qmrp\CustomField\Traits\HasCustomFields;

class Product extends Model
{
    use HasCustomFields;
}
```

### 2. 创建自定义字段

```php
use Qmrp\CustomField\Facades\CustomField;

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
    ],
    [
        'key' => 'formatted_price',
        'name' => '格式化价格',
        'type' => 'formatted',
        'config' => [
            'source_field' => 'price',
            'format' => 'currency',
            'decimals' => 2
        ],
        'sort_order' => 3,
        'is_active' => true
    ]
]);
```

### 3. 查询时使用自定义字段

```php
$products = Product::withCustomFields()->get();

foreach ($products as $product) {
    echo $product->getCustomField('total_price');
    echo $product->getCustomField('category_name');
    echo $product->getCustomField('formatted_price');
}
```

## 字段类型

### 1. 基础字段 (Basic)

直接显示模型中的字段值。

```php
[
    'key' => 'status_text',
    'name' => '状态文本',
    'type' => 'basic',
    'config' => [
        'source_field' => 'status',
        'default' => '未知'
    ]
]
```

### 2. 计算字段 (Computed)

通过回调函数计算字段值。

```php
[
    'key' => 'total_amount',
    'name' => '总金额',
    'type' => 'computed',
    'config' => [
        'callback' => function ($model) {
            return $model->subtotal + $model->tax - $model->discount;
        },
        'default' => 0
    ]
]
```

### 3. 关系字段 (Relation)

显示关联模型的字段值。

```php
[
    'key' => 'user_name',
    'name' => '用户名',
    'type' => 'relation',
    'config' => [
        'relation' => 'user',
        'display_field' => 'name',
        'multiple' => false,
        'separator' => ', '
    ]
]
```

### 4. 格式化字段 (Formatted)

对字段值进行格式化。

```php
[
    'key' => 'created_date',
    'name' => '创建日期',
    'type' => 'formatted',
    'config' => [
        'source_field' => 'created_at',
        'format' => 'date',
        'date_format' => 'Y-m-d'
    ]
]
```

支持的格式类型：
- `date`: 日期格式化
- `datetime`: 日期时间格式化
- `currency`: 货币格式化
- `number`: 数字格式化
- `percentage`: 百分比格式化
- `phone`: 电话号码格式化
- `uppercase`: 转大写
- `lowercase`: 转小写
- `ucfirst`: 首字母大写
- `truncate`: 文本截断

### 5. 条件字段 (Conditional)

根据条件返回不同的值。

```php
[
    'key' => 'order_status',
    'name' => '订单状态',
    'type' => 'conditional',
    'config' => [
        'default' => '未知',
        'conditions' => [
            [
                'field' => 'status',
                'operator' => '==',
                'expected' => 'pending',
                'value' => function ($model) {
                    return '待处理';
                }
            ],
            [
                'field' => 'status',
                'operator' => '==',
                'expected' => 'completed',
                'value' => function ($model) {
                    return '已完成';
                }
            ]
        ]
    ]
]
```

支持的条件操作符：
- `==`, `===`: 等于
- `!=`, `!==`: 不等于
- `>`, `<`, `>=`, `<=`: 比较运算符
- `in`, `not_in`: 包含/不包含
- `contains`: 包含字符串
- `starts_with`, `ends_with`: 开头/结尾
- `null`, `not_null`: 为空/不为空
- `empty`, `not_empty`: 为空/不为空
- `callback`: 自定义回调

## Blade 模板使用

### 表格组件

```blade
<x-customfield::table 
    :module="'product'" 
    :data="$products" 
    :user-id="auth()->id()" 
/>
```

### 字段值组件

```blade
<x-customfield::value 
    :model="$product" 
    field-key="total_price" 
    module="product" 
/>
```

### 表单组件

```blade
<x-customfield::form 
    module="product" 
    :model="$product" 
    :user-id="auth()->id()" 
/>
```

## API 使用

### 获取字段列表

```bash
GET /api/customfield/fields?module=product&user_id=1
```

### 创建字段

```bash
POST /api/customfield/fields
Content-Type: application/json

{
    "module": "product",
    "fields": [
        {
            "key": "total_price",
            "name": "总价",
            "type": "computed",
            "config": {
                "callback": "..."
            }
        }
    ]
}
```

### 更新字段

```bash
PUT /api/customfield/fields/{module}/{key}
Content-Type: application/json

{
    "name": "总价（更新）",
    "is_active": true
}
```

### 删除字段

```bash
DELETE /api/customfield/fields/{module}/{key}
```

### 用户设置

```bash
# 获取用户设置
GET /api/customfield/user-settings?module=product&user_id=1

# 保存用户设置
POST /api/customfield/user-settings
Content-Type: application/json

{
    "module": "product",
    "user_id": 1,
    "settings": [
        {
            "custom_module_field_id": 1,
            "sort_order": 1,
            "is_show": true,
            "is_fixed": false
        }
    ]
}
```

### 模板管理

```bash
# 获取模板列表
GET /api/customfield/templates?module=product&user_id=1

# 创建模板
POST /api/customfield/templates
Content-Type: application/json

{
    "name": "产品默认模板",
    "module": "product",
    "fields": [...],
    "is_public": true,
    "created_by": 1
}

# 应用模板
POST /api/customfield/templates/apply
Content-Type: application/json

{
    "module": "product",
    "template_id": 1
}

# 复制模板
POST /api/customfield/templates/duplicate
Content-Type: application/json

{
    "template_id": 1,
    "new_name": "产品模板副本",
    "created_by": 1
}

# 删除模板
DELETE /api/customfield/templates/{templateId}
```

### 导入导出

```bash
# 导出字段配置
POST /api/customfield/export
Content-Type: application/json

{
    "module": "product"
}

# 导入字段配置
POST /api/customfield/import
Content-Type: application/json

{
    "data": {
        "module": "product",
        "fields": [...]
    }
}
```

## 权限管理

### 设置字段权限

```php
use Qmrp\CustomField\Services\CustomFieldPermissionService;

$permissionService = app(CustomFieldPermissionService::class);

// 授予用户查看权限
$permissionService->grantPermission($fieldId, 'view', $userId);

// 授予角色编辑权限
$permissionService->grantPermission($fieldId, 'edit', null, $roleId);

// 撤销权限
$permissionService->revokePermission($fieldId, 'view', $userId);

// 检查权限
$hasPermission = $permissionService->checkPermission($fieldId, 'view', $userId);
```

### 获取可访问字段

```php
$accessibleFields = $permissionService->getAccessibleFields('product', $userId, 'view');
```

## 命令行工具

### 安装

```bash
php artisan customfield:install
```

### 导出字段配置

```bash
php artisan customfield:export product --output=product_fields.json
```

### 导入字段配置

```bash
php artisan customfield:import product_fields.json
```

## 高级用法

### 自定义字段类型

```php
use Qmrp\CustomField\Contracts\FieldTypeInterface;
use Qmrp\CustomField\Types\AbstractFieldType;

class CustomFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function __construct()
    {
        parent::__construct('custom', '自定义类型');
    }

    public function getValue($model, array $config)
    {
        // 自定义逻辑
        return $model->{$config['field']} ?? $config['default'];
    }

    public function validate(array $config): bool
    {
        return isset($config['field']);
    }
}

// 注册自定义类型
use Qmrp\CustomField\Types\FieldTypeRegistry;

FieldTypeRegistry::register('custom', new CustomFieldType());
```

### 使用模板

```php
use Qmrp\CustomField\Facades\CustomField;

// 创建模板
$template = CustomField::createTemplate(
    '产品标准模板',
    'product',
    [
        [
            'key' => 'total_price',
            'name' => '总价',
            'type' => 'computed',
            'config' => [...]
        ]
    ],
    true,
    1
);

// 应用模板
CustomField::applyTemplate('product', $template->id);

// 复制模板
$newTemplate = CustomField::duplicateTemplate($template->id, '产品模板副本', 2);
```

### 缓存配置

```php
use Qmrp\CustomField\Config\FieldConfigManager;

$configManager = app(FieldConfigManager::class);

// 设置缓存时间（秒）
$configManager->setCacheTtl(7200);
```

## 配置选项

在 `config/customfield.php` 中配置：

```php
return [
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],

    'permissions' => [
        'enabled' => true,
        'default_permissions' => [
            'view' => true,
            'edit' => true,
            'delete' => false,
        ],
    ],

    'middleware' => [
        'auth' => 'auth:sanctum',
        'permission' => \Qmrp\CustomField\Http\Middleware\CustomFieldPermission::class,
    ],

    'routes' => [
        'prefix' => 'api/customfield',
        'middleware' => 'api',
    ],
];
```

## 数据库表结构

### custom_module_fields
存储自定义字段定义

### custom_fields_user_settings
存储用户的字段显示设置

### custom_field_templates
存储字段模板

### custom_field_permissions
存储字段权限配置

## 常见问题

### Q: 如何在现有模型中添加自定义字段？

A: 只需在模型中使用 `HasCustomFields` Trait，然后创建字段配置即可。

### Q: 自定义字段会影响性能吗？

A: 不会，字段值在访问时才计算，并且支持缓存。建议对频繁访问的字段使用缓存。

### Q: 可以在 API 响应中包含自定义字段吗？

A: 可以，使用 `ModelWithCustomFieldsResource` 或在查询时使用 `withCustomFields()`。

### Q: 如何实现字段的动态权限控制？

A: 使用 `CustomFieldPermissionService` 设置字段级别的权限，并在中间件中验证。

## 许可证

MIT License
