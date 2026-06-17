<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Entity;

use Exception;
use Sparkframe\Entity\Entity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Sparkframe\Attributes\Primary;
use Sparkframe\Tests\Mocks\Entities\EntityWithoutPrimaryKey;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

/**
 * Tests for the Entity base class.
 */
class EntityTest extends TestCase
{
    public static function mockEntityProvider(): array
    {
        $mockEntity1 = new UserMockEntity();
        $mockEntity1->setId(1);
        $mockEntity1->name = 'John Doe';
        $mockEntity1->email_address = 'john.doe@example.com';
        $mockEntity1->age = 30;
        $mockEntity1->phone_number = '1234567890';

        return [
            'single_entity' => [$mockEntity1],
        ];
    }

    public function testConstruct(): void
    {
        $expected = [
            'id' => 1,
            'name' => 'John Doe',
            'email_address' => 'john.doe@example.com',
            'age' => 30,
            'phone_number' => '1234567890'
        ];
        $mockEntity = new UserMockEntity($expected);

        $this->assertEquals($expected['id'], $mockEntity->id);
        $this->assertEquals($expected['name'], $mockEntity->name);
        $this->assertEquals($expected['email_address'], $mockEntity->email_address);
        $this->assertEquals($expected['age'], $mockEntity->age);
        $this->assertEquals($expected['phone_number'], $mockEntity->phone_number);
    }

    #[DataProvider('mockEntityProvider')]
    public function testGetColumnNames(UserMockEntity $mockEntity): void
    {
        $column_names = $mockEntity->getColumnNames();
        $expected = [
            'id',
            'name',
            'age',
            'email_address',
            'phone_number'
        ];
        sort($column_names);
        sort($expected);

        $this->assertEquals($expected, $column_names);
    }

    #[DataProvider('mockEntityProvider')]
    public function testGetColumnReflections(UserMockEntity $mockEntity): void
    {
        $getColumnReflectionsMethod = new ReflectionMethod($mockEntity, 'getColumnReflections');

        /** @var ReflectionProperty[] $column_reflections */
        $column_reflections = $getColumnReflectionsMethod->invoke($mockEntity);

        $this->assertContainsOnlyInstancesOf(ReflectionProperty::class, $column_reflections);
        foreach ($column_reflections as $columnReflection) {
            $name = $columnReflection->getName();
            $this->assertTrue(in_array($name, [
                'id',
                'name',
                'age',
                'email_address',
                'phone_number'
            ]));
        }

        /** @var ReflectionProperty[] $primary_column_reflections */
        $primary_column_reflections = $getColumnReflectionsMethod->invoke($mockEntity, Primary::class);
        $this->assertContainsOnlyInstancesOf(ReflectionProperty::class, $primary_column_reflections);
        foreach ($primary_column_reflections as $primary_column_reflection) {
            $name = $primary_column_reflection->getName();
            $this->assertTrue($primary_column_reflection->getName() == 'id');
        }
    }


    #[DataProvider('mockEntityProvider')]
    public function testGetColumnReflectionsError(UserMockEntity $mockEntity): void
    {
        $getColumnReflectionsMethod = new ReflectionMethod($mockEntity, 'getColumnReflections');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $type must be Column::class or Primary::class');
        $getColumnReflectionsMethod->invoke($mockEntity, Object::class);
    }

    public function testGetPrimaryKeyColumnName(): void
    {
        $expected = 'id';
        $primary_key_column_name = UserMockEntity::getPrimaryKeyColumnName();

        $this->assertEquals($expected, $primary_key_column_name);
    }

    public function testGetPrimaryKeyColumnNameError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No primary key set');

        EntityWithoutPrimaryKey::getPrimaryKeyColumnName();
    }

    public function testSetId(): void
    {
        $id = 1;
        $mockEntity = new UserMockEntity();
        $mockEntity->setId($id);

        $this->assertEquals($id, $mockEntity->id);
    }


    #[DataProvider('mockEntityProvider')]
    public function testGetValuesArray(UserMockEntity $mockEntity): void
    {
        $expected = [
            'id' => 1,
            'name' => 'John Doe',
            'email_address' => 'john.doe@example.com',
            'age' => 30,
            'phone_number' => '1234567890'
        ];
        $values_array = $mockEntity->getValuesArray();

        $this->assertEquals($expected, $values_array);
    }

    public function testGetPrimaryKeyDataType(): void
    {
        $expected = 'int';
        $data_type = UserMockEntity::getPrimaryKeyDataType();

        $this->assertEquals($expected, $data_type);
    }

    public function testGetColumnDataType(): void
    {
        $properties_expected_data_types = [
            'id' => 'int',
            'name' => 'string',
            'email_address' => 'string',
            'age' => 'int',
            'phone_number' => 'string'
        ];

        foreach ($properties_expected_data_types as $property => $expected_data_type) {
            $data_type = UserMockEntity::getColumnDataType($property);
            $this->assertEquals($expected_data_type, $data_type);
        }
    }

    public function testGetColumnDataTypeError(): void
    {
        $column = 'fake_column';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Column: ' . $column . ' does not exist!');

        UserMockEntity::getColumnDataType($column);
    }
}
