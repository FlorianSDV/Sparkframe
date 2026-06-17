<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Sqlite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteInsertQueryBuilder;
use Sparkframe\Database\SqliteDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

/**
 * Tests for SQLiteInsertQueryBuilder.
 */
class SQLiteInsertQueryBuilderTest extends TestCase
{
    private SQLiteInsertQueryBuilder $sqlite_insert_query_builder;

    public function setUp(): void
    {
        $this->sqlite_insert_query_builder = new SqliteDatabaseWrapper($this->createStub(Sqlite::class))
            ->insertQuery('users', UserMockEntity::class);
    }

    public static function mockEntityProvider(): array
    {
        $mockEntity1 = new UserMockEntity();
        $mockEntity1->setId(1);
        $mockEntity1->name = 'John Doe';
        $mockEntity1->email_address = 'john.doe@example.com';
        $mockEntity1->age = 30;
        $mockEntity1->phone_number = '1234567890';

        $mockEntity2 = new UserMockEntity();
        $mockEntity2->setId(2);
        $mockEntity2->name = 'Jane Doe';
        $mockEntity2->email_address = 'jane.doe@example.com';
        $mockEntity2->age = 25;
        $mockEntity2->phone_number = '0987654321';

        return [
            'single_entity' => [[$mockEntity1]],
            'multiple_entities' => [[$mockEntity1, $mockEntity2]]
        ];
    }

    public function testInsertQuery(): void
    {
        $columns = UserMockEntity::getColumnNames();

        $query = new ReflectionMethod(SQLiteInsertQueryBuilder::class, 'getQuery')
            ->invoke($this->sqlite_insert_query_builder, $columns);

        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();
        $expected_query = "insert into users ($p_key_name, name, email_address, age, phone_number) values (:$p_key_name, :name, :email_address, :age, :phone_number)";
        $this->assertEquals($expected_query, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testGetQueryWithValues(array $mockEntities): void
    {
        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();
        $base_query = new ReflectionMethod(SQLiteInsertQueryBuilder::class, 'getQuery')
            ->invoke($this->sqlite_insert_query_builder, UserMockEntity::getColumnNames());
        $base_expected_query = "insert into users ($p_key_name, name, email_address, age, phone_number) values (:$p_key_name, :name, :email_address, :age, :phone_number)";

        /** @var UserMockEntity $mockEntity  */
        foreach ($mockEntities as $mockEntity) {
            $query = $base_query;
            $expected_query = $base_expected_query;
            $value_array = $mockEntity->getValuesArray();

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

        $this->assertEquals($class_name, UserMockEntity::class);
    }
}
