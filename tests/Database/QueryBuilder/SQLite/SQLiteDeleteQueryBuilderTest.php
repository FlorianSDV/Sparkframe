<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Sqlite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteDeleteQueryBuilder;
use Sparkframe\Database\SqliteDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\MockEntity;

class SQLiteDeleteQueryBuilderTest extends TestCase
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

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQuery(array $mock_entities): void
    {
        $deleteQueryBuilder = $this->sqlite_database_wrapper
            ->deleteQuery('users', MockEntity::class);
        $reflectionMethod = new ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery');
        
        foreach ($mock_entities as $mock_entity) {
            $deleteQueryBuilder->addEntity($mock_entity);
        }
        
        $primaryKeyColumnName = MockEntity::getPrimaryKeyColumnName();
        $query = $reflectionMethod->invoke($deleteQueryBuilder, $primaryKeyColumnName);

        $placeholder = str_repeat('?, ', count($mock_entities) - 1) . '?';
        $expectedQuery = 'delete from users where ' . $primaryKeyColumnName . ' in (' . $placeholder . ')';
        
        $this->assertEquals($query, $expectedQuery);
    }

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQueryWithValues(array $mock_entities): void
    {
        $primaryKeyColumnName = MockEntity::getPrimaryKeyColumnName();

        $deleteQueryBuilder = $this->sqlite_database_wrapper
            ->deleteQuery('users', MockEntity::class);
    
        $primaryKeysValues = [];
        foreach ($mock_entities as $mock_entity) {
            $deleteQueryBuilder->addEntity($mock_entity);
            $primaryKeysValues[] = (string)$mock_entity->$primaryKeyColumnName;
        }
        
        $query = new ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery')
            ->invoke($deleteQueryBuilder, $primaryKeyColumnName);

        foreach ($primaryKeysValues as $primaryKeyValue) {
            $query = preg_replace('/\?/', $primaryKeyValue, $query, 1);
        }

        $placeholder = implode(', ', $primaryKeysValues);
        $expectedQuery = 'delete from users where ' . $primaryKeyColumnName . ' in (' . $placeholder . ')';

        $this->assertEquals($expectedQuery, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testSettingEntityClassName(array $mock_entities): void
    {
        $deleteQueryBuilder = $this->sqlite_database_wrapper
            ->deleteQuery('users', MockEntity::class);

        foreach ($mock_entities as $mock_entity) {
            $deleteQueryBuilder->addEntity($mock_entity);
        }

        $class_name = new ReflectionClass($deleteQueryBuilder)
            ->getProperty('entity_class')
            ->getValue($deleteQueryBuilder);

        $this->assertEquals($class_name, MockEntity::class);
    }
}
