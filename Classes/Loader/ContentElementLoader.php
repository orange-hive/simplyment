<?php

namespace OrangeHive\Simplyment\Loader;

use OrangeHive\Simplyment\Attributes\ContentElement;
use OrangeHive\Simplyment\DataProcessing\ContentElementDataProcessor;
use OrangeHive\Simplyment\Registry\ContentElementRegistry;
use OrangeHive\Simplyment\Renderer\BlueprintRenderer;
use OrangeHive\Simplyment\Utility\IconUtility;
use OrangeHive\Simplyment\Utility\LocalizationUtility;
use OrangeHive\Simplyment\Utility\ModelTcaUtility;
use ReflectionClass;
use TYPO3\CMS\Core\Configuration\Loader\PageTsConfigLoader;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentElementLoader extends AbstractLoader
{

    public static function load(string $vendorName, string $extensionKey): void
    {
        $extPath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Classes/Domain/Model/Content');

        $files = glob($extPath . '/*.php');

        foreach ($files as $file) {
            $className = basename($file, '.php');

            $fqcn = $vendorName . '\\' . ucfirst(GeneralUtility::underscoredToUpperCamelCase($extensionKey)) . '\\Domain\\Model\\Content\\' . $className;

            $classRef = new ReflectionClass($fqcn);

            foreach ($classRef->getAttributes(ContentElement::class) as $attribute) {
                /** @var ContentElement $attributeInstance */
                $attributeInstance = $attribute->newInstance();

                ContentElementRegistry::add(
                    name: $attributeInstance->name,
                    extensionKey: $extensionKey,
                    fqcn: $fqcn,
                    tab: $attributeInstance->wizardTab,
                    position: $attributeInstance->position,
                    flexFormPath: $attributeInstance->flexFormPath,
                    icon: $attributeInstance->iconPath,
                    hideContentElement: $attributeInstance->hideContentElement
                );
            }
        }
    }

    public static function extLocalconf(string $vendorName, string $extensionName): void
    {
        self::load($vendorName, $extensionName);
        self::registerTemplates($extensionName);
        self::createTemplateFileIfNotExistent($extensionName);
    }

    public static function extTables(string $vendorName, string $extensionName): void
    {
        self::registerWizardItems($extensionName);
    }

    public static function classes(string $vendorName, string $extensionName): array
    {
        // get content element class mappings
        $mapping = [];

        foreach (ContentElementRegistry::listByExtensionKey($extensionName) as $ceSignature => $ceData) {
            $fqcn = $ceData['fqcn'];

            $mapping[$fqcn] = [
                'tableName' => 'tt_content',
            ];
        }

        return $mapping;
    }

    public static function tcaTtContentOverrides(string $vendorName, string $extensionName): void
    {
        foreach (ContentElementRegistry::listByExtensionKey($extensionName) as $ceSignature => $ceData) {
            $fqcn = $ceData['fqcn'];
            $relativeToField = '';
            $relativePosition = '';


            if (!is_null($ceData['position'])) {
                list($relativePosition, $relativeToField) = explode(':', $ceData['position']);
            }

            $translationKeyPath = 'LLL:EXT:' . $extensionName . '/Resources/Private/Language/locallang.xlf:';
            $ceTitleTranslationKey = 'content.element.' . $ceData['name'];
            if (LocalizationUtility::keyExistsInLocallang($extensionName, $ceTitleTranslationKey)) {
                $ceTitle = $translationKeyPath . $ceTitleTranslationKey;
            } else {
                $ceTitle = $ceData['name'];
            }

            $iconIdentifier = IconUtility::getIconIdentifierBySignature($ceSignature, $ceData['icon']);

            // add content element to tt_content selection
            ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    $ceTitle,
                    $ceSignature,
                    $iconIdentifier
                ],
                $relativeToField,
                $relativePosition
            );


            // add content element TCA columns to tt_content columns, do not override already existent columns!
            // override is handled in $contentTca with overrideColumns
            $modelColumnsTca = ModelTcaUtility::getModelColumnsTca($fqcn);
            $existentColumns = array_keys($GLOBALS['TCA']['tt_content']['columns']);
            $modelColumnsTca = array_filter($modelColumnsTca, function($key) use ($existentColumns) {
                return !in_array($key, $existentColumns);
            }, ARRAY_FILTER_USE_KEY);

            ExtensionManagementUtility::addTCAcolumns('tt_content', $modelColumnsTca);


            // flexform registration
            $hasFlexForm = self::registerFlexForm(extensionKey: $extensionName, ceSignature: $ceSignature, ceData: $ceData);


            // register content element as tt_content type
            $contentTca = ModelTcaUtility::getContentElementTca(fqcn: $fqcn, hasFlexForm: $hasFlexForm);
            $GLOBALS['TCA']['tt_content']['types'][$ceSignature] = $contentTca;

            // add icon for content element in backend rendering
            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$ceSignature] = $iconIdentifier;
        }
    }

    public static function registerWizardItems(string $extensionKey): void
    {
        $data = [];
        $translationKeyPath = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:';

        foreach (ContentElementRegistry::listByExtensionKey($extensionKey) as $signature => $ceData) {
            if ($ceData['hideContentElement']) {
                continue;
            }

            $tab = $ceData['tab'];

            if (!array_key_exists($tab, $data)) {
                $data[$tab] = [
                    'signatures' => [],
                    'items' => [],
                ];
            }

            $iconIdentifier = IconUtility::getIconIdentifierBySignature($signature, $ceData['icon']);

            $ceTitleTranslationKey = 'content.element.' . $ceData['name'];
            if (LocalizationUtility::keyExistsInLocallang($extensionKey, $ceTitleTranslationKey)) {
                $ceTitle = $translationKeyPath . $ceTitleTranslationKey;
            } else {
                $ceTitle = $ceData['name'];
            }

            $ceDescriptionTranslationKey = $translationKeyPath . 'content.element.' . $ceData['name'] . '.description';
            $ceDescription = ($ceData['description'] ?? $ceDescriptionTranslationKey);

            $data[$tab]['signatures'][] = $signature;
            $data[$tab]['items'][] = $signature . ' {
                iconIdentifier = ' . $iconIdentifier . '
                title = ' . $ceTitle . '
                description = ' . $ceDescription . '
                tt_content_defValues {
                    CType = ' . $signature . '
                }
            }';
        }


        foreach ($data as $tab => $tabData) {
            // set wizard tab header property if not existent, use from translation if defined
            $headerTs = '';
            if (!self::wizardTabHeaderExists($tab)) {
                $ceTitleTranslationKey = 'wizardTab.' . $tab;
                if (LocalizationUtility::keyExistsInLocallang($extensionKey, $ceTitleTranslationKey)) {
                    $tabHeader = $translationKeyPath . $ceTitleTranslationKey;
                } else {
                    $tabHeader = $tab;
                }

                $headerTs = 'header = ' . $tabHeader;
            }

            ExtensionManagementUtility::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.' . $tab . ' {
  ' . $headerTs . '
  elements {
    ' . implode(LF, $tabData['items']). '
  }
  show:= addToList(' . implode(',', $tabData['signatures']) . ')
}');
        }
    }

    protected static function wizardTabHeaderExists($tab): bool {
        $loader = GeneralUtility::makeInstance(PageTsConfigLoader::class);
        $tsConfigString = $loader->load([]);

        $typoScriptParser = GeneralUtility::makeInstance(TypoScriptParser::class);

        $typoScriptParser->parse($tsConfigString);
        $parsed = $typoScriptParser->setup;
        $header = $typoScriptParser->getVal('mod.wizards.newContentElement.wizardItems.' . $tab . '.header', $parsed);

        return (!empty($header[0]));
    }

    protected static function createTemplateFileIfNotExistent(string $extensionKey): void
    {
        $blueprintMappings = [
            [
                'blueprintName' => 'Frontend',
                'path' => 'Resources/Private/Templates/Content',
                'fileNameSuffix' => '',
            ],
            [
                'blueprintName' => 'Backend',
                'path' => 'Resources/Private/Templates/Content',
                'fileNameSuffix' => 'Backend',
            ],
        ];

        foreach (ContentElementRegistry::listByExtensionKey($extensionKey) as $signature => $ceData) {
            $extensionKey = $ceData['extensionKey'];
            $name = $ceData['name'];

            foreach ($blueprintMappings as $mapping) {
                $fileName = $name . $mapping['fileNameSuffix'];

                $absPath = ExtensionManagementUtility::extPath($extensionKey, $mapping['path'] . '/' . $fileName . '.html');

                if (file_exists($absPath)) {
                    continue;
                }

                $content = BlueprintRenderer::render('Resources/Private/Templates/Content/' . $mapping['blueprintName'], [
                    'CONTENT_ELEMENT' => $name,
                    'TEMPLATE_PATH' => 'EXT:' . $extensionKey . '/' . $mapping['path'] . '/' . $fileName . '.html',
                ]);

                GeneralUtility::mkdir_deep(ExtensionManagementUtility::extPath($extensionKey));
                GeneralUtility::writeFile($absPath, $content);
            }
        }
    }

    public static function registerTemplates(string $extensionKey)
    {
        $ceDataProcessorFqcn = ContentElementDataProcessor::class;

        $typoScript = '';
        $pageTypoScript = '';
        foreach (ContentElementRegistry::listByExtensionKey($extensionKey) as $signature => $ceData) {
            $extensionKey = $ceData['extensionKey'];
            $name = $ceData['name'];

            $typoScript .= <<<TEXT

tt_content.{$signature} =< lib.contentElement
tt_content.{$signature}.templateName = {$name}
tt_content.{$signature}.templateRootPaths.200 = EXT:{$extensionKey}/Resources/Private/Templates/Content/
tt_content.{$signature}.partialRootPaths.200 = EXT:{$extensionKey}/Resources/Private/Partials/Content/
tt_content.{$signature}.layoutRootPaths.200 = EXT:{$extensionKey}/Resources/Private/Layouts/Content/
tt_content.{$signature}.dataProcessing {
    10 = {$ceDataProcessorFqcn}
}

TEXT;

            // register backend preview
            $pageTypoScript .= <<<TEXT

mod.web_layout.tt_content.preview.{$signature} = EXT:{$extensionKey}/Resources/Private/Templates/Content/{$name}Backend.html

TEXT;

        }

        ExtensionManagementUtility::addTypoScriptSetup($typoScript);
        ExtensionManagementUtility::addPageTSConfig($pageTypoScript);
    }

    protected static function registerFlexForm(string $extensionKey, string $ceSignature, array $ceData): bool
    {
        $flexFormPath = null;
        if (array_key_exists('flexForm', $ceData) && !empty($ceData['flexForm'])) {
            $flexFormPath = $ceData['flexForm'];
        } else {
            // try to load from default directory
            $filePathInExtension = 'Configuration/FlexForms/Content/' . $ceData['name'] . '.xml';

            $defaultPath = ExtensionManagementUtility::extPath($extensionKey)
                . $filePathInExtension;

            if (file_exists($defaultPath)) {
                $flexFormPath = 'EXT:' . $extensionKey . '/' . $filePathInExtension;
            }
        }

        if (!is_null($flexFormPath)) {
            if (strpos($flexFormPath, 'EXT:') === 0) {
                $flexFormPath = 'FILE:' . $flexFormPath;
            }
            ExtensionManagementUtility::addPiFlexFormValue(
                '*',
                $flexFormPath,
                $ceSignature
            );
        }

        return !is_null($flexFormPath);
    }

}