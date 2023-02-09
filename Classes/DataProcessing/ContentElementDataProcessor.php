<?php

namespace OrangeHive\Simplyment\DataProcessing;

use OrangeHive\Simplyment\Registry\ContentElementRegistry;
use OrangeHive\Simplyment\Utility\ModelUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ContentElementDataProcessor implements DataProcessorInterface
{

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array
    {
        $ceData = ContentElementRegistry::getBySignature($cObj->data['CType']);

        $uid = $cObj->data['uid'];

        $model = ModelUtility::getModel($ceData['fqcn'], $uid);

        $processedData['object'] = $model;


        return $processedData;
    }

}