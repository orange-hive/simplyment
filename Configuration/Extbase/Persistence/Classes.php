<?php


use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\SimplymentExtensionRegistry;


$mapping = [];
if (CustomCache::has(SimplymentExtensionRegistry::CACHE_IDENTIFIER)) {
    $extensions = CustomCache::get(SimplymentExtensionRegistry::CACHE_IDENTIFIER);

    foreach ($extensions as $extension) {
        $mapping = array_merge($mapping, \OrangeHive\Simplyment\Loader::classes($extension['vendor'], $extension['extensionKey']));
    }
}

$simplymentMapping = \OrangeHive\Simplyment\Loader::classes('OrangeHive', 'simplyment');


return array_merge($mapping, $simplymentMapping);