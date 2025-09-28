<?php

declare(strict_types=1);

namespace Sparkframe\Database;

use Exception;
use Pdo;
class DatabaseWrapperFactory
{
    /**
     * @throws Exception
     */
    public static function createDatabaseWrapper(DatabaseInfo $databaseInfo): DatabaseWrapperInterface
    {
        $pdo = Pdo::connect(
            $databaseInfo->getDatabaseUrl(),
            $databaseInfo->getUser(),
            $databaseInfo->getPassword(),
        );
        switch ($pdo::class) {
            case Pdo\Sqlite::class:
                return new SqliteDatabaseWrapper($pdo);
            case Pdo\Mysql::class:
                return new MySQLDatabaseWrapper($pdo);
            default:
                throw new Exception('Db type not allowed');
        }
    }
}