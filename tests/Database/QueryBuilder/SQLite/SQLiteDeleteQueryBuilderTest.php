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
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

/**
 * Tests for SQLiteDeleteQueryBuilder.
 */
class SQLiteDeleteQueryBuilderTest extends TestCase
{
    private SQLiteDeleteQueryBuilder $sqlite_delete_query_builder;

    public function setUp(): void
    {
        $this->sqlite_delete_query_builder = new SqliteDatabaseWrapper($this->createStub(Sqlite::class))
            ->deleteQuery('users', UserMockEntity::class);
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

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQuery(UserMockEntity|array $mockEntities): void
    {
        if (count($mockEntities) > 1) {
            $this->sqlite_delete_query_builder->addEntities($mockEntities);
        } else {
            $this->sqlite_delete_query_builder->addEntity($mockEntities[0]);
        }

        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();

        $query = new ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery')
            ->invoke($this->sqlite_delete_query_builder, $p_key_name);

        $placeholder = str_repeat('?, ', count($mockEntities) - 1) . '?';
        $expected_query = 'delete from users where ' . $p_key_name . ' in (' . $placeholder . ')';

        $this->assertEquals($expected_query, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQueryWithValues(UserMockEntity|array $mockEntities): void
    {
        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();

        $primary_keys_values = [];

        if (is_array($mockEntities)) {
            $this->sqlite_delete_query_builder->addEntities($mockEntities);
            $primary_keys_values = array_map(fn (UserMockEntity $mockEntity): string => (string)$mockEntity->$p_key_name, $mockEntities);
        } else {
            $this->sqlite_delete_query_builder->addEntity($mockEntities);
            $primary_keys_values = (string) $mockEntities->$p_key_name;
        }

        $query = new ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery')
            ->invoke($this->sqlite_delete_query_builder, $p_key_name);

        foreach ($primary_keys_values as $primary_key_value) {
            $query = preg_replace('/\?/', $primary_key_value, $query, 1);
        }

        $placeholder = implode(', ', $primary_keys_values);
        $expected_query = 'delete from users where ' . $p_key_name . ' in (' . $placeholder . ')';

        $this->assertEquals($expected_query, $query);
    }

    public function testSettingEntityClassName(): void
    {
        $class_name = new ReflectionClass($this->sqlite_delete_query_builder)
            ->getProperty('entity_class')
            ->getValue($this->sqlite_delete_query_builder);

        $this->assertEquals($class_name, UserMockEntity::class);
    }
}
