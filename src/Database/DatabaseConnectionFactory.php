<?php

namespace Sparkframe\Database;

use Exception;
use Pdo;
class DatabaseConnectionFactory
{
    /**
     * @throws Exception
     */
    public static function createDatabaseConnection(BaseDatabaseInfo $databaseInfo): DataBaseConnection
    {
        $pdo = Pdo::connect(
            $databaseInfo->getDatabaseUrl(),
            $databaseInfo->getUser(),
            $databaseInfo->getPassword(),
        );
        switch ($pdo::class) {
            case Pdo\Sqlite::class:
                return new SqliteDatabaseConnection($pdo);
            default:
                throw new Exception('Db type not allowed');
        }
    }
}