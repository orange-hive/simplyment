<?php

namespace OrangeHive\Simplyment\Registry;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentElementRegistry
{
    use RegistryTrait;

    public static function add(
        string  $name,
        string  $extensionKey,
        string  $fqcn,
        string  $tab = 'common',
        ?string $position = null,
        ?string $flexFormPath = null,
        ?string $icon = null,
        ?bool   $hideContentElement = false
    )
    {
        try {
            /** @var ExtensionConfiguration $extensionConfiguration */
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

            $signatureType = $extensionConfiguration->get('simplyment', 'contentElementSignatureType');

            $key = match ($signatureType) {
                'pluginname_contentelementname' => str_replace('_', '', $extensionKey) . '_' . mb_strtolower($name),
                'plugin_name_content_element_name' => $extensionKey . '_' . GeneralUtility::camelCaseToLowerCaseUnderscored($name),
            };

        } catch (\Exception $exception) {
            $key = str_replace('_', '', $extensionKey) . '_' . mb_strtolower($name);
        }

        self::$data[$key] = [
            'name' => $name,
            'extensionKey' => $extensionKey,
            'fqcn' => $fqcn,
            'tab' => $tab,
            'position' => $position,
            'flexForm' => $flexFormPath,
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