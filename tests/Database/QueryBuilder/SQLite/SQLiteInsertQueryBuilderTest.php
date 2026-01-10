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
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteInsertQueryBuilder;

class SQLiteInsertQueryBuilderTest extends TestCase
{
    private SQLiteInsertQueryBuilder $sqlite_insert_query_builder;

    public function setUp(): void
    {
        $this->sqlite_insert_query_builder = new SqliteDatabaseWrapper($this->createStub(Sqlite::class))
            ->insertQuery('users', MockEntity::class);
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
        $reflectionMethod = new ReflectionMethod($this->sqlite_insert_query_builder, 'getQuery');

        $columns = MockEntity::getColumnNames();

        $query = $reflectionMethod->invoke($this->sqlite_insert_query_builder, $columns);

        $p_key_name = MockEntity::getPrimaryKeyColumnName();
        $expected_query = "insert into users ($p_key_name, name) values (:$p_key_name, :name)";
        $this->assertEquals($expected_query, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testGetQueryWithValues(array $mock_entities): void
    {
        $p_key_name = MockEntity::getPrimaryKeyColumnName();
        $base_query = new ReflectionMethod($this->sqlite_insert_query_builder, 'getQuery')
            ->invoke($this->sqlite_insert_query_builder, MockEntity::getColumnNames());
        $base_expected_query = "insert into users ($p_key_name, name) values (:$p_key_name, :name)";

        /** @var MockEntity $mock_entity  */
        foreach ($mock_entities as $mock_entity) {
            $query = $base_query;
            $expected_query = $base_expected_query;
            $value_array = $mock_entity->getValuesArray();

            foreach ($value_array as $value_type => $value) {
                $query = str_replace(":$value_type", (string) $value, $query);
                $expected_query = str_replace(":$value_type", (string) $value, $expected_query);
            }
            $this->assertEquals($expected_query, $query);
        }
    }

    public function testSettingEntityClassName(): void
    {
        $class_name = new ReflectionClass($this->sqlite_insert_query_builder)
            ->getProperty('entity_class')
            ->getValue($this->sqlite_insert_query_builder);

        $this->assertEquals($class_name, MockEntity::class);
    }
}
