<?php

namespace OrangeHive\Simplyment\Registry;

use JetBrains\PhpStorm\ArrayShape;

class DatabaseModelRegistry
{
    use RegistryTrait;

    public static function addTable(
        string $tableName,
        string $fqcn,
        array  $indices = []
    ): void
    {
        self::createTableEntryIfNotExistent($tableName);

        self::$data[$tableName]['fqcn'] = $fqcn;
        if (!array_key_exists('fields', self::$data[$tableName])) {
            self::$data[$tableName]['fields'] = [];
        }
        if (!array_key_exists('indices', self::$data[$tableName])) {
            self::$data[$tableName]['indices'] = $indices;
        }
    }

    public static function addOverrideInformation(string $tableName, string $overrideTableName): void
    {
        self::createTableEntryIfNotExistent($tableName);

        self::$data[$tableName]['overrideTableName'] = $overrideTableName;
    }

    public static function addField(string $tableName, string $field, array $fieldConfig): void
    {
        self::createTableEntryIfNotExistent($tableName);

        self::$data[$tableName]['fields'][$field] = $fieldConfig;
    }

    #[ArrayShape([
        'string' => [
            'fqcn' => 'string',
            'fields' => [
                'string' => [],
            ],
            'indices' => [
                'string' => 'string',
            ],
        ],
    ])]
    public static function list(): array
    {
        return self::$data;
    }

    #[ArrayShape([
        'fqcn' => 'string',
        'fields' => [
            'string' => [],
        ],
        'indices' => [
            'string' => 'string',
        ],
    ])]
    public static function getByTableName(string $tableName): ?array
    {
        if (!array_key_exists($tableName, self::$data)) {
            return null;
        }

        return self::$data[$tableName];
    }

    protected static function createTableEntryIfNotExistent(string $tableName): void
    {
        if (!array_key_exists($tableName, self::$data)) {
            self::$data[$tableName] = [];
        }
    }

}