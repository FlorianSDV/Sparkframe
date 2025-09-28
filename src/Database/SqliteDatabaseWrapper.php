<?php

declare(strict_types=1);

namespace Sparkframe\Database;

use Pdo\Sqlite;
use Sparkframe\Database\QueryBuilder\DeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteSelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteInsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteUpdateQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteDeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\UpdateQueryBuilder;

readonly class SqliteDatabaseWrapper implements DatabaseWrapper
{
    public function __construct(protected Sqlite $PDO) {}

    public function getPDO(): Sqlite
    {
        return $this->PDO;
    }

    public function selectQuery(string $from_table_name, string $entity_class): SQLiteSelectQueryBuilder
    {
        return new SQLiteSelectQueryBuilder($this->PDO, $from_table_name, $entity_class);
    }

    public function insertQuery(string $insert_into_table_name, string $entity_class): SQLiteInsertQueryBuilder
    {
        return new SQLiteInsertQueryBuilder($this->PDO, $insert_into_table_name, $entity_class);
    }

    public function updateQuery(string $update_table_name, string $entity_class): UpdateQueryBuilder
    {
        return new SQLiteUpdateQueryBuilder($this->PDO, $update_table_name, $entity_class);
    }

    public function deleteQuery(string $delete_from_table_name, string $entity_class): DeleteQueryBuilder
    {
        return new SQLiteDeleteQueryBuilder($this->PDO, $delete_from_table_name, $entity_class);
    }
}
