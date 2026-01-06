<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Sqlite;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\SqliteDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\MockEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;

class SQLiteInsertQueryBuilderTest extends TestCase
{
    private SqliteDatabaseWrapper $sqlite_database_wrapper;

    public function setUp(): void
    {
        $this->sqlite_database_wrapper = new SqliteDatabaseWrapper($this->createStub(Sqlite::class));
    }

    public static function mockEntityProvider(): array
    {
        $mock_entity_1 = new MockEntity();
        $mock_entity_1->setId(1);
        $mock_entity_1->name = 'John Doe';

        $mock_entity_2 = new MockEntity();
        $mock_entity_2->setId(2);
        $mock_entity_2->name = 'Jane Doe';

        return [
            'single_entity' => [[$mock_entity_1]],
            'multiple_entities' => [[$mock_entity_1, $mock_entity_2]]
        ];
    }

    public function testInsertQuery()
    {
        $insertQueryBuilder = $this->sqlite_database_wrapper
            ->insertQuery('users', MockEntity::class);
        $reflectionMethod = new ReflectionMethod($insertQueryBuilder, 'getQuery');

        $columns = MockEntity::getColumnNames();

        $query = $reflectionMethod->invoke($insertQueryBuilder, $columns);

        $this->assertEquals("insert into users (id, name) values (:id, :name)", $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testGetQueryWithValues(array $mock_entities): void
    {
        $insertQueryBuilder = $this->sqlite_database_wrapper
            ->insertQuery('users', MockEntity::class);
        $reflectionMethod = new ReflectionMethod($insertQueryBuilder, 'getQuery');

        $base_query = $reflectionMethod->invoke($insertQueryBuilder, MockEntity::getColumnNames());
        foreach ($mock_entities as $mock_entity) {
            $curr_query = $base_query;
            $values = $mock_entity->getValuesArray();
            $comparison_query = "insert into users (id, name) values (" . $values['id'] . ", " . $values['name'] . ")";
            foreach ($values as $key => $value) {
                $curr_query = str_replace(":$key", (string)$value, $curr_query);
            }
            $this->assertEquals($curr_query, $comparison_query);
        }
    }

    #[DataProvider('mockEntityProvider')]
    public function testSettingEntityClassName(array $mock_entities): void
    {
        $insertQueryBuilder = $this->sqlite_database_wrapper
            ->insertQuery('users', MockEntity::class);

        foreach ($mock_entities as $mock_entity) {
            $insertQueryBuilder->addEntity($mock_entity);
        }

        $class_name = new ReflectionClass($insertQueryBuilder)
            ->getProperty('entity_class')
            ->getValue($insertQueryBuilder);

        $this->assertEquals($class_name, MockEntity::class);
    }
}
