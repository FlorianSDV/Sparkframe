<?php

namespace Sparkframe\Database;

use PDO;
use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;

/**
 * Interface DatabaseWrapper
 * A DatabaseWrapper provides all you need to interact with a database.
 * It provides a PDO instance and methods to create query builders.
 */
interface DatabaseWrapper
{
    public function getPdo(): PDO;
    //todo: implement various methods for building queries
    public function selectQuery(string $from_table_name): SelectQueryBuilder;
    public function insertQuery(string $insert_into_table_name, string $entity_class): InsertQueryBuilder;
}
