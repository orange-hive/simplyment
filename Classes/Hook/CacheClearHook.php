<?php

namespace OrangeHive\Simplyment\Hook;

use OrangeHive\Simplyment\Cache\CustomCache;
use TYPO3\CMS\Core\Service\OpcodeCacheService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheClearHook
{

    public function clearCache($params = [], &$reference = NULL) {
        // Add your code for clearing cache here.
        CustomCache::flush();
        GeneralUtility::makeInstance(OpcodeCacheService::class)->clearAllActive();
    }

}