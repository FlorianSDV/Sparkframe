<?php

declare(strict_types=1);

namespace Sparkframe\Database;

use Pdo\Mysql;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLDeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLInsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLSelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLUpdateQueryBuilder;
use Sparkframe\Entity\Entity;

/**
 * A wrapper for a MySQL database. Provides a PDO instance and various querybuilders.
 */
readonly class MySQLDatabaseWrapper implements DatabaseWrapperInterface
{
    public function __construct(protected Mysql $pdo)
    {
    }

    public function getPDO(): Mysql
    {
        return $this->pdo;
    }

    /** @param class-string<Entity> $entity_class */
    public function selectQuery(string $from_table_name, string $entity_class): MySQLSelectQueryBuilder
    {
        return new MySQLSelectQueryBuilder($this->pdo, $from_table_name, $entity_class);
    }

    /** @param class-string<Entity> $entity_class */
    public function insertQuery(string $insert_into_table_name, string $entity_class): MySQLInsertQueryBuilder
    {
        return new MySQLInsertQueryBuilder($this->pdo, $insert_into_table_name, $entity_class);
    }

    /** @param class-string<Entity> $entity_class */
    public function updateQuery(string $update_table_name, string $entity_class): MySQLUpdateQueryBuilder
    {
        return new MySQLUpdateQueryBuilder($this->pdo, $update_table_name, $entity_class);
    }

    /** @param class-string<Entity> $entity_class */
    public function deleteQuery(string $delete_from_table_name, string $entity_class): MySQLDeleteQueryBuilder
    {
        return new MySQLDeleteQueryBuilder($this->pdo, $delete_from_table_name, $entity_class);
    }
}
