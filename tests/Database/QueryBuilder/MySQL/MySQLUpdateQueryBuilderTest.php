<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Mysql;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\MySQLDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLUpdateQueryBuilder;

class MySQLUpdateQueryBuilderTest extends TestCase
{
    private MySQLUpdateQueryBuilder $mysql_update_query_builder;

    public function setUp(): void
    {
        $this->mysql_update_query_builder = new MySQLDatabaseWrapper($this->createStub(Mysql::class))
            ->updateQuery('users', UserMockEntity::class);
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

    public function testUpdateQuery()
    {
        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();
        $query = new ReflectionMethod(MySQLUpdateQueryBuilder::class, 'getQuery')
            ->invoke($this->mysql_update_query_builder, $p_key_name);

        $expected_query = "update users set $p_key_name = :$p_key_name, name = :name, email_address = :email_address, age = :age, phone_number = :phone_number where $p_key_name = :$p_key_name";
        
        $this->assertEquals($expected_query, $query);
    }

    #[DataProvider('mockEntityProvider')]
    public function testUpdateQueryWithValues(array $mock_entities): void 
    {
        $p_key_name = UserMockEntity::getPrimaryKeyColumnName();
        $base_query = new ReflectionMethod(MySQLUpdateQueryBuilder::class, 'getQuery')
            ->invoke($this->mysql_update_query_builder, $p_key_name);
        $base_expected_query = "update users set $p_key_name = :$p_key_name, name = :name, email_address = :email_address, age = :age, phone_number = :phone_number where $p_key_name = :$p_key_name";

        /** @var UserMockEntity $mock_entity  */
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
        $class_name = new ReflectionClass(MySQLUpdateQueryBuilder::class)
            ->getProperty('entity_class')
            ->getValue($this->mysql_update_query_builder);

        $this->assertEquals($class_name, UserMockEntity::class);
    }
}
