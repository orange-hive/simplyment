<?php

namespace OrangeHive\Simplyment\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class DatabaseTable
{
    public function __construct(
        public ?string $tableName = null,
        public array   $indices = []
    )
    {
    }

}