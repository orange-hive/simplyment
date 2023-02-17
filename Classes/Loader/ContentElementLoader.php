<?php

namespace OrangeHive\Simplyment\Loader;

use OrangeHive\Simplyment\Attributes\ContentElement;
use OrangeHive\Simplyment\DataProcessing\ContentElementDataProcessor;
use OrangeHive\Simplyment\Registry\ContentElementRegistry;
use OrangeHive\Simplyment\Renderer\BlueprintRenderer;
use OrangeHive\Simplyment\Utility\ModelTcaUtility;
use ReflectionClass;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
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
                    position: $attributeInstance->position,
                    icon: $attributeInstance->iconPath,
                    hideContentElement: $attributeInstance->hideContentElement
                );
            }
        }
    }

    public static function extLocalconf(string $vendorName, string $extensionName): void
    {
        self::load($vendorName, $extensionName);
    }

    public static function extTables(string $vendorName, string $extensionName): void
    {
        self::registerWizardItems($extensionName);
        self::createTemplateFileIfNotExistent($extensionName);
        self::registerTemplates($extensionName);
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

            // add content element to tt_content selection
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    $ceData['name'],
                    $ceSignature,
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

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $modelColumnsTca);

            // register content element as tt_content type
            $contentTca = ModelTcaUtility::getContentElementTca($fqcn);
            $GLOBALS['TCA']['tt_content']['types'][$ceSignature] = $contentTca;
        }
    }

    public static function registerWizardItems(string $extensionKey): void
    {
        $data = [];

        /** @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        $defaultIcon = ExtensionManagementUtility::getExtensionIcon(
            extensionPath: ExtensionManagementUtility::extPath('simplyment'),
            returnFullPath: true
        );

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



            // register icon
            $iconIdentifier = 'ce-icon-' . $signature;
            $iconPath = $ceData['iconPath'] ?? $defaultIcon;

            $iconProvider = BitmapIconProvider::class;
            if (mb_substr(mb_strtolower($iconPath), -3) === 'svg') {
                $iconProvider = SvgIconProvider::class;
            }

            $iconRegistry->registerIcon(
                $iconIdentifier,
                $iconProvider,
                [
                    'source' => $iconPath,
                ]
            );


            $data[$tab]['signatures'][] = $signature;
            $data[$tab]['items'][] = $signature . ' {
                iconIdentifier = ' . $iconIdentifier . '
                title = ' . $ceData['name'] . '
                description = ' . ($ceData['description'] ?? '') . '
                tt_content_defValues {
                    CType = ' . $signature . '
                }
            }';
        }


        foreach ($data as $tab => $tabData) {
            ExtensionManagementUtility::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.' . $tab . ' {
  elements {
    ' . implode(LF, $tabData['items']). '
  }
  show:= addToList(' . implode(',', $tabData['signatures']) . ')
}');
        }
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

}