<?php

namespace OrangeHive\Simplyment\Loader;

interface LoaderInterface
{

    public static function load(string $vendorName, string $extensionKey): void;

    public static function extLocalconf(string $vendorName, string $extensionName);
    public static function extTables(string $vendorName, string $extensionName);
}