<?php

namespace OrangeHive\Simplyment\Enumeration;

use ReflectionClass;

class TcaFieldTypeEnum
{

    const CATEGORY = 'category';

    const CHECK = 'check';

    const FILE = 'file';

    // custom type based on type="file"
    const FILE_IMAGE = 'file_image';

    const FILE_MEDIA = 'file_media';

    const FLEX = 'flex';

    const GROUP = 'group';

    const IMAGE_MANIPULATION = 'imageManipulation';

    const INLINE = 'inline';

    const INPUT = 'input';

    const SELECT = 'select';

    const TEXT = 'text';

    public static function getAll() {
        $refClass = new ReflectionClass(__CLASS__);
        return $refClass->getConstants();
    }

    public static function getAllValues() {
        return array_values(self::getAll());
    }

}