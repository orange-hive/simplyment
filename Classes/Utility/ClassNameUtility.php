<?php

namespace OrangeHive\Simplyment\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClassNameUtility
{

    public static function getExtensionKey(string $fqcn)
    {
        list($vendorName, $extension) = explode('\\', $fqcn);

        return GeneralUtility::camelCaseToLowerCaseUnderscored($extension);
    }

    public static function getFqcnFromPath(string $vendorName, string $extensionKey, string $path)
    {
        $pathInExtension = substr($path, stripos($path, $extensionKey) + strlen($extensionKey) + strlen('/Classes') + 1);

        $pathSegments = explode('/', $pathInExtension);
        $pathSegments[count($pathSegments) - 1] = basename($pathSegments[count($pathSegments) - 1], '.php');

        $pathSegments = array_merge(
            [
                $vendorName,
                ucfirst(GeneralUtility::underscoredToUpperCamelCase($extensionKey)),
            ],
            $pathSegments
        );

        return implode('\\', $pathSegments);
    }
    
    public static function getTableNameByFqcn(string $fqcn)
    {
        $fqcn = ltrim($fqcn, '\\');

        $fqcnParts = explode('\\', $fqcn);

        // remove vendor
        $fqcnParts = array_slice($fqcnParts, 1);

        return 'tx_' . str_replace('\\', '_', mb_strtolower(implode('_', $fqcnParts)));
    }

}