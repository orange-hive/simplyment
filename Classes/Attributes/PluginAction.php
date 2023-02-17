<?php

namespace OrangeHive\Simplyment\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PluginAction
{

    public function __construct(
        public ?string $pluginName,
        public         $noCache = false
    )
    {

    }

}