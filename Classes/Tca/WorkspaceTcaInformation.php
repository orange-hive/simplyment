<?php

namespace OrangeHive\Simplyment\Tca;

class WorkspaceTcaInformation implements TcaInformationInterface
{

    public static function getTca(string $tableName): array
    {
        return [
            'ctrl' => [
                'versioningWS' => true,
                'shadowColumnsForNewPlaceholders' => 'sys_language_uid',
                'origUid' => 't3_origuid',
            ],
            'columns' => [
                't3ver_label' => [
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
                    'config' => [
                        'type' => 'input',
                        'size' => 30,
                        'max' => 255,
                    ],
                ],
            ],
        ];
    }

}