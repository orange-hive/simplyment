<?php

namespace OrangeHive\Simplyment\Attributes;

use OrangeHive\Simplyment\Registry\TableOnStandardPagesRegistry;
use OrangeHive\Simplyment\Utility\ModelTcaUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Tca
{


    public function __construct(
        public bool    $allowOnStandardPage = false,
        public ?string $icon = null,
        public array   $config = []
    )
    {
    }

    public function getTca(string $fqcn = null): array
    {
        // preparation for TYPO3 >= 12
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Security.html#ctrl-security-ignorepagetyperestriction
        $tca = [
            'ctrl' => [
                'security' => [
                    'ignorePageTypeRestriction' => $this->allowOnStandardPage,
                ],
            ],
        ];

        // add icon
        if (!is_null($this->icon)) {
            $tca['ctrl']['iconfile'] = $this->icon;
        }


        ArrayUtility::mergeRecursiveWithOverrule($tca, $this->config);

        return $tca;
    }
}