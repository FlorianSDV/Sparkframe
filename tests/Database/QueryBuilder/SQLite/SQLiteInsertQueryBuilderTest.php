<?php
declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use PDO;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteInsertQueryBuilder;
use Sparkframe\Tests\Mocks\Entities\MockEntity;


class SQLiteInsertQueryBuilderTest extends TestCase
{
    public function testInsertQuery()
    {
        $newEntity = new MockEntity();
        $newEntity->setId(1);
        $newEntity->name = 'John Doe';

        $pdo = new PDO('sqlite::memory:');
        $insertQueryBuilder = new SQLiteInsertQueryBuilder($pdo, 'users', MockEntity::class);
        $reflectionMethod = new \ReflectionMethod($insertQueryBuilder, 'getQuery');


        $columns = MockEntity::getColumnNames();

        $query = $reflectionMethod->invoke($insertQueryBuilder, $columns);

        $this->assertEquals("insert into users (id, name) values (:id, :name)", $query);
    }
}
