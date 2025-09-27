<?php

namespace Sparkframe\Entity;

use Exception;

abstract class Entity
{
    protected const array COLUMN_DESCRIPTIONS = [];


    public function __construct(array $columns_and_values = [])
    {
        $column_names = static::getColumnNames();
        foreach ($columns_and_values as $column => $property) {
            if (in_array($column, $column_names)) {
                $this->$column = $property;
            }
        }
    }

    public static function getColumnNames(): array
    {
        return array_keys(static::getColumnDescriptions());
    }

    public static function getColumnDescriptions(): array
    {
        return static::COLUMN_DESCRIPTIONS;
    }

    /**
     * @throws Exception
     */
    public static function getPrimaryKeyColumnName(): string
    {
        foreach (static::getColumnDescriptions() as $key => $column) {
            if (is_array($column) && in_array('primary', $column)) {
                return $key;
            }
        }
        throw new Exception("No primary key set");
    }

    /**
     * @throws Exception
     */
    public function setId(string|int $id): void
    {
        $primary = $this->getPrimaryKeyColumnName();
        $this->$primary = $id;
    }

    /**
     * @return array
     */
    public function getValuesArray(): array
    {
        $values = [];
        foreach (static::getColumnNames() as $column) {
            $value = null;
            if (
                property_exists($this, $column) &&
                isset($this->{$column})
            ) {
                $value = $this->$column;
            }
            $values[$column] = $value;
        }
        return $values;
    }
}