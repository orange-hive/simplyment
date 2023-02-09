<?php

namespace OrangeHive\Simplyment\Renderer;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class BlueprintRenderer
{

    public static function render(string $templateName, array $variables = []): string
    {
        $blueprintPath = ExtensionManagementUtility::extPath('simplyment') . 'Resources/Private/Blueprint';

        $content = file_get_contents($blueprintPath . '/' . $templateName . '.tmpl');
        foreach ($variables as $placeholder => $value) {
            $content = str_replace('##' . $placeholder . '##', $value, $content);
        }

        return $content;
    }

}