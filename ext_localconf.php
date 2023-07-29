<?php

use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\SimplymentExtensionRegistry;

defined('TYPO3') or die('Access denied.');

// register custom TCA renderType
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1675267771] = [
    'nodeName' => 'simplymentPlaceholderElement',
    'priority' => 40,
    'class' => \OrangeHive\Simplyment\Form\Element\SimplymentPlaceholderElement::class
];

// register backend layout hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['simplyment'] = 'OrangeHive\Simplyment\Hook\BackendLayoutDataProvider';



if (CustomCache::has(SimplymentExtensionRegistry::CACHE_IDENTIFIER)) {
    $extensions = CustomCache::get(SimplymentExtensionRegistry::CACHE_IDENTIFIER);

    foreach ($extensions as $extension) {
        \OrangeHive\Simplyment\Loader::extLocalconf($extension['vendor'], $extension['extensionKey']);
    }
}