<?php

namespace OrangeHive\Simplyment\Loader;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractLoader
{

    protected static function getCacheManager(): CacheManager
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }

}