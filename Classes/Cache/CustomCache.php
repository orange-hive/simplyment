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
        return unserialize(file_get_contents(self::getCachePath($identifier)));
    }

    public static function set(string $identifier, string|array $data): void
    {
        $mainCachePath = self::getCachePath('');
        if (!file_exists($mainCachePath) || !is_dir($mainCachePath)) {
            GeneralUtility::mkdir_deep($mainCachePath);
        }

        file_put_contents(self::getCachePath($identifier), serialize($data));
    }

}