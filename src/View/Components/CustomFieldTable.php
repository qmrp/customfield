<?php

namespace Qmrp\CustomField\View\Components;

use Illuminate\View\Component;
use Qmrp\CustomField\Config\FieldConfigManager;

class CustomFieldTable extends Component
{
    public $module;

    public $fields;

    public $data;

    public $userId;

    protected $configManager;

    public function __construct(string $module, $data, ?int $userId = null, FieldConfigManager $configManager = null)
    {
        $this->module = $module;
        $this->data = $data;
        $this->userId = $userId;
        $this->configManager = $configManager ?? app(FieldConfigManager::class);
        $this->fields = $this->getFields();
    }

    protected function getFields(): array
    {
        $service = app('customFieldService');
        return $service->getFieldsForTable($this->module, $this->userId);
    }

    public function render()
    {
        return view('customfield::components.table');
    }
}
