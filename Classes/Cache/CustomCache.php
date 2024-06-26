<?php

namespace OrangeHive\Simplyment\Cache;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class CustomCache extends AbstractCache
{

    public static function has(string $identifier): bool
    {
        return self::exists($identifier);
    }

    public static function get(string $identifier)
    {
        $path = self::getCachePath($identifier);
        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);

        if (!is_string($content)) {
            return null;
        }

        return unserialize($content);
    }

    public static function set(string $identifier, string|array $data): void
    {
        $mainCachePath = self::getCachePath('');
        if (!file_exists($mainCachePath) || !is_dir($mainCachePath)) {
            GeneralUtility::mkdir_deep($mainCachePath);
        }

        file_put_contents(self::getCachePath($identifier), serialize($data));
    }

    public static function flush(string $identifier = ''): void
    {
        $cachePath = self::getCachePath($identifier);
        if (file_exists($cachePath)) {
            if (is_dir($cachePath)) {
                GeneralUtility::rmdir($cachePath, true);
            } else {
                unlink($cachePath);
            }
        }
    }

}