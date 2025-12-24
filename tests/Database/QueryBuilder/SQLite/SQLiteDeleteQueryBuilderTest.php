<?php
declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use PDO;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteDeleteQueryBuilder;
use Sparkframe\Tests\Mocks\Entities\MockEntity;


class SQLiteDeleteQueryBuilderTest extends TestCase
{
    public function testDeleteQuery(): void
    {
        $reflectionMethod = new \ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery');
        $mock_entity = new MockEntity();
        $mock_entity->setId(1);

        $pdo = new PDO('sqlite::memory:');
        $deleteQueryBuilder = new SQLiteDeleteQueryBuilder($pdo, 'users', MockEntity::class);
        $deleteQueryBuilder->addEntity($mock_entity);

        $primaryKeyColumnName = MockEntity::getPrimaryKeyColumnName();
        $query = $reflectionMethod->invoke($deleteQueryBuilder, $primaryKeyColumnName);

        $expectedQuery = 'delete from users where ' . $primaryKeyColumnName . ' in (?)';
        $this->assertEquals($query, $expectedQuery);
    }
}
