<?php
/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Simplyment',
    'description' => 'Make TYPO3 development easier with using PHP attributes for common tasks.',
    'category' => 'be',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'OrangeHive\\Simplyment\\' => 'Classes/',
        ],
    ],
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'author' => 'Stefan Glotzbach',
    'author_email' => 's.glotzbach@orangehive.de',
    'author_company' => 'Orange Hive',
    'version' => '1.0.0',
];
