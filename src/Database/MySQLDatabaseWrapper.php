<?php
declare(strict_types=1);

namespace Sparkframe\Database;

use PDO;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLSelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLInsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLUpdateQueryBuilder;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLDeleteQueryBuilder;

readonly class MySQLDatabaseWrapper implements DatabaseWrapperInterface
{
    public function __construct(protected PDO $PDO) {}

    public function getPDO(): PDO
    {
        return $this->PDO;
    }

    public function selectQuery(string $from_table_name, string $entity_class): MySQLSelectQueryBuilder
    {
        return new MySQLSelectQueryBuilder($this->PDO, $from_table_name, $entity_class);
    }

    public function insertQuery(string $insert_into_table_name, string $entity_class): MySQLInsertQueryBuilder
    {
        return new MySQLInsertQueryBuilder($this->PDO, $insert_into_table_name, $entity_class);
    }

    public function updateQuery(string $update_table_name, string $entity_class): MySQLUpdateQueryBuilder
    {
        return new MySQLUpdateQueryBuilder($this->PDO, $update_table_name, $entity_class);
    }

    public function deleteQuery(string $delete_from_table_name, string $entity_class): MySQLDeleteQueryBuilder
    {
        return new MySQLDeleteQueryBuilder($this->PDO, $delete_from_table_name, $entity_class);
    }
}