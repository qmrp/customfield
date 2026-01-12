<?php

namespace Qmrp\CustomField\Types;

use Qmrp\CustomField\Contracts\FieldTypeInterface;

abstract class AbstractFieldType implements FieldTypeInterface
{
    protected $type;

    protected $name;

    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function validate(array $config): bool
    {
        return true;
    }

    public function getDefaultValue(array $config)
    {
        return $config['default'] ?? null;
    }

    protected function executeCallback($callback, $model, array $config)
    {
        if (is_callable($callback)) {
            return $callback($model, $config);
        }else{
            $callback = '$callback = '. $callback .';';
            eval($callback);
            return $callback($model, $config);
        }
        return null;
    }
}
