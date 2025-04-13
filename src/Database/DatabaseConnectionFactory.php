<?php

namespace Sparkframe\Database;

use Exception;
use SQLite3;

class DatabaseConnectionFactory
{
    /**
     * @throws Exception
     */
    public static function createDatabaseConnection(): DataBaseConnection
    {
        switch (getenv('DB_TYPE')) {
            case 'sqlite':

                $env = getenv('DB_FILENAME');
                $sqlite = new SQLite3($env);
                return new Sqlite3DatabaseConnection($sqlite);
            default:
                throw new Exception('Db type not allowed');
        }
    }
}