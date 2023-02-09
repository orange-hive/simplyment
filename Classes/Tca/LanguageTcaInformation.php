<?php

namespace OrangeHive\Simplyment\Tca;

class LanguageTcaInformation implements TcaInformationInterface
{

    public static function getTca(string $tableName): array
    {
        return [
            'ctrl' => [
                'languageField' => 'sys_language_uid',
                'transOrigPointerField' => 'l10n_parent',
                'transOrigDiffSourceField' => 'l10n_diffsource',
            ],
            'columns' => [
                'sys_language_uid' => [
                    'exclude' => 1,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'default' => '0',
                        'special' => 'languages',
                        'items' => [
                            [
                                'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                                -1,
                                'flags-multiple',
                            ],
                        ],
                    ],
                ],
                'l10n_parent' => [
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'default' => 0,
                        'items' => [
                            [
                                '',
                                0,
                            ],
                        ],
                        'foreign_table' => $tableName,
                        'foreign_table_where' => 'AND ' . $tableName . '.pid=###CURRENT_PID### AND ' . $tableName . '.sys_language_uid IN (-1,0)',
                    ],
                ],
                'l10n_diffsource' => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
            ],
            'palettes' => [
                'language' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource'],
            ],
        ];
    }

}