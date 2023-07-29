<?php


use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\SimplymentExtensionRegistry;

defined('TYPO3') or die();


if (CustomCache::has(SimplymentExtensionRegistry::CACHE_IDENTIFIER)) {
    $extensions = CustomCache::get(SimplymentExtensionRegistry::CACHE_IDENTIFIER);

    foreach ($extensions as $extension) {
        \OrangeHive\Simplyment\Loader::tcaTtContentOverrides($extension['vendor'], $extension['extensionKey']);
    }
}

\OrangeHive\Simplyment\Loader\PluginLoader::register();