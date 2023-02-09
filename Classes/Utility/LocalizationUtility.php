<?php

namespace OrangeHive\Simplyment\Utility;

class LocalizationUtility
{

    public static function addLocalizationLabel(string $fqcn, array &$modelColumns, string $idPrefix = ''): void
    {
        $extensionKey = ClassNameUtility::getExtensionKey($fqcn);
        $translationFile = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf';

        if (!empty($idPrefix)) {
            $idPrefix .= '.';
        }

        foreach ($modelColumns as $field => &$fieldTca) {
            // skip columns without TCA or field has not DatabaseField attribute
            if (
                is_null($fieldTca)
                || (array_key_exists('_hasDatabaseFieldAttribute', $fieldTca) && !$fieldTca['_hasDatabaseFieldAttribute'])
            ) {
                continue;
            }

            if (!array_key_exists('label', $fieldTca)) {
                $fieldTca['label'] = $translationFile . ':' . $idPrefix . $field;
            }
        }
    }

}