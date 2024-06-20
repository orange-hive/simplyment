<?php

namespace OrangeHive\Simplyment\Registry;

trait RegistryTrait
{

    protected static array $data = [];


    public static function set(array $data, bool $add = false)
    {
        if (!$add || empty(self::$data)) {
            self::$data = $data;
            return;
        }

        $tmp = self::$data;
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($tmp, $data);
        self::$data = $tmp;
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