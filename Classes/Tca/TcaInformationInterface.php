<?php

namespace OrangeHive\Simplyment\Tca;

interface TcaInformationInterface
{
    public static function getTca(string $tableName): array;

}