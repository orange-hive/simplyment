<?php

namespace OrangeHive\Simplyment\Loader;

use OrangeHive\Simplyment\Attributes\ContentElement;
use OrangeHive\Simplyment\Attributes\DatabaseField;
use OrangeHive\Simplyment\Attributes\DatabaseTable;
use OrangeHive\Simplyment\Attributes\Tca;
use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\DatabaseModelRegistry;
use OrangeHive\Simplyment\Registry\TableOnStandardPagesRegistry;
use OrangeHive\Simplyment\Renderer\BlueprintRenderer;
use OrangeHive\Simplyment\Utility\ClassNameUtility;
use OrangeHive\Simplyment\Utility\ModelTcaUtility;
use ReflectionClass;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseModelLoader extends AbstractLoader implements LoaderInterface
{

    public static function load(string $vendorName, string $extensionKey): void
    {
        $databaseModelRegistryCacheIdentifier = CustomCache::createIdentifier(DatabaseModelRegistry::class, $vendorName, $extensionKey);
        $tableOnStandardPagesRegistryCacheIdentifier = CustomCache::createIdentifier(TableOnStandardPagesRegistry::class, $vendorName, $extensionKey);

        $databaseModelRegistryCacheData = CustomCache::get($databaseModelRegistryCacheIdentifier);
        $tableOnStandardPagesRegistryCacheData = CustomCache::get($tableOnStandardPagesRegistryCacheIdentifier);

        if (
            is_array($databaseModelRegistryCacheData)
            && is_array($tableOnStandardPagesRegistryCacheData)
        ) {
            DatabaseModelRegistry::set($databaseModelRegistryCacheData);
            TableOnStandardPagesRegistry::set($tableOnStandardPagesRegistryCacheData);
            return;
        }

        $extPath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Classes/Domain/Model');

        $files = array_merge(
            glob($extPath . '/*.php'),
            glob($extPath . '/Content/*.php')
        );
        foreach ($files as $file) {
            $fqcn = ClassNameUtility::getFqcnFromPath(vendorName: $vendorName, extensionKey: $extensionKey, path: $file);

            if (is_null($fqcn)) {
                continue;
            }

            $classRef = new ReflectionClass($fqcn);

            $tableName = '';

            $isOverride = (count($classRef->getAttributes(ContentElement::class)) > 0);
            if ($isOverride) {
                $tableName = 'tt_content';
                DatabaseModelRegistry::addOverrideInformation(
                    tableName: $tableName,
                    overrideTableName: $tableName
                );
            }

            foreach ($classRef->getAttributes(DatabaseTable::class) as $attribute) {
                /** @var DatabaseTable $instance */
                $instance = $attribute->newInstance();

                $tableName = ClassNameUtility::getTableNameByFqcn($fqcn);
                if (!is_null($instance->tableName)) {
                    $tableName = $instance->tableName;

                    DatabaseModelRegistry::addOverrideInformation(
                        tableName: $tableName,
                        overrideTableName: $tableName
                    );
                }

                DatabaseModelRegistry::addTable(
                    tableName: $tableName,
                    fqcn: $fqcn,
                    indices: $instance->indices
                );
            }

            foreach ($classRef->getProperties() as $property) {
                foreach ($property->getAttributes(DatabaseField::class) as $attribute) {
                    /** @var DatabaseField $instance */
                    $instance = $attribute->newInstance();

                    $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($property->getName());

                    DatabaseModelRegistry::addField($tableName, $fieldName, $instance->getFieldConfiguration());
                }
            }

            // process Tca attributes on class
            foreach ($classRef->getAttributes(Tca::class) as $attribute) {
                /** @var Tca $instance */
                $instance = $attribute->newInstance();
                if ($instance->allowOnStandardPage) {
                    TableOnStandardPagesRegistry::addTable($tableName);
                }
            }
        }

        CustomCache::set($databaseModelRegistryCacheIdentifier, DatabaseModelRegistry::list());
        CustomCache::set($tableOnStandardPagesRegistryCacheIdentifier, TableOnStandardPagesRegistry::list());
    }


    public static function extLocalconf(string $vendorName, string $extensionName): void
    {

    }

    public static function extTables(string $vendorName, string $extensionName): void
    {
        self::load($vendorName, $extensionName);
        self::allowTablesOnStandardPages();
        self::createTcaFileIfNotExistent();
    }

    public static function classes(string $vendorName, string $extensionName): array
    {
        // get database models extending existent tables class mappings
        $mapping = [];

        foreach (DatabaseModelRegistry::list() as $tableName => $data) {
            if (
                array_key_exists('overrideTableName', $data)
                && $data['overrideTableName'] !== 'tt_content'
            ) {
                $mapping[$data['fqcn']] = [
                    'tableName' => $tableName,
                ];
            }
        }

        return $mapping;
    }

    /** special handling for DatabaseModel with tableName = tt_content due to mix with ContentElementLoader */
    public static function tcaTtContentOverrides(string $vendorName, string $extensionName): void
    {
        self::load($vendorName, $extensionName);

        foreach (DatabaseModelRegistry::list() as $tableName => $data) {
            if ($tableName !== 'tt_content' || is_null($data['fqcn'])) {
                continue;
            }

            $customColumnOverrides = [];
            ModelTcaUtility::addColumnTcaOverrides(
                fqcn: $data['fqcn'],
                tableName: $tableName,
                columnOverrides: $customColumnOverrides
            );
        }
    }

    protected static function allowTablesOnStandardPages()
    {
        foreach (TableOnStandardPagesRegistry::list() as $tableName) {
            ExtensionManagementUtility::allowTableOnStandardPages($tableName);
        }
    }

    protected static function createTcaFileIfNotExistent()
    {
        foreach (DatabaseModelRegistry::list() as $tableName => $data) {
            if (array_key_exists('overrideTableName', $data)) {
                // tt_content overrides are handled at Loader->tcaTtContentOverrides
                if ($data['overrideTableName'] !== 'tt_content') {
                    self::createTcaOverrideFileIfNotExistent($tableName, $data);
                }

                continue;
            }

            $fqcn = $data['fqcn'];
            $extensionKey = ClassNameUtility::getExtensionKey($fqcn);

            $extTcaPath = ExtensionManagementUtility::extPath($extensionKey) . 'Configuration/TCA';
            $tcaFilePath = $extTcaPath . '/' . $tableName . '.php';

            if (!file_exists($tcaFilePath)) {
                GeneralUtility::mkdir_deep($extTcaPath);

                $content = BlueprintRenderer::render('Configuration/TCA/CustomTable', [
                    'FQCN' => '\\' . $fqcn . '::class',
                ]);

                GeneralUtility::writeFile($tcaFilePath, $content);
            }
        }
    }

    protected static function createTcaOverrideFileIfNotExistent(string $tableName, array $data)
    {
        $fqcn = $data['fqcn'];
        $extensionKey = ClassNameUtility::getExtensionKey($fqcn);

        $extTcaPath = ExtensionManagementUtility::extPath($extensionKey) . 'Configuration/TCA/Overrides';
        $tcaFilePath = $extTcaPath . '/' . $tableName . '.php';

        if (!file_exists($tcaFilePath)) {
            GeneralUtility::mkdir_deep($extTcaPath);

            $content = BlueprintRenderer::render('Configuration/TCA/Overrides/table', [
                'FQCN' => '\\' . $fqcn . '::class',
                'TABLE' => $tableName,
            ]);

            GeneralUtility::writeFile($tcaFilePath, $content);
        }
    }

}