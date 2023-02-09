<?php

namespace OrangeHive\Simplyment\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PluginAction
{

    public function __construct(
        public ?string $pluginName,
        public         $noCache = false
    )
    {

    }

}