<?php

namespace OrangeHive\Simplyment\Attributes;

use OrangeHive\Simplyment\Registry\PluginRegistry;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BackendModule
{

    public function __construct(
        public string  $name,
        public string  $mainModuleName = 'web',
        public string  $subModuleName = '',
        public string  $position = 'top',
        public string  $access = 'admin',
        public string  $navigationComponentId = 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        public bool    $inheritNavigationComponentFromMainModule = false,
        public ?string $iconIdentifier = null
    )
    {
        if (empty($this->description)) {
            $this->description = $this->name;
        }

        //BackendModuleRegistry::addModuleInformation($name, $description, $iconPath);
    }

}