<?php

namespace OrangeHive\Simplyment\Utility;

use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormUtility
{

    public static function xml2array(string $xml): array
    {
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        return $flexFormService->convertFlexFormContentToArray($xml) ?? [];
    }

}