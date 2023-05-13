<?php

namespace OrangeHive\Simplyment\Attributes;

use OrangeHive\Simplyment\Enumeration\TcaFieldTypeEnum;
use OrangeHive\Simplyment\Utility\ClassNameUtility;
use OrangeHive\Simplyment\Utility\ModelTcaUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class TcaField
{

    public function __construct(
        public ?string  $label = null,
        public string  $type = 'input',
        public ?bool   $exclude = null,
        public ?string $targetClass = null,
        public array   $config = []
    )
    {
    }

    public function getTca(string $fqcn = null, string $propertyName = null)
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);

        $availableTypes = TcaFieldTypeEnum::getAllValues();

        if (!in_array($this->type, $availableTypes)) {
            $this->type = 'input';
        }


        $config = [
            'type' => $this->type,
        ];


        if ($this->type === TcaFieldTypeEnum::INLINE) {
            $foreignFieldName = '';

            // get foreign field from targetClass, by return type className of field
            if (!is_null($this->targetClass)) {
                $classRef = new \ReflectionClass($this->targetClass);

                foreach ($classRef->getProperties() as $property) {
                    if (!is_null($property->getType()) && $property->getType()->getName() === $fqcn) {
                        $foreignFieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($property->getName());
                    }
                }

                $inlineConfig = [
                    'type' => 'inline',
                    'foreign_table' => ClassNameUtility::getTableNameByFqcn($this->targetClass),
                    'foreign_field' => $foreignFieldName,
                    'foreign_sortby' => 'sorting',
                ];
            }


            if (empty($foreignFieldName)) {
                $inlineConfig = [
                    'type' => 'user',
                    'renderType' => 'simplymentPlaceholderElement',
                    'parameters' => [
                        'message' => 'Could not determine "foreign_field".<br />Please define TCA for this field on your own or provide "targetClass" argument in TcaField PHP attribute.',
                        'code' => <<<TEXT
[
    'type' => 'inline',
    'foreign_table' => 'INSERT_FOREIGN_TABLE',
    'foreign_field' => 'INSERT_FOREIGN_FIELD',
    'foreign_sortby' => 'sorting',
]
TEXT,
                    ],
                ];
            }

            ArrayUtility::mergeRecursiveWithOverrule($config, $inlineConfig);
        } else if ($this->type === TcaFieldTypeEnum::FILE) {
            if ($typo3Version->getMajorVersion() < 12) {
                $fileConfig = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'files',
                    [
                        'foreign_match_fields' => [
                            'fieldname' => GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName),
                        ],
                    ]
                );

                ArrayUtility::mergeRecursiveWithOverrule($config, $fileConfig);
            }
        } else if ($this->type === TcaFieldTypeEnum::FILE_IMAGE) {
            if ($typo3Version->getMajorVersion() < 12) {
                $fileConfig = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'image',
                    [
                        'foreign_match_fields' => [
                            'fieldname' => GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName),
                        ],
                    ],
                    $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                );
            } else {
                $fileConfig = [
                    'config' => TcaFieldTypeEnum::FILE,
                    'allowed' => 'common-image-types',
                ];
            }

            ArrayUtility::mergeRecursiveWithOverrule($config, $fileConfig);
        } else if ($this->type === TcaFieldTypeEnum::FILE_MEDIA) {
            if ($typo3Version->getMajorVersion() < 12) {
                $fileConfig = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'media',
                    [
                        'foreign_match_fields' => [
                            'fieldname' => GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName),
                        ],
                    ],
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext']
                );
            } else {
                $fileConfig = [
                    'config' => TcaFieldTypeEnum::FILE,
                    'allowed' => 'common-media-types',
                ];
            }

            ArrayUtility::mergeRecursiveWithOverrule($config, $fileConfig);
        } else if ($this->type === TcaFieldTypeEnum::SELECT && !is_null($this->targetClass)) {
            $classRef = new \ReflectionClass($this->targetClass);

            $selectConfig = [
                'type' => TcaFieldTypeEnum::SELECT,
                'foreign_table' => ClassNameUtility::getTableNameByFqcn($this->targetClass),
                'foreign_sortby' => 'sorting',
                'renderType' => 'selectSingle',
            ];

            ArrayUtility::mergeRecursiveWithOverrule($config, $selectConfig);
        }


        ArrayUtility::mergeRecursiveWithOverrule($config, $this->config);

        $fieldTca = [
            'config' => $config,
        ];

        if (!is_null($this->exclude)) {
            $fieldTca['exclude'] = $this->exclude;
        }
        if (!is_null($this->label)) {
            $fieldTca['label'] = $this->label;
        }

        return $fieldTca;
    }

}