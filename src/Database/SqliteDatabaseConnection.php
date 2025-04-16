<?php

namespace Sparkframe\Database;



use Pdo\Sqlite;

readonly class SqliteDatabaseConnection implements DataBaseConnection
{
    public function __construct(private Sqlite $pdo)
    {

    }

    public function query(string $query_string)
    {
        return $this->pdo->query($query_string);
    }
}