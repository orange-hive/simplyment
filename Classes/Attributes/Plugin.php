<?php

namespace OrangeHive\Simplyment\Attributes;

use OrangeHive\Simplyment\Registry\PluginRegistry;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Plugin
{

    public function __construct(
        public string  $name,
        public string  $description = '',
        public ?string $iconPath = null,
        public ?string $flexFormPath = null,
        public bool    $hideContentElement = false
    )
    {
        if (empty($this->description)) {
            $this->description = $this->name;
        }
    }

}