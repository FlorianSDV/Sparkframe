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
        $mock_entity_1 = new UserMockEntity();
        $mock_entity_1->setId(1);
        $mock_entity_1->name = 'John Doe';
        $mock_entity_1->email_address = 'john.doe@example.com';
        $mock_entity_1->age = 30;
        $mock_entity_1->phone_number = '1234567890';

        $mock_entity_2 = new UserMockEntity();
        $mock_entity_2->setId(2);
        $mock_entity_2->name = 'Jane Doe';
        $mock_entity_2->email_address = 'jane.doe@example.com';
        $mock_entity_2->age = 25;
        $mock_entity_2->phone_number = '0987654321';

        return [
            'single_entity' => [[$mock_entity_1]],
            'multiple_entities' => [[$mock_entity_1, $mock_entity_2]]
        ];
    }

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQuery(UserMockEntity|array $mock_entities): void
    {
        if (count($mock_entities) > 1) {
            $this->sqlite_delete_query_builder->addEntities($mock_entities);
        } else {
            $this->sqlite_delete_query_builder->addEntity($mock_entities[0]);
        }

        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();

        $query = new ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery')
            ->invoke($this->sqlite_delete_query_builder, $p_key_name);

        $placeholder = str_repeat('?, ', count($mock_entities) - 1) . '?';
        $expected_query = 'delete from users where ' . $p_key_name . ' in (' . $placeholder . ')';

        $this->assertEquals($expected_query, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQueryWithValues(UserMockEntity|array $mock_entities): void
    {
        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();

        $primaryKeysValues = [];

        if (is_array($mock_entities)) {
            $this->sqlite_delete_query_builder->addEntities($mock_entities);
            $primaryKeysValues = array_map(fn ($mock_entity) => (string)$mock_entity->$p_key_name, $mock_entities);
        } else {
            $this->sqlite_delete_query_builder->addEntity($mock_entities);
            $primaryKeysValues = (string) $mock_entities->$p_key_name;
        }

        $query = new ReflectionMethod(SQLiteDeleteQueryBuilder::class, 'getQuery')
            ->invoke($this->sqlite_delete_query_builder, $p_key_name);

        foreach ($primaryKeysValues as $primaryKeyValue) {
            $query = preg_replace('/\?/', $primaryKeyValue, $query, 1);
        }

        $placeholder = implode(', ', $primaryKeysValues);
        $expectedQuery = 'delete from users where ' . $p_key_name . ' in (' . $placeholder . ')';

        $this->assertEquals($expectedQuery, $query);
    }

    public function testSettingEntityClassName(): void
    {
        $class_name = new ReflectionClass($this->sqlite_delete_query_builder)
            ->getProperty('entity_class')
            ->getValue($this->sqlite_delete_query_builder);

        $this->assertEquals($class_name, UserMockEntity::class);
    }
}
