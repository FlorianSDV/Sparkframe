<?php

declare(strict_types=1);

namespace Sparkframe\Entity;

use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Sparkframe\Attributes\Column;
use Sparkframe\Attributes\Primary;

/**
 * An object representing a single row in a database table.
 */
abstract class Entity
{
    public function __construct(array $columns_and_values = [])
    {
        $column_names = static::getColumnNames();

        foreach ($columns_and_values as $column => $value) {
            if (in_array($column, $column_names)) {
                $this->$column = $value;
            }
        }
    }

    /**
     * @return string[] the names of all the columns that are defined with a #[Column] or #[Primary] attribute.
     */
    public static function getColumnNames(): array
    {
        $reflections = static::getColumnReflections();
        return array_map(
            fn (ReflectionProperty $columnReflection): string => $columnReflection->getName(),
            $reflections
        );
    }

    /**
     * Get reflections of all the Entity properties that have a #[Column] or #[Primary] attrubute.
     * @param class-string<Column>|class-string<Primary> $type The type of column to get. Defaults to all columns.
     * @return ReflectionProperty[]
     */
    protected static function getColumnReflections(string $type = Column::class): array
    {
        if ($type !== Column::class && $type !== Primary::class) {
            throw new \InvalidArgumentException(
                'Argument $type must be Column::class or Primary::class'
            );
        }

        $columns = [];
        $properties = new ReflectionClass(static::class)->getProperties();

        foreach ($properties as $property) {
            if ($property->getAttributes($type, ReflectionAttribute::IS_INSTANCEOF)) {
                $columns[] = $property;
            }
        }

        return $columns;
    }

    /**
     * @throws Exception
     */
    public static function getPrimaryKeyColumnName(): string
    {
        $primary_key_column_reflections = static::getColumnReflections(Primary::class);
        if (!$primary_key_column_reflections) {
            throw new Exception('No primary key set');
        }

        return $primary_key_column_reflections[0]->getName();
    }

    /**
     * @throws Exception
     */
    public function setId(string|int $id): void
    {
        $primary_key = $this->getPrimaryKeyColumnName();
        $this->$primary_key = $id;
    }

    /**
     * @return array<string, mixed> A key value array of the column names and their values.
     */
    public function getValuesArray(): array
    {
        $values = [];

        foreach (static::getColumnNames() as $column_name) {
            $value = null;

            if (
                property_exists($this, $column_name) &&
                isset($this->{$column_name})
            ) {
                $value = $this->$column_name;
            }
            $values[$column_name] = $value;
        }

        return $values;
    }

    public static function getPrimaryKeyDataType(): string
    {
        return static::getColumnReflections(Primary::class)[0]
            ->getType()
            ->getName();
    }

    /**
     * @throws Exception
     */
    public static function getColumnDataType(string $column_name): string
    {
        $column_reflections = static::getColumnReflections();
        $column = array_find(
            $column_reflections,
            fn (ReflectionProperty $columnReflection): bool =>
            $columnReflection->getName() == $column_name
        );

        if (!$column) {
            throw new Exception('Column: ' . $column_name . ' does not exist!');
        }

        return $column->getType()->getName();
    }
}
