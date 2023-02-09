<?php

namespace OrangeHive\Simplyment\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ContentElement
{


    public function __construct(
        public string $name,
        public ?string $iconPath = null,
        public ?string $position = null,
        public bool $hideContentElement = false
    )
    {
    }
}