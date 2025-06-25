<?php

namespace Sparkframe\Database;

use Pdo\Sqlite;
use PDOStatement;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteSelectQueryBuilder;

readonly class SqliteDatabaseConnection implements DataBaseConnection
{
    public function __construct(protected Sqlite $pdo)
    {

    }

    public function query(string $query_string): false|PDOStatement
    {
        return $this->pdo->query($query_string);
    }

    public function selectQuery(string $from_table_name): SQLiteSelectQueryBuilder
    {
        return new SQLiteSelectQueryBuilder($this, $from_table_name);
    }
}