<?php

namespace OrangeHive\Simplyment\Service;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocalizationService
{

    public function addTransUnit(string $extensionKey, string $id, string $source)
    {
        $txt = <<<TEXT
<trans-unit id="{$id}"><source>{$source}</source></trans-unit>
TEXT;

        $languagePath = $this->getLanguagePath($extensionKey);
        $languageFilePath = $languagePath . '/locallang.xlf';

        if (!file_exists($languageFilePath)) {
            GeneralUtility::mkdir_deep($languagePath);
            GeneralUtility::writeFile($languageFilePath, $this->getBaseXliffStructure($extensionKey));
        }

        $content = file_get_contents($languageFilePath);
        //str_replace();
    }

    protected function getLanguagePath(string $extensionKey): string
    {
        return ExtensionManagementUtility::extPath($extensionKey) . 'Resources/Private/Language';
    }

    protected function getBaseXliffStructure(string $extensionKey): string
    {
        $date = date('c');

        return <<<TEXT
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<xliff version="1.0">
	<file source-language="en" datatype="plaintext" original="messages" date="{$date}" product-name="{$extensionKey}">
		<body>
        </body>
    </file>
</xliff>
TEXT;
    }

}