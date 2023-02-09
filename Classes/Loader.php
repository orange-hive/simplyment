<?php

namespace OrangeHive\Simplyment;

use OrangeHive\Simplyment\Loader\BackendLayoutLoader;
use OrangeHive\Simplyment\Loader\ContentElementLoader;
use OrangeHive\Simplyment\Loader\DatabaseModelLoader;
use OrangeHive\Simplyment\Loader\HookLoader;
use OrangeHive\Simplyment\Loader\PluginLoader;
use OrangeHive\Simplyment\Renderer\BlueprintRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class Loader
{

    protected static array $defaultLoaders = [
        DatabaseModelLoader::class,
        PluginLoader::class,
        ContentElementLoader::class,
        BackendLayoutLoader::class,
        HookLoader::class,
    ];

    public static function extLocalconf(string $vendorName, string $extensionName, ?array $loaders = null): void
    {
        self::createRelevantFilesIfNotExistent($vendorName, $extensionName);

        if (is_null($loaders)) {
            $loaders = self::$defaultLoaders;
        }

        foreach ($loaders as $loader) {
            if (method_exists($loader, 'extLocalconf')) {
                $loader::extLocalconf($vendorName, $extensionName);
            }
        }
    }


    public static function extTables(string $vendorName, string $extensionName, ?array $loaders = null): void
    {
        if (is_null($loaders)) {
            $loaders = self::$defaultLoaders;
        }

        foreach ($loaders as $loader) {
            if (method_exists($loader, 'extTables')) {
                $loader::extTables($vendorName, $extensionName);
            }
        }
    }

    public static function tcaTtContentOverrides(string $vendorName, string $extensionName, ?array $loaders = null): void
    {
        if (is_null($loaders)) {
            $loaders = self::$defaultLoaders;
        }

        foreach ($loaders as $loader) {
            if (method_exists($loader, 'tcaTtContentOverrides')) {
                $loader::tcaTtContentOverrides($vendorName, $extensionName);
            }
        }
    }

    public static function classes(string $vendorName, string $extensionName, ?array $loaders = null): array
    {
        if (is_null($loaders)) {
            $loaders = self::$defaultLoaders;
        }

        $result = [];

        foreach ($loaders as $loader) {
            if (method_exists($loader, 'classes')) {
                $result = array_merge($result, $loader::classes($vendorName, $extensionName));
            }
        }

        return $result;
    }

    protected static function createRelevantFilesIfNotExistent(string $vendorName, string $extensionName): void
    {
        $fileMappings = [
            [
                'fileName' => 'tt_content',
                'path' => 'Configuration/TCA/Overrides',
            ],
            [
                'fileName' => 'Classes',
                'path' => 'Configuration/Extbase/Persistence',
            ],
        ];

        foreach ($fileMappings as $mapping) {
            $absPath = ExtensionManagementUtility::extPath($extensionName, $mapping['path'] . '/' . $mapping['fileName'] . '.php');

            if (file_exists($absPath)) {
                continue;
            }

            $content = BlueprintRenderer::render($mapping['path'] . '/' . $mapping['fileName'], [
                'VENDOR_NAME' => $vendorName,
                'EXTENSION_KEY' => $extensionName
            ]);

            GeneralUtility::mkdir_deep(ExtensionManagementUtility::extPath($extensionName, $mapping['path']));
            GeneralUtility::writeFile($absPath, $content);
        }
    }

}