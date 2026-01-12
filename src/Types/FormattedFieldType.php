<?php

namespace Qmrp\CustomField\Types;

class FormattedFieldType extends AbstractFieldType
{
    public function __construct()
    {
        parent::__construct('formatted', '格式化字段');
    }

    public function getValue($model, array $config)
    {
        $sourceField = $config['source_field'] ?? null;
        $format = $config['format'] ?? null;
        $callback = $config['callback'] ?? null;

        if ($callback) {
            return $this->executeCallback($callback, $model, $config);
        }

        if (!$sourceField || !$format) {
            return $this->getDefaultValue($config);
        }

        $value = $model->$sourceField ?? null;

        if ($value === null) {
            return $this->getDefaultValue($config);
        }

        return $this->formatValue($value, $format, $config);
    }

    protected function formatValue($value, string $format, array $config)
    {
        switch ($format) {
            case 'date':
                return date($config['date_format'] ?? 'Y-m-d', strtotime($value));
            case 'datetime':
                return date($config['datetime_format'] ?? 'Y-m-d H:i:s', strtotime($value));
            case 'currency':
                return number_format($value, $config['decimals'] ?? 2, $config['decimal_separator'] ?? '.', $config['thousands_separator'] ?? ',');
            case 'number':
                return number_format($value, $config['decimals'] ?? 0, $config['decimal_separator'] ?? '.', $config['thousands_separator'] ?? ',');
            case 'percentage':
                return number_format($value * 100, $config['decimals'] ?? 2) . '%';
            case 'phone':
                return $this->formatPhone($value, $config['phone_format'] ?? null);
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'ucfirst':
                return ucfirst($value);
            case 'truncate':
                $length = $config['truncate_length'] ?? 50;
                $suffix = $config['truncate_suffix'] ?? '...';
                return strlen($value) > $length ? substr($value, 0, $length) . $suffix : $value;
            default:
                return $value;
        }
    }

    protected function formatPhone($phone, $format = null)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if ($format === 'us') {
            return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3', $phone);
        }
        
        return $phone;
    }

    public function validate(array $config): bool
    {
        return isset($config['source_field']) && (isset($config['format']) || isset($config['callback']));
    }
}
