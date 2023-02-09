<?php

namespace OrangeHive\Simplyment\Registry;

use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;

class BackendLayoutRegistry
{
    use RegistryTrait;


    public static function addBackendLayout(string $identifier, string $title, string|array $configuration, ?string $icon = null): void
    {
        self::$data[$identifier] = [
            'identifier' => $identifier,
            'title' => $title,
            'configuration' => $configuration,
            'icon' => $icon,
        ];
    }

    public static function getByIdentfier(string $identifier): ?array
    {
        if (array_key_exists($identifier, self::$data)) {
            return self::$data[$identifier];
        }

        return null;
    }

}