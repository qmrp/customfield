<?php

namespace Qmrp\CustomField\Types;

class BasicFieldType extends AbstractFieldType
{
    public function __construct()
    {
        parent::__construct('basic', '基础字段');
    }

    public function getValue($model, array $config)
    {
        $sourceField = $config['source_field'] ?? null;

        if (!$sourceField) {
            return $this->getDefaultValue($config);
        }

        $value = $model->$sourceField ?? null;

        if ($value === null) {
            return $this->getDefaultValue($config);
        }

        return $value;
    }

    public function validate(array $config): bool
    {
        return isset($config['source_field']);
    }
}
