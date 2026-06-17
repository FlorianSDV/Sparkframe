<?php

declare(strict_types=1);

namespace Sparkframe\Database;

use Pdo\Sqlite;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteDeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteInsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteSelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteUpdateQueryBuilder;
use Sparkframe\Entity\Entity;

/**
 * A wrapper for a Sqlite database. Provides a PDO instance and various querybuilders.
 */
readonly class SqliteDatabaseWrapper implements DatabaseWrapperInterface
{
    public function __construct(protected Sqlite $pdo)
    {
    }

    public function getPDO(): Sqlite
    {
        return $this->pdo;
    }

    /** @param class-string<Entity> $entity_class */
    public function selectQuery(string $from_table_name, string $entity_class): SQLiteSelectQueryBuilder
    {
        return new SQLiteSelectQueryBuilder($this->pdo, $from_table_name, $entity_class);
    }

    /** @param class-string<Entity> $entity_class */
    public function insertQuery(string $insert_into_table_name, string $entity_class): SQLiteInsertQueryBuilder
    {
        return new SQLiteInsertQueryBuilder($this->pdo, $insert_into_table_name, $entity_class);
    }

    /** @param class-string<Entity> $entity_class */
    public function updateQuery(string $update_table_name, string $entity_class): SQLiteUpdateQueryBuilder
    {
        return new SQLiteUpdateQueryBuilder($this->pdo, $update_table_name, $entity_class);
    }

    /** @param class-string<Entity> $entity_class */
    public function deleteQuery(string $delete_from_table_name, string $entity_class): SQLiteDeleteQueryBuilder
    {
        return new SQLiteDeleteQueryBuilder($this->pdo, $delete_from_table_name, $entity_class);
    }
}
