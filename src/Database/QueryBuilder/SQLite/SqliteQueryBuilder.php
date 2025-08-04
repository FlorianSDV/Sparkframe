<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use Sparkframe\Database\QueryBuilder\QueryBuilder;

abstract class SqliteQueryBuilder extends QueryBuilder
{
    public function getFromPart(): string
    {
        return "from $this->from_table_name ";
    }
}
