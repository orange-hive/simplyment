<?php

namespace OrangeHive\Simplyment\Renderer;

use OrangeHive\Simplyment\Loader\DatabaseModelLoader;
use OrangeHive\Simplyment\Registry\ContentElementRegistry;
use OrangeHive\Simplyment\Utility\ModelUtility;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;


class ContentElementPreviewRenderer extends StandardContentPreviewRenderer
{
    protected function renderContentElementPreviewFromFluidTemplate(array $row, ?GridColumnItem $item = null): ?string
    {
        $tsConfig = BackendUtility::getPagesTSconfig($row['pid'])['mod.']['web_layout.']['tt_content.']['preview.'] ?? [];
        $fluidTemplateFile = '';

        if ($row['CType'] === 'list' && !empty($row['list_type'])
            && !empty($tsConfig['list.'][$row['list_type']])
        ) {
            $fluidTemplateFile = $tsConfig['list'][$row['list_type']];
        } elseif (!empty($tsConfig[$row['CType']])) {
            $fluidTemplateFile = $tsConfig[$row['CType']];
            $typoscript = $this->getTyposcript($row['pid'])->setup['tt_content.'][$row['CType'] . '.'];
        }

        $ceData = ContentElementRegistry::getBySignature($row['CType']);
        $uid = $row['uid'];
        $model = ModelUtility::getModel($ceData['fqcn'], $uid);


        if ($fluidTemplateFile) {
            $fluidTemplateFile = GeneralUtility::getFileAbsFileName($fluidTemplateFile);
            if ($fluidTemplateFile) {
                try {
                    $view = GeneralUtility::makeInstance(StandaloneView::class);
                    $view->setTemplatePathAndFilename($fluidTemplateFile);
                    $view->assignMultiple($row);
                    if (!empty($row['pi_flexform'])) {
                        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
                        $view->assign('pi_flexform_transformed', $flexFormService->convertFlexFormContentToArray($row['pi_flexform']));
                    }
                    if ($typoscript && $typoscript['layoutRootPaths.']) {
                        $view->setLayoutRootPaths(array_values($typoscript['layoutRootPaths.']));
                    }
                    if ($typoscript && $typoscript['partialRootPaths.']) {
                        $view->setPartialRootPaths(array_values($typoscript['partialRootPaths.']));
                    }

                    if ($model) {
                        $view->assign('object', $model);
                    }

                    return $view->render();
                } catch (\Exception $e) {
                    $this->logger->warning('The backend preview for content element {uid} can not be rendered using the Fluid template file "{file}"', [
                        'uid' => $row['uid'],
                        'file' => $fluidTemplateFile,
                        'exception' => $e,
                    ]);

                    if ($this->getBackendUser()->shallDisplayDebugInformation()) {
                        $view = GeneralUtility::makeInstance(StandaloneView::class);
                        $view->assign('error', [
                            'message' => str_replace(Environment::getProjectPath(), '', $e->getMessage()),
                            'title' => 'Error while rendering FluidTemplate preview using ' . str_replace(Environment::getProjectPath(), '', $fluidTemplateFile),
                        ]);
                        $view->setTemplateSource('<f:be.infobox title="{error.title}" state="2">{error.message}</f:be.infobox>');
                        return $view->render();
                    }
                }
            }
        }
        return null;
    }

    // see: https://www.in2code.de/aktuelles/php-typoscript-im-backend-oder-command-kontext-nutzen/
    protected function getTyposcript(int $pageUid): TemplateService
    {
        /** @var RootlineUtility $rootlineUtil */
        $rootlineUtil = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        /** @var TemplateService $templateService */
        $templateService = GeneralUtility::makeInstance(TemplateService::class);

        // get the rootline
        $rootLine = $rootlineUtil->get();

        // initialize template service and generate typoscript configuration
        $templateService->runThroughTemplates($rootLine);
        $templateService->generateConfig();

        return $templateService;
    }
}
