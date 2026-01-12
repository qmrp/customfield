<?php

namespace Qmrp\CustomField\View\Components;

use Illuminate\View\Component;

class CustomFieldForm extends Component
{
    public $module;

    public $fields;

    public $model;

    public $userId;

    public function __construct(string $module, $model = null, ?int $userId = null)
    {
        $this->module = $module;
        $this->model = $model;
        $this->userId = $userId;
        $this->fields = $this->getFields();
    }

    protected function getFields(): array
    {
        $service = app('customFieldService');
        return $service->getFieldsForTable($this->module, $this->userId);
    }

    public function render()
    {
        return view('customfield::components.form');
    }
}
