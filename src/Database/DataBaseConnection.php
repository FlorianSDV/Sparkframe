<?php

namespace Sparkframe\Database;

use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;

interface DataBaseConnection
{
    //todo: implement various methods for building queries
    public function selectQuery(string $from_table_name): SelectQueryBuilder;
}
