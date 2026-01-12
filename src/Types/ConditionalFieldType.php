<?php

namespace Qmrp\CustomField\Types;

class ConditionalFieldType extends AbstractFieldType
{
    public function __construct()
    {
        parent::__construct('conditional', '条件字段');
    }

    public function getValue($model, array $config)
    {
        $conditions = $config['conditions'] ?? [];
        $defaultValue = $this->getDefaultValue($config);

        foreach ($conditions as $condition) {
            if ($this->evaluateCondition($model, $condition)) {
                return $this->executeCallback($condition['value'], $model, $config);
            }
        }

        return $defaultValue;
    }

    protected function evaluateCondition($model, array $condition): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '==';
        $expected = $condition['expected'] ?? null;

        if (!$field) {
            return false;
        }
        $field = explode('.', $field);
        if(count($field) > 1){  // 处理关联字段
            $attr = $field[0];
            $key = $field[1];
            $actual = $model->$attr[$key] ?? null;
        }else{
            $field = $field[0];
            $actual = $model->$field ?? null;
        }
        switch ($operator) {
            case '==':
                return $actual == $expected;
            case '===':
                return $actual === $expected;
            case '!=':
                return $actual != $expected;
            case '!==':
                return $actual !== $expected;
            case '>':
                return $actual > $expected;
            case '<':
                return $actual < $expected;
            case '>=':
                return $actual >= $expected;
            case '<=':
                return $actual <= $expected;
            case 'in':
                return in_array($actual, (array)$expected);
            case 'not_in':
                return !in_array($actual, (array)$expected);
            case 'contains':
                return strpos($actual, $expected) !== false;
            case 'starts_with':
                return strpos($actual, $expected) === 0;
            case 'ends_with':
                return substr($actual, -strlen($expected)) === $expected;
            case 'null':
                return $actual === null;
            case 'not_null':
                return $actual !== null;
            case 'empty':
                return empty($actual);
            case 'not_empty':
                return !empty($actual);
            case 'callback':
                $callback = $condition['callback'] ?? null;
                return $callback ? $this->executeCallback($callback, $model, $condition) : false;
            default:
                return false;
        }
    }

    public function validate(array $config): bool
    {
        return isset($config['conditions']) && is_array($config['conditions']) && !empty($config['conditions']);
    }
}
