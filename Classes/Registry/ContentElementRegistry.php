<?php

namespace OrangeHive\Simplyment\Registry;

class ContentElementRegistry
{
    use RegistryTrait;

    public static function add(
        string  $name,
        string  $extensionKey,
        string  $fqcn,
        string  $tab = 'common',
        ?string $position = null,
        ?string $icon = null,
        ?bool   $hideContentElement = false
    )
    {
        $key = str_replace('_', '', $extensionKey) . '_' . mb_strtolower($name);

        self::$data[$key] = [
            'name' => $name,
            'extensionKey' => $extensionKey,
            'fqcn' => $fqcn,
            'tab' => $tab,
            'position' => $position,
            'icon' => $icon,
            'hideContentElement' => $hideContentElement,
        ];
    }

    public static function getBySignature(string $signature): ?array
    {
        if (array_key_exists($signature, self::$data)) {
            return self::$data[$signature];
        }

        return null;
    }

    public static function listByExtensionKey(string $extensionKey): array
    {
        $filtered = [];
        foreach (self::$data as $ceSignature => $ceData) {
            if ($ceData['extensionKey'] === $extensionKey) {
                $filtered[$ceSignature] = $ceData;
            }
        }

        return $filtered;
    }

}