<?php

namespace Sparkframe\Database;

use Pdo\Sqlite;
use PDOStatement;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteSelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteInsertQueryBuilder;

readonly class SqliteDatabaseWrapper implements DatabaseWrapper
{
    public function __construct(protected Sqlite $pdo)
    {

    }

    public function getPdo(): Sqlite
    {
        return $this->pdo;
    }

    public function selectQuery(string $from_table_name): SQLiteSelectQueryBuilder
    {
        return new SQLiteSelectQueryBuilder($this->pdo, $from_table_name);
    }

    public function insertQuery(string $insert_into_table_name, string $entity_class): SQLiteInsertQueryBuilder
    {
        return new SQLiteInsertQueryBuilder($this->pdo, $insert_into_table_name, $entity_class);
    }
}