<?php

namespace Qmrp\CustomField\Contracts;

interface FieldTypeInterface
{
    public function getType(): string;

    public function getName(): string;

    public function getValue($model, array $config);

    public function validate(array $config): bool;

    public function getDefaultValue(array $config);
}
