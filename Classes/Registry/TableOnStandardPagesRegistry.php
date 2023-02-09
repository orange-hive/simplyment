<?php

namespace OrangeHive\Simplyment\Registry;

class TableOnStandardPagesRegistry
{
    use RegistryTrait;


    public static function addTable(string $table): void
    {
        self::$data[] = $table;
    }

}