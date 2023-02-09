<?php

namespace OrangeHive\Simplyment\Service;

class DatabaseModelService
{

    protected function generateSql(string $tableName, array $fields)
    {
        if (empty($fields)) {
            return '';
        }

        return 'CREATE TABLE ' . $tableName . ' (' . implode(',', $fields) . ');' . LF;
    }

}