<?php

namespace OrangeHive\Simplyment\Registry;

class SimplymentExtensionRegistry
{

    use RegistryTrait;

    public const CACHE_IDENTIFIER = 'SimplymentExtensionRegistry';


    public static function add(string $vendor, string $extensionKey)
    {
        self::$data[] = [
            'vendor' => $vendor,
            'extensionKey' => $extensionKey,
        ];
    }


}