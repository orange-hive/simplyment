<?php

namespace OrangeHive\Simplyment\Loader;

use OrangeHive\Simplyment\Attributes\Plugin;
use OrangeHive\Simplyment\Attributes\PluginAction;
use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\PluginRegistry;
use OrangeHive\Simplyment\Utility\LocalizationUtility;
use ReflectionClass;
use ReflectionMethod;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class PluginLoader implements LoaderInterface
{

    /*
     * @TODO: implement caching
     */
    public static function load(string $vendorName, string $extensionKey): void
    {
        $pluginRegistryCacheIdentifier = 'PluginRegistry_' . $vendorName . '_' . $extensionKey;

        if (CustomCache::has($pluginRegistryCacheIdentifier)) {
            PluginRegistry::set(CustomCache::get($pluginRegistryCacheIdentifier));
            return;
        }


        $extPath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Classes/Controller');

        $files = glob($extPath . '/*Controller.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');

            $fqcn = $vendorName . '\\' . ucfirst(GeneralUtility::underscoredToUpperCamelCase($extensionKey)) . '\\Controller\\' . $className;

            $classRef = new ReflectionClass($fqcn);
            foreach ($classRef->getAttributes(Plugin::class) as $attribute) {
                /** @var Plugin $instance */
                $instance = $attribute->newInstance();
                PluginRegistry::addExtensionKey($instance->name, $extensionKey);
                PluginRegistry::addVendorName($instance->name, $vendorName);
            }

            foreach ($classRef->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

                $methodRef = new ReflectionMethod($method->class, $method->name);
                foreach ($methodRef->getAttributes(PluginAction::class) as $attribute) {
                    /** @var PluginAction $attributeInstance */
                    $attributeInstance = $attribute->newInstance();

                    PluginRegistry::addAction(
                        pluginName: $attributeInstance->pluginName,
                        controllerFqcn: $fqcn,
                        actionName: basename($method->name, 'Action'),
                        noCache: $attributeInstance->noCache
                    );
                }
            }
        }

        CustomCache::set($pluginRegistryCacheIdentifier, PluginRegistry::list());
    }

    public static function register(): void
    {
        foreach (PluginRegistry::list() as $pluginName => $data) {
            $defaultIcon = ExtensionManagementUtility::getExtensionIcon(
                extensionPath: ExtensionManagementUtility::extPath('simplyment'),
                returnFullPath: true
            );

            $extensionKey = $data['extensionKey'];

            $pluginTitle = $pluginName;
            if (LocalizationUtility::keyExistsInLocallang($extensionKey, 'plugin.' . $pluginName)) {
                $pluginTitle = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:plugin.' . $pluginName;
            }

            ExtensionUtility::registerPlugin(
                extensionName: $data['extensionKey'],
                pluginName: $pluginName,
                pluginTitle: $pluginTitle,
                pluginIcon: $data['iconPath'] ?? $defaultIcon
            );
        }
    }

    public static function extLocalconf(string $vendorName, string $extensionName): void
    {
        self::load($vendorName, $extensionName);
        self::configure();
    }

    public static function extTables(string $vendorName, string $extensionName): void
    {
        self::registerWizardItems();
    }

    public static function tcaTtContentOverrides(string $vendorName, string $extensionName): void
    {
        $pluginRegistryCacheIdentifier = 'PluginRegistry_' . $vendorName . '_' . $extensionName;

        if (CustomCache::has($pluginRegistryCacheIdentifier)) {
            PluginRegistry::set(CustomCache::get($pluginRegistryCacheIdentifier));
        }

        foreach (PluginRegistry::list() as $pluginName => $pluginData) {
            $extensionKey = $pluginData['extensionKey'];

            $flexFormPath = null;
            if (array_key_exists('flexForm', $pluginData) && !empty($pluginData['flexForm'])) {
                $flexFormPath = $pluginData['flexForm'];
            } else {
                // try to load from default directory
                $filePathInExtension = 'Configuration/FlexForms/' . $pluginName . '.xml';

                $defaultPath = ExtensionManagementUtility::extPath($extensionKey)
                    . $filePathInExtension;

                if (file_exists($defaultPath)) {
                    $flexFormPath = 'EXT:' . $extensionKey . '/' . $filePathInExtension;
                }
            }

            if (is_null($flexFormPath)) {
                continue;
            }

            $pluginSignature = str_replace('_', '', $extensionKey) . '_' . mb_strtolower($pluginName);
            if (strpos($flexFormPath, 'EXT:') === 0) {
                $flexFormPath = 'FILE:' . $flexFormPath;
            }

            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                $pluginSignature,
                $flexFormPath
            );
        }
    }

    public static function configure(): void
    {
        foreach (PluginRegistry::list() as $pluginName => $pluginData) {
            $actions = [];
            $noCacheActions = [];

            foreach ($pluginData['controllers'] as $fqcn => $data) {
                if (!empty($data['actions'])) {
                    $actions[$fqcn] = join(',', $data['actions']);
                }
                if (!empty($data['noCache'])) {
                    $noCacheActions[$fqcn] = join(',', $data['noCache']);
                }
            }

            ExtensionUtility::configurePlugin(
                extensionName: $pluginData['extensionKey'],
                pluginName: $pluginName,
                controllerActions: $actions,
                nonCacheableControllerActions: $noCacheActions
            );
        }
    }

    public static function registerWizardItems(): void
    {
        $pluginSignatures = [];
        $pluginElements = [];

        /** @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        $defaultIcon = ExtensionManagementUtility::getExtensionIcon(
            extensionPath: ExtensionManagementUtility::extPath('simplyment'),
            returnFullPath: true
        );

        foreach (PluginRegistry::list() as $pluginName => $pluginData) {
            if ($pluginData['hideContentElement']) {
                continue;
            }

            $pluginSignature = str_replace('_', '', $pluginData['extensionKey']) . '_' . mb_strtolower($pluginName);
            $pluginSignatures[] = $pluginSignature;

            // register icon
            $iconIdentifier = 'plugin-icon-' . $pluginSignature;
            $iconPath = $pluginData['iconPath'] ?? $defaultIcon;

            $iconProvider = BitmapIconProvider::class;
            if (mb_substr(mb_strtolower($iconPath), -3) === 'svg') {
                $iconProvider = SvgIconProvider::class;
            }

            $iconRegistry->registerIcon(
                $iconIdentifier,
                $iconProvider,
                [
                    'source' => $iconPath,
                ]
            );

            $extensionKey = $pluginData['extensionKey'];

            $pluginTitle = $pluginName;
            if (LocalizationUtility::keyExistsInLocallang($extensionKey, 'plugin.' . $pluginName)) {
                $pluginTitle = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:plugin.' . $pluginName;
            }

            $pluginDescription = ($pluginData['description'] ?? '');
            if (
                !empty($pluginDescription)
                && LocalizationUtility::keyExistsInLocallang($extensionKey, 'plugin.' . $pluginName . '.description')
            ) {
                $pluginDescription = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:plugin.' . $pluginName . '.description';
            }


            $pluginElements[] = $pluginSignature . ' {
                iconIdentifier = ' . $iconIdentifier . '
                title = ' . $pluginTitle . '
                description = ' . $pluginDescription . '
                tt_content_defValues {
                    CType = list
                    list_type = ' . $pluginSignature . '
                }
            }';
        }

        ExtensionManagementUtility::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.plugins {
  elements {
    ' . implode(LF, $pluginElements). '
  }
  show:= addToList(' . implode(',', $pluginSignatures) . ')
}');
    }

}