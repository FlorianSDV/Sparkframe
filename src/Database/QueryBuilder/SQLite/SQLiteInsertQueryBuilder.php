<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Entity\Entity;

class SQLiteInsertQueryBuilder extends SQLiteQueryBuilder implements InsertQueryBuilder
{
    // todo: implement a way to add multiple entities at once, maybe by using an array of entities
    private ?Entity $entity;
    public function addEntity(Entity $entity): InsertQueryBuilder
    {
        $this->entity = $entity;

        return $this;
    }

    public function clearEntity(): InsertQueryBuilder
    {
        unset($this->entity);

        return $this;
    }

    /**
     * @throws Exception
     */
    function execute(): void
    {
        if (empty($this->entity)) {
            throw new Exception("Tried to execute insert query without any Entities set.");
        }

        $columns = $this->entity::getColumnNames();

        $sql = $this->getQuery($columns);
        $actual_values = $this->getActualValues($columns);

        $this->dataBaseConnection
            ->prepare($sql)
            ->execute($actual_values);

        $insert_id = $this->dataBaseConnection->getLastInsertId();
        $this->entity->setId($insert_id);
        $this->clearEntity();
    }

    private function getQuery(array $columns): string
    {
        $sql_columns = implode(', ', $columns);
        $values = array_map(fn() => '?', $columns);
        $sql_values_part = implode(', ', $values);

        return "insert into {$this->getTargetTable()} ($sql_columns) values ($sql_values_part)";
    }

    /**
     * @param array $columns
     * @return array
     */
    public function getActualValues(array $columns): array
    {
        $values = [];
        foreach ($columns as $column) {
            if (
                !property_exists($this->entity, $column) ||
                !isset($this->entity->{$column})
            ) {
                $values[] = null;
                continue;
            }
            $values[] = $this->entity->{$column};
        }
        return $values;
    }
}
