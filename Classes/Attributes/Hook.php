<?php

namespace OrangeHive\Simplyment\Attributes;


#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Hook
{

    public function __construct(
        public string $identifier,
        public ?string $key = null
    )
    {

    }

}