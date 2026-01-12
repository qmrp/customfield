<?php

namespace Qmrp\CustomField\Types;

class ComputedFieldType extends AbstractFieldType
{
    public function __construct()
    {
        parent::__construct('computed', '计算字段');
    }

    public function getValue($model, array $config)
    {
        $callback = $config['callback'] ?? null;
        
        if (!$callback) {
            return $this->getDefaultValue($config);
        }

        return $this->executeCallback($callback, $model, $config);
    }

    public function validate(array $config): bool
    {
        return isset($config['callback']) && is_callable($config['callback']);
    }
}
