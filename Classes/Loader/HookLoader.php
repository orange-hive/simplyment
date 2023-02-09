<?php

namespace OrangeHive\Simplyment\Loader;

use OrangeHive\Simplyment\Attributes\Hook;
use OrangeHive\Simplyment\Cache\Typo3Cache;
use OrangeHive\Simplyment\Registry\HookRegistry;
use ReflectionClass;
use ReflectionMethod;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class HookLoader implements LoaderInterface
{

    public static function load(string $vendorName, string $extensionKey): void
    {
        $extPath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Classes/Hook');

        $files = glob($extPath . '/*.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');

            $fqcn = $vendorName . '\\' . ucfirst(GeneralUtility::underscoredToUpperCamelCase($extensionKey)) . '\\Hook\\' . $className;

            $classRef = new ReflectionClass($fqcn);
            foreach ($classRef->getAttributes(Hook::class) as $attribute) {
                /** @var Hook $instance */
                $instance = $attribute->newInstance();

                HookRegistry::addHook(
                    hookIdentifier: $instance->identifier,
                    fqcn: $fqcn,
                    key: $instance->key
                );
            }

            foreach ($classRef->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

                $methodRef = new ReflectionMethod($method->class, $method->name);
                foreach ($methodRef->getAttributes(Hook::class) as $attribute) {
                    /** @var Hook $attributeInstance */
                    $attributeInstance = $attribute->newInstance();

                    HookRegistry::addHook(
                        hookIdentifier: $attributeInstance->identifier,
                        fqcn: $fqcn,
                        methodName: $method->name,
                        key: $attributeInstance->key
                    );
                }
            }
        }
    }

    protected static function register()
    {
        foreach (HookRegistry::list() as $hook) {
            $hookSegments = explode('/', $hook['hookIdentifier']);
            $firstSegment = array_shift($hookSegments);

            // add key
            $key = $hook['key'];
            if (is_null($key)) {
                $key = time();
            }
            $hookSegments[] = $key;

            $path = join('/', $hookSegments);

            $value = $hook['fqcn'];
            if (!is_null($hook['methodName'])) {
                $value .= '->' . $hook['methodName'];
            }

            $GLOBALS[$firstSegment] = ArrayUtility::setValueByPath(
                array: $GLOBALS[$firstSegment],
                path: $path,
                value: $value
            );
        }
    }

    public static function extLocalconf(string $vendorName, string $extensionName)
    {
        self::load($vendorName, $extensionName);
        self::register();
    }

    public static function extTables(string $vendorName, string $extensionName)
    {
        // nothing to do
    }
}