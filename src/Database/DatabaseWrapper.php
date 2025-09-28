<?php

declare(strict_types=1);

namespace Sparkframe\Database;

use PDO;
use Sparkframe\Database\QueryBuilder\DeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\UpdateQueryBuilder;

/**
 * Interface DatabaseWrapper
 * A DatabaseWrapper provides all you need to interact with a database.
 * It provides a PDO instance and methods to create query builders.
 */
interface DatabaseWrapper
{
    public function getPDO(): PDO;
    //todo: implement various methods for building queries
    public function selectQuery(string $from_table_name, string $entity_class): SelectQueryBuilder;
    public function insertQuery(string $insert_into_table_name, string $entity_class): InsertQueryBuilder;
    public function updateQuery(string $update_table_name, string $entity_class): UpdateQueryBuilder;
    public function deleteQuery(string $delete_from_table_name, string $entity_class): DeleteQueryBuilder;
}
