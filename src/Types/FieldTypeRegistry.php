<?php

namespace Qmrp\CustomField\Types;

use Qmrp\CustomField\Contracts\FieldTypeInterface;

class FieldTypeRegistry
{
    protected static $types = [];

    public static function register(string $type, FieldTypeInterface $fieldType)
    {
        self::$types[$type] = $fieldType;
    }

    public static function get(string $type): ?FieldTypeInterface
    {
        return self::$types[$type] ?? null;
    }

    public static function has(string $type): bool
    {
        return isset(self::$types[$type]);
    }

    public static function all(): array
    {
        return self::$types;
    }

    public static function registerDefaults()
    {
        self::register('basic', new BasicFieldType());
        self::register('computed', new ComputedFieldType());
        self::register('relation', new RelationFieldType());
        self::register('formatted', new FormattedFieldType());
        self::register('conditional', new ConditionalFieldType());
    }
}
