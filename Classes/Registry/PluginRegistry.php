<?php

namespace OrangeHive\Simplyment\Registry;

use JetBrains\PhpStorm\ArrayShape;
use OrangeHive\Simplyment\Attributes\Plugin;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PluginRegistry
{
    use RegistryTrait;

    public static function addPluginInformation(
        string $extensionKey,
        string $vendorName,
        Plugin $plugin
    ): void
    {
        $key = self::getKey($extensionKey, $plugin->name);

        if (!array_key_exists($key, self::$data)) {
            $pluginData = [
                'pluginName' => $plugin->name,
                'extensionKey' => $extensionKey,
                'vendorName' => $vendorName,
                'controllers' => [],
                'description' => $plugin->description,
                'iconPath' => $plugin->iconPath,
                'hideContentElement' => $plugin->hideContentElement,
            ];

            if (!is_null($plugin->flexFormPath)) {
                $pluginData['flexForm'] = $plugin->flexFormPath;
            }

            self::$data[$key] = $pluginData;
        }
    }

    public static function addAction(
        string $extensionKey,
        string $pluginName,
        string $controllerFqcn,
        string $actionName,
        bool   $noCache = false
    ): void
    {
        $key = self::getKey($extensionKey, $pluginName);

        if (!array_key_exists($key, self::$data)) {
            throw new \Exception(<<<TEXT
No Plugin has been registered with the plugin name "{$pluginName}" in "{$extensionKey}".
TEXT);
        }

        if (!array_key_exists($controllerFqcn, self::$data[$key]['controllers'])) {
            self::$data[$key]['controllers'][$controllerFqcn] = [
                'actions' => [],
                'noCache' => [],
            ];
        }

        self::$data[$key]['controllers'][$controllerFqcn]['actions'][] = $actionName;
        if ($noCache) {
            self::$data[$key]['controllers'][$controllerFqcn]['noCache'][] = $actionName;
        }
    }

    #[ArrayShape([
        'string' => [
            'pluginName' => 'string',
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

    protected static function getKey(string $extensionKey, string $pluginName): string
    {
        return str_replace('_', '', $extensionKey) . '_' . mb_strtolower($pluginName);
    }
}