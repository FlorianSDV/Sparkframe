<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\MySQL;

use Pdo\Mysql;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Sparkframe\Database\MySQLDatabaseWrapper;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLDeleteQueryBuilder;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

class MySQLDeleteQueryBuilderTest extends TestCase
{
    private MySQLDeleteQueryBuilder $mysql_delete_query_builder;

    public function setUp(): void
    {
        $this->mysql_delete_query_builder = new MySQLDatabaseWrapper($this->createStub(Mysql::class))
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
    public function testDeleteQuery(array $mock_entities): void
    {
        foreach ($mock_entities as $mock_entity) {
            $this->mysql_delete_query_builder->addEntity($mock_entity);
        }

        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();

        $query = new ReflectionMethod(MySQLDeleteQueryBuilder::class, 'getQuery') 
            ->invoke($this->mysql_delete_query_builder, $p_key_name);

        $placeholder = str_repeat('?, ', count($mock_entities) - 1) . '?';
        $expected_query = 'delete from users where ' . $p_key_name . ' in (' . $placeholder . ')';
        
        $this->assertEquals($expected_query, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testDeleteQueryWithValues(array $mock_entities): void
    {
        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();
    
        $primaryKeysValues = [];
        foreach ($mock_entities as $mock_entity) {
            $this->mysql_delete_query_builder->addEntity($mock_entity);
            $primaryKeysValues[] = (string)$mock_entity->$p_key_name;
        }
        
        $query = new ReflectionMethod(MySQLDeleteQueryBuilder::class, 'getQuery')
            ->invoke($this->mysql_delete_query_builder, $p_key_name);

        foreach ($primaryKeysValues as $primaryKeyValue) {
            $query = preg_replace('/\?/', $primaryKeyValue, $query, 1);
        }

        $placeholder = implode(', ', $primaryKeysValues);
        $expectedQuery = 'delete from users where ' . $p_key_name . ' in (' . $placeholder . ')';

        $this->assertEquals($expectedQuery, $query);
    }

    public function testSettingEntityClassName(): void
    {
        $class_name = new ReflectionClass(MySQLDeleteQueryBuilder::class)
            ->getProperty('entity_class')
            ->getValue($this->mysql_delete_query_builder);

        $this->assertEquals($class_name, UserMockEntity::class);
    }
}
