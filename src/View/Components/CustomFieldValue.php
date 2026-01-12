<?php

namespace Qmrp\CustomField\View\Components;

use Illuminate\View\Component;

class CustomFieldValue extends Component
{
    public $model;

    public $fieldKey;

    public $module;

    public function __construct($model, string $fieldKey, ?string $module = null)
    {
        $this->model = $model;
        $this->fieldKey = $fieldKey;
        $this->module = $module;
    }

    public function getValue()
    {
        if ($this->model && method_exists($this->model, 'getCustomField')) {
            return $this->model->getCustomField($this->fieldKey);
        }
        return null;
    }

    public function render()
    {
        return view('customfield::components.value');
    }
}
