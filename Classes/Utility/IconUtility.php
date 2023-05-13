<?php

namespace OrangeHive\Simplyment\Utility;

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IconUtility
{

    public static function getIconIdentifierBySignature(string $signature, ?string $iconPath = null, string $type = 'ce-icon-')
    {
        /** @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        $iconIdentifier = 'ce-icon-' . $signature;
        $iconPath = $iconPath ?? self::getDefaultIconPath();

        if (!$iconRegistry->isRegistered($iconIdentifier)) {
            // icon not yet registered
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
        }

        return $iconIdentifier;
    }

    public static function getDefaultIconPath()
    {
        $defaultIcon = ExtensionManagementUtility::getExtensionIcon(
            extensionPath: ExtensionManagementUtility::extPath('simplyment'),
            returnFullPath: true
        );

        return $defaultIcon;
    }
}