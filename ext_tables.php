<?php

use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\SimplymentExtensionRegistry;

defined('TYPO3') or die('Access denied.');

\OrangeHive\Simplyment\Loader::extTables(
    vendorName: 'OrangeHive',
    extensionName: 'simplyment',
    loaders: [
        \OrangeHive\Simplyment\Loader\HookLoader::class,
    ]
);


if (CustomCache::has(SimplymentExtensionRegistry::CACHE_IDENTIFIER)) {
    $extensions = CustomCache::get(SimplymentExtensionRegistry::CACHE_IDENTIFIER);

    foreach ($extensions as $extension) {
        \OrangeHive\Simplyment\Loader::extTables($extension['vendor'], $extension['extensionKey']);
    }
}