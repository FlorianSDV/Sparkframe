<?php

namespace Sparkframe\Database;

use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;

interface DataBaseConnection
{
    //todo: implement various methods for building queries
    public function selectQuery(string $from_table_name): SelectQueryBuilder;
    public function insertQuery(string $insert_into_table_name): InsertQueryBuilder;
}
