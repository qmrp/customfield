<?php

namespace Qmrp\CustomField\Types;

use Illuminate\Support\Facades\DB;

class RelationFieldType extends AbstractFieldType
{
    public function __construct()
    {
        parent::__construct('relation', '关系字段');
    }

    public function getValue($model, array $config)
    {
        $relation = $config['relation'] ?? null;
        $displayField = $config['display_field'] ?? 'name';
        $multiple = $config['multiple'] ?? false;
        $separator = $config['separator'] ?? ', ';

        if (!$relation) {
            return $this->getDefaultValue($config);
        }

        try {
            if (method_exists($model, $relation)) {
                $related = $model->$relation;
                
                if ($multiple) {
                    if ($related instanceof \Illuminate\Database\Eloquent\Collection) {
                        return $related->pluck($displayField)->implode($separator);
                    }
                    return $related;
                } else {
                    if ($related) {
                        return $related->$displayField ?? $this->getDefaultValue($config);
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->getDefaultValue($config);
        }

        return $this->getDefaultValue($config);
    }

    public function validate(array $config): bool
    {
        return isset($config['relation']) && is_string($config['relation']);
    }
}
