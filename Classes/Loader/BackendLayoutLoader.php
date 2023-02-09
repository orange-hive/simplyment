<?php

namespace OrangeHive\Simplyment\Loader;

use OrangeHive\Simplyment\Cache\Typo3Cache;
use OrangeHive\Simplyment\Registry\BackendLayoutRegistry;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutLoader implements LoaderInterface
{

    public static function load(string $vendorName, string $extensionKey): void
    {
        if (Typo3Cache::exists(Typo3Cache::createIdentifier(BackendLayoutRegistry::class))) {
            return;
        }

        $extPath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Resources/Private/BackendLayouts');

        $files = glob($extPath . '/*.{ts,txt,typoscript,tsconfig}', GLOB_BRACE);
        foreach ($files as $file) {
            $identifier = pathinfo($file)['filename'];
            $title = $identifier;
            $configurationString = file_get_contents($file);
            $icon = null;

            /** @var TypoScriptParser $parser */
            $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
            $parser->parse($configurationString);
            $configuration = $parser->setup;

            if (array_key_exists('backend_layout.', $configuration)) {
                // directly starts with backend_layout
                $configuration = [
                    'title' => $title,
                    'config.' => $configuration,
                ];
            } else {
                if (ArrayUtility::isValidPath($configuration, 'title')) {
                    $title = ArrayUtility::getValueByPath($configuration, 'title');
                }

                if (ArrayUtility::isValidPath($configuration, 'description')) {
                    $description = ArrayUtility::getValueByPath($configuration, 'description');
                }

                if (ArrayUtility::isValidPath($configuration, 'icon')) {
                    $icon = ArrayUtility::getValueByPath($configuration, 'icon');
                }
            }

            BackendLayoutRegistry::addBackendLayout(
                identifier: $identifier,
                title: $title,
                configuration: $configuration,
                icon: $icon
            );
        }
    }

    public static function extLocalconf(string $vendorName, string $extensionName)
    {
        self::load($vendorName, $extensionName);

        // TODO: Implement extLocalconf() method.
    }

    public static function extTables(string $vendorName, string $extensionName)
    {
        // TODO: Implement extTables() method.
    }
}