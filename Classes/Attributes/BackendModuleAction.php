<?php

namespace OrangeHive\Simplyment\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class BackendModuleAction
{

    public function __construct(
        public ?string $pluginName,
        public bool    $noCache = false
    )
    {

    }

}