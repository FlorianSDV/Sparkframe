<?php

namespace Sparkframe\Database;

use PDO;
use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;

interface DataBaseConnection
{
    public function getPdo(): PDO;
    //todo: implement various methods for building queries
    public function selectQuery(string $from_table_name): SelectQueryBuilder;
    public function insertQuery(string $insert_into_table_name, string $entity_class): InsertQueryBuilder;
}
