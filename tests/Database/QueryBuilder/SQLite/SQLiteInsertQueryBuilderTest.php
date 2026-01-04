<?php
declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Sqlite;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\SqliteDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\MockEntity;


class SQLiteInsertQueryBuilderTest extends TestCase
{
    private function createSqliteDatabaseWrapper(): SqliteDatabaseWrapper
    {
        $sqlite_mock = $this->createMock(SQLite::class);
        $sqlite_database_wrapper = new SqliteDatabaseWrapper($sqlite_mock);
        return $sqlite_database_wrapper;
    }

    public function testInsertQuery()
    {
        $sqlite_database_wrapper = $this->createSqliteDatabaseWrapper();

        $insertQueryBuilder = $sqlite_database_wrapper->insertQuery('users', MockEntity::class);
        $reflectionMethod = new \ReflectionMethod($insertQueryBuilder, 'getQuery');

        $columns = MockEntity::getColumnNames();

        $query = $reflectionMethod->invoke($insertQueryBuilder, $columns);

        $this->assertEquals("insert into users (id, name) values (:id, :name)", $query);
    }
}
