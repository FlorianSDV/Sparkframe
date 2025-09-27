<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use Sparkframe\Database\QueryBuilder\QueryBuilder;

abstract class SQLiteQueryBuilder extends QueryBuilder
{
    public function getTargetTable(): string
    {
        return $this->target_table_name;
    }
}
