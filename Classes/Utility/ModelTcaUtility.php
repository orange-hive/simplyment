<?php

namespace OrangeHive\Simplyment\Utility;

use OrangeHive\Simplyment\Service\TcaService;
use OrangeHive\Simplyment\Tca\EnableFieldsTcaInformation;
use OrangeHive\Simplyment\Tca\LanguageTcaInformation;
use OrangeHive\Simplyment\Tca\WorkspaceTcaInformation;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as Typo3LocalizationUtility;

class ModelTcaUtility
{

    public static function getTca(string $fqcn): array
    {
        $tableName = ClassNameUtility::getTableNameByFqcn($fqcn);
        $extensionKey = ClassNameUtility::getExtensionKey($fqcn);
        $translationFile = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf';
        $tableTitle = $translationFile . ':' . $tableName;

        $tcaService = GeneralUtility::makeInstance(TcaService::class, $fqcn);

        $modelTca = $tcaService->getModelTca();
        $modelColumns = $tcaService->getModelColumns();
        LocalizationUtility::addLocalizationLabel($fqcn, $modelColumns, $tableName);

        $customFields = array_keys($modelColumns);

        $showItems = array_merge($customFields, [
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language',
            '--palette--;;language',
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
            '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access',
        ]);

        $baseTca = [];
        if (!isset($baseTca['columns']) || !\is_array($baseTca['columns'])) {
            $baseTca['columns'] = $modelColumns;
        }

        $defaultIcon = ExtensionManagementUtility::getExtensionIcon(
            extensionPath: ExtensionManagementUtility::extPath('simplyment'),
            returnFullPath: true
        );

        // use header as default field, if not found use title - still not found use first custom field
        $labelField = 'header';
        if (!in_array($labelField, $customFields)) {
            $labelField = 'title';
        }
        if (!in_array($labelField, $customFields)) {
            $labelField = $customFields[0];
        }

        $overrideTca = [
            'ctrl' => [
                'title' => $tableTitle,
                'label' => $labelField,
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'dividers2tabs' => true,
                'sortby' => 'sorting',
                'delete' => 'deleted',
                //'searchFields' => implode(',', $searchFields),
                'iconfile' => $defaultIcon,

                'transOrigPointerField' => 'l18n_parent',
                'transOrigDiffSourceField' => 'l18n_diffsource',
                'origUid' => 't3_origuid',
                'languageField' => 'sys_language_uid',
                'translationSource' => 'l10n_source',
            ],
            'types' => [
                '1' => ['showitem' => implode(',', $showItems)],
            ],
            'palettes' => [
                'access' => ['showitem' => 'starttime, endtime, --linebreak--, hidden, editlock, --linebreak--, fe_group'],
            ],
        ];

        ArrayUtility::mergeRecursiveWithOverrule($overrideTca, EnableFieldsTcaInformation::getTca($tableName));
        ArrayUtility::mergeRecursiveWithOverrule($overrideTca, LanguageTcaInformation::getTca($tableName));

        if (ExtensionManagementUtility::isLoaded('workspaces')) {
            $overrideTca['ctrl']['shadowColumnsForNewPlaceholders'] .= ',' . $labelField;
            ArrayUtility::mergeRecursiveWithOverrule($overrideTca, WorkspaceTcaInformation::getTca($tableName));
        }


        $resultTca = array_merge_recursive($baseTca, $overrideTca);

        ArrayUtility::mergeRecursiveWithOverrule($resultTca, $modelTca);

        return $resultTca;
    }

    public static function addColumnTcaOverrides(string $fqcn, string $tableName, array $columnOverrides = []): void
    {
        $tcaService = GeneralUtility::makeInstance(TcaService::class, $fqcn);

        $modelColumns = $tcaService->getModelColumns();
        LocalizationUtility::addLocalizationLabel($fqcn, $modelColumns, $tableName);


        ArrayUtility::mergeRecursiveWithOverrule($modelColumns, $columnOverrides);


        ExtensionManagementUtility::addTCAcolumns($tableName, $modelColumns);
    }

    public static function addColumnsToAllTcaTypes(
        string $fqcn,
        string $tableName,
        ?array $fieldsOverride = null,
        string $typeList = '',
        string $position = ''
    ): void
    {
        if (!is_null($fieldsOverride) && !empty($fieldsOverride)) {
            $customFields = $fieldsOverride;
        } else {
            $tcaService = GeneralUtility::makeInstance(TcaService::class, $fqcn);
            $modelColumns = $tcaService->getModelColumns();
            $customFields = array_keys($modelColumns);
        }

        ExtensionManagementUtility::addToAllTCAtypes(
            table: $tableName,
            newFieldsString: implode(',', $customFields),
            typeList: $typeList,
            position: $position
        );
    }

    public static function getModelColumnsTca(string $fqcn): array
    {
        $tcaService = GeneralUtility::makeInstance(TcaService::class, $fqcn);

        $modelColumns = $tcaService->getModelColumns();

        LocalizationUtility::addLocalizationLabel($fqcn, $modelColumns, 'tt_content');

        return $modelColumns;
    }

    public static function getContentElementTca(string $fqcn): array
    {
        $tcaService = GeneralUtility::makeInstance(TcaService::class, $fqcn);

        $modelColumns = $tcaService->getModelColumns(true);
        LocalizationUtility::addLocalizationLabel($fqcn, $modelColumns, 'tt_content');

        $customFields = array_keys($modelColumns);

        $showItems = array_merge(
            [
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general',
                '--palette--;;general',
            ],
            $customFields,
            [
                '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance',
                '--palette--;;frames',
                '--palette--;;appearanceLinks',

                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language',
                '--palette--;;language',

                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
                '--palette--;;hidden',
                '--palette--;;access',

                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories',
                'categories',

                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes',
                'rowDescription',
            ]
        );

        $columnsOverrides = [];
        foreach ($modelColumns as $propertyName => $columnTca) {
            // skip model columns without TCA information
            if (is_null($columnTca)) {
                continue;
            }

            $columnsOverrides[$propertyName] = $columnTca;
        }


        $tca = [
            'showitem' => implode(',', $showItems),
            'columnsOverrides' => $columnsOverrides,
        ];

        return $tca;
    }

}