<?php

namespace OrangeHive\Simplyment\Cache;

use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\CacheManager as Typo3CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Typo3Cache extends AbstractCache
{

    protected static ?Typo3CacheManager $cacheManager = null;

    public static function has(string $identifier): bool
    {
        return self::getCache()->has(self::escapeIdentifier($identifier));
    }

    public static function get(string $identifier)
    {
        return self::getCache()->get(self::escapeIdentifier($identifier));
    }

    public static function set(string $identifier, string|array $data): void
    {
        self::getCache()->set(self::escapeIdentifier($identifier), $data);
    }

    protected static function getCache()
    {
        $cm = self::getCacheManager();

        if (!$cm->hasCache(self::CACHE_IDENTIFIER)) {
            $backend = new SimpleFileBackend(getenv('TYPO3_CONTEXT') ?? 'production');
            $frontendCache = new VariableFrontend('simplyment', $backend);

            $cm->registerCache($frontendCache);
        }

        return $cm->getCache(self::CACHE_IDENTIFIER);
    }

    protected static function getCacheManager(): Typo3CacheManager
    {
        if (is_null(self::$cacheManager)) {
            self::$cacheManager = GeneralUtility::makeInstance(Typo3CacheManager::class);
        }

        return self::$cacheManager;
    }

}