<?php

namespace OrangeHive\Simplyment\Registry;

class HookRegistry
{
    use RegistryTrait;


    public static function addHook(string $hookIdentifier, string $fqcn, ?string $methodName = null, ?string $key = null): void
    {
        $hook = [
            'hookIdentifier' => $hookIdentifier,
            'fqcn' => $fqcn,
            'key' => $key,
        ];

        if (!is_null($methodName)) {
            $hook['methodName'] = $methodName;
        }

        self::$data[] = $hook;
    }

}