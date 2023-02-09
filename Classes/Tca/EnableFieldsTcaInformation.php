<?php

namespace OrangeHive\Simplyment\Tca;

class EnableFieldsTcaInformation implements TcaInformationInterface
{

    public static function getTca(string $tableName): array
    {
        return [
            'ctrl' => [
                'enablecolumns' => [
                    'disabled' => 'hidden',
                    'starttime' => 'starttime',
                    'endtime' => 'endtime',
                    'fe_group' => 'fe_group',
                ],
                'editlock' => 'editlock',
            ],
            'columns' => [
                'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
                'editlock' => [
                    'exclude' => 1,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:editlock',
                    'config' => [
                        'type' => 'check',
                        'behaviour' => [
                            'allowLanguageSynchronization' => true,
                        ],
                    ],
                ],
                'hidden' => [
                    'exclude' => 1,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
                    'config' => [
                        'type' => 'check',
                    ],
                ],
                'starttime' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
                    'config' => [
                        'type' => 'input',
                        'renderType' => 'inputDateTime',
                        'eval' => 'datetime',
                        'default' => 0,
                    ],
                    'l10n_mode' => 'exclude',
                    'l10n_display' => 'defaultAsReadonly',
                ],
                'endtime' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
                    'config' => [
                        'type' => 'input',
                        'renderType' => 'inputDateTime',
                        'eval' => 'datetime',
                        'default' => 0,
                        'range' => [
                            'upper' => mktime(0, 0, 0, 1, 1, 2050),
                        ],
                    ],
                    'l10n_mode' => 'exclude',
                    'l10n_display' => 'defaultAsReadonly',
                ],
            ],
        ];
    }

}