<?php

namespace OrangeHive\Simplyment\Registry;

trait RegistryTrait
{

    protected static array $data = [];


    public static function set(array $data)
    {
        self::$data = $data;
    }

    public static function list(): array
    {
        return self::$data;
    }

    public static function clear(): void
    {
        self::$data = [];
    }

}