<?php

namespace OrangeHive\Simplyment\Listener;

use OrangeHive\Simplyment\Registry\DatabaseModelRegistry;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DatabaseListener
{

    public function alterTableDefinitionStatements(AlterTableDefinitionStatementsEvent $event): AlterTableDefinitionStatementsEvent
    {
        $modelsSql = [];

        foreach (DatabaseModelRegistry::list() as $tableName => $data) {
            $fieldsSql = '';
            if (array_key_exists('fields', $data)) {
                $fieldsSql = $this->getFieldsSqlPart($data['fields']);
            }

            $indicesSql = '';
            if (array_key_exists('indices', $data)) {
                $indicesSql = $this->getIndicesSqlPart($data['indices']);
            }

            if (empty($fieldsSql) && empty($indicesSql)) {
                continue;
            }

            if (!empty($indicesSql)) {
                $indicesSql = ', ' . LF . $indicesSql;
            }


            $modelsSql[] = 'CREATE TABLE ' . $tableName
                . ' (' . LF
                . $fieldsSql . LF
                . $indicesSql . ');' . LF;
        }


        if (empty($modelsSql)) {
            return $event;
        }

        $before = $event->getSqlData();
        $event->setSqlData(array_merge($before, $modelsSql));

        return $event;
    }

    protected function getFieldsSqlPart(?array $fields): string
    {
        if (empty($fields)) {
            return '';
        }

        $sql = [];
        foreach ($fields as $fieldName => $fieldConfig) {
            $sql[] = $fieldName . ' ' . $fieldConfig['sql'];
        }

        return implode(',' . LF, $sql);
    }

    protected function getIndicesSqlPart(?array $indices): string
    {
        if (empty($indices)) {
            return '';
        }

        $sql = [];
        foreach ($indices as $indexName => $indexFields) {
            $sql[] = 'KEY `' . $indexName . '` (`' . implode('`, `', $indexFields) . '`)' . PHP_EOL;
        }

        return implode(',' . LF, $sql);
    }

}