<?php

namespace OrangeHive\Simplyment\Service;

use OrangeHive\Simplyment\Attributes\DatabaseField;
use OrangeHive\Simplyment\Attributes\Tca;
use OrangeHive\Simplyment\Attributes\TcaField;
use ReflectionClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class TcaService
{

    protected ReflectionClass $classRef;

    protected string $fqcn;

    public function __construct(string $fqcn)
    {
        $this->fqcn = $fqcn;
        $this->classRef = new ReflectionClass($fqcn);
    }

    public function getModelTca(): array
    {
        $modelTca = [];
        foreach ($this->classRef->getAttributes(Tca::class) as $attribute) {
            /** @var Tca $tcaInstance */
            $tcaInstance = $attribute->newInstance();

            $modelTca = $tcaInstance->getTca();
        }

        return $modelTca;
    }

    public function getModelColumns(bool $includeNonTcaFieldProperties = false): array
    {
        $modelColumns = [];

        foreach ($this->classRef->getProperties() as $property) {
            $property = $this->classRef->getProperty($property->getName());
            $propertyNameLowerCaseUnderscored = GeneralUtility::camelCaseToLowerCaseUnderscored($property->getName());

            // used for ContentElements
            if ($includeNonTcaFieldProperties) {
                $modelColumns[$propertyNameLowerCaseUnderscored] = null;
            }

            foreach ($property->getAttributes(TcaField::class) as $field) {
                /** @var TcaField $fieldInstance */
                $fieldInstance = $field->newInstance();

                $tca = $fieldInstance->getTca(
                    fqcn: $this->fqcn,
                    propertyName: $field->getName(),
                );

                $tca['_hasDatabaseFieldAttribute'] = count($property->getAttributes(DatabaseField::class)) > 0;

                /** @var $field TcaField */
                $modelColumns[$propertyNameLowerCaseUnderscored] = $tca;
            }
        }

        return $modelColumns;
    }

}