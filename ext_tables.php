<?php
defined('TYPO3') or die('Access denied.');

\OrangeHive\Simplyment\Loader::extTables(
    vendorName: 'OrangeHive',
    extensionName: 'simplyment',
    loaders: [
        \OrangeHive\Simplyment\Loader\HookLoader::class,
    ]
);
