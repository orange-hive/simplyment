<?php

namespace OrangeHive\Simplyment\Registry;

use JetBrains\PhpStorm\ArrayShape;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PluginRegistry
{
    use RegistryTrait;

    public static function addPluginInformation(
        string  $pluginName,
        string  $description,
        ?string $iconPath = null,
        ?string $flexFormPath = null,
        bool    $hideContentElement = false
    ): void
    {
        if (!array_key_exists($pluginName, self::$data)) {
            self::$data[$pluginName] = [
                'controllers' => [],
            ];
        }

        self::$data[$pluginName]['description'] = $description;
        self::$data[$pluginName]['iconPath'] = $iconPath;
        if (!is_null($flexFormPath)) {
            self::$data[$pluginName]['flexForm'] = $flexFormPath;
        }
        self::$data[$pluginName]['hideContentElement'] = $hideContentElement;
    }

    public static function addExtensionKey(string $pluginName, string $extensionKey): void
    {
        self::$data[$pluginName]['extensionKey'] = $extensionKey;
    }

    public static function addVendorName(string $pluginName, string $vendorName): void
    {
        self::$data[$pluginName]['vendorName'] = $vendorName;
    }

    public static function addAction(string $pluginName, string $controllerFqcn, string $actionName, bool $noCache = false): void
    {
        if (!array_key_exists($pluginName, self::$data)) {
            self::$data[$pluginName] = [
                'controllers' => [],
                'description' => $pluginName,
            ];
        }

        if (!array_key_exists($controllerFqcn, self::$data[$pluginName]['controllers'])) {
            self::$data[$pluginName]['controllers'][$controllerFqcn] = [
                'actions' => [],
                'noCache' => [],
            ];
        }

        self::$data[$pluginName]['controllers'][$controllerFqcn]['actions'][] = $actionName;
        if ($noCache) {
            self::$data[$pluginName]['controllers'][$controllerFqcn]['noCache'][] = $actionName;
        }
    }

    #[ArrayShape([
        'string' => [
            'description' => 'string',
            'iconPath' => 'string|null',
            'controllers' => [],
            'extensionKey' => 'string',
            'vendorName' => 'string',
            'flexForm' => 'string|null',
            'hideContentElement' => 'bool'
        ],
    ])]
    public static function list(): array
    {
        return self::$data;
    }

}