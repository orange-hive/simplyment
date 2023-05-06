<?php
/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Simplyment',
    'description' => 'Make TYPO3 development easier with using PHP attributes for common tasks.',
    'category' => 'be',
    'constraints' => [
        'depends' => [
            'php'   => '8.0.0-8.99.99',
            'typo3' => '11.5.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'OrangeHive\\Simplyment\\' => 'Classes/',
        ],
    ],
    'version' => '1.2.0',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Stefan Glotzbach',
    'author_email' => 's.glotzbach@orangehive.de',
    'author_company' => 'Orange Hive',
];
