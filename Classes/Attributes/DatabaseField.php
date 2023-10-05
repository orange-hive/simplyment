<?php

namespace OrangeHive\Simplyment\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DatabaseField
{

    /**
     * @param string|null $type use database field types, e.g. varchar(255), int(11)
     * @param string|null $default
     * @param string $sql
     * @param bool $nullable
     */
    public function __construct(
        public ?string $type = null,
        public ?string $default = '',
        public string  $sql = '',
        public bool    $nullable = true
    )
    {
    }

    public function getFieldConfiguration(): array
    {
        return [
            'type' => $this->type,
            'sql' => $this->getSqlByType(),
        ];
    }

    protected function getSqlByType()
    {
        if (!empty($this->sql)) {
            return $this->sql;
        }

        $sql = $this->type;
        switch ($this->type) {
            case 'bool':
                $sql = 'tinyint(1)';
                break;
            case 'int':
                $sql = 'int(11)';
                break;
            case 'string':
                $sql = 'varchar(255)';
                break;
        }

        if (is_null($this->default) || $this->nullable) {
            $sql .= ' DEFAULT NULL';
        } else {
            $defaultValue = $this->default;
            if (empty($defaultValue) && ($this->type === 'int' || $this->type === 'bool')) {
                $defaultValue = 0;
            }

            $sql .= ' DEFAULT \'' . $defaultValue . '\'';
        }

        if(!$this->nullable) {
            $sql .= " NOT NULL";
        }

        return $sql;
    }

}