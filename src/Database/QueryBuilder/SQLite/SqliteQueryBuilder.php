<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Sparkframe\Database\QueryBuilder\QueryBuilder;

abstract class SqliteQueryBuilder extends QueryBuilder
{
    // TODO: refactor
    public function where(string ...$column_names): SqliteQueryBuilder
    {
        foreach ($column_names as $column_name) {
            $this->where_columns[] = $column_name;
        }

        return $this;
    }

    public function getFromPart(): string
    {
        return "from $this->from_table_name ";
    }
    public function getWherePart(): string
    {
        $where_part = '';
        foreach ($this->where_columns as $where_column) {
            // todo: dit pak ik op bij de single select
            $where_part .= ' ';
        }
        return '';
    }
}