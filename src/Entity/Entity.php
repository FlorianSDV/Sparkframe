<?php

namespace Sparkframe\Entity;

abstract class Entity
{
    protected const array COLUMN_DESCRIPTIONS = [];

    public static function getColumnNames(): array
    {
        return array_keys(static::getColumnDescriptions());
    }

    public static function getColumnDescriptions(): array
    {
        return self::COLUMN_DESCRIPTIONS;
    }

    public function setId(string|int $id): void
    {
        foreach ($this::getColumnDescriptions() as $key => $column) {
            if (is_array($column) && in_array('primary', $column)) {
                $this->$key = $id;
                break;
            }
        }
    }
}