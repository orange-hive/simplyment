<?php

namespace OrangeHive\Simplyment\Cache;

use TYPO3\CMS\Core\Core\Environment;

abstract class AbstractCache
{

    protected const CACHE_IDENTIFIER = 'simplyment';

    /**
     * Check if cache file exists, used for backend layout loader check
     */
    public static function exists(string $identifier): bool
    {
        return file_exists(self::getCachePath($identifier));
    }

    abstract public static function has(string $identifier): bool;

    abstract public static function get(string $identifier);

    abstract public static function set(string $identifier, string|array $data): void;

    public static function createIdentifier(string $fqcn, ?string $vendorName = null, ?string $extensionKey = null): string
    {
        $name = substr($fqcn, strrpos($fqcn, '\\') + 1);

        $parts = [
            $name
        ];

        if (!is_null($vendorName)) {
            $parts[] = $vendorName;
        }

        if (!is_null($extensionKey)) {
            $parts[] = $extensionKey;
        }

        return implode('_', $parts);
    }

    protected static function escapeIdentifier(string $identifier): string
    {
        return preg_replace('/[^a-z_\-0-9]/i', '_', $identifier);
    }

    protected static function getCachePath(string $identifier): string
    {
        return Environment::getVarPath() . '/cache/data/' . self::CACHE_IDENTIFIER . '/' . self::escapeIdentifier($identifier);
    }

}