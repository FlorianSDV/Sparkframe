<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\QueryWithEntitiesTrait;
use Sparkframe\Entity\Entity;

class SQLiteInsertQueryBuilder extends SQLiteQueryBuilder implements InsertQueryBuilder
{
    use QueryWithEntitiesTrait;

    /** @var class-string<Entity> */
    protected string $entity_class;

    /**
     * Generates the SQL query string for inserting a single entity into the target table.
     * @param array $columns
     * @return string
     */
    private function getQuery(array $columns): string
    {
        $sql_columns = implode(', ', $columns);
        $values = array_map(fn($column) => ":$column", $columns);
        $sql_values_part = implode(', ', $values);

        return "insert into {$this->getTargetTable()} ($sql_columns) values ($sql_values_part)";
    }

    /**
     * @throws Exception
     */
    function execute(): void
    {
        if (empty($this->entities)) {
            throw new Exception("Tried to execute insert query without any Entities set.");
        }

        if (empty($this->entity_class)) {
            throw new Exception("Tried to execute insert query without Entity class being set.");
        }

        $columns = $this->entity_class::getColumnNames();

        $sql = $this->getQuery($columns);

        try {
            $pdo = $this->PDO;
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);

            foreach ($this->entities as $entity) {
                $values = $entity->getValuesArray();
                $stmt->execute($values);
                $entity->setId($pdo->lastInsertId());
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception("Failed to execute insert query: " . $e->getMessage(), 0, $e);
        }

        $this->cleanUp();
    }

    protected function cleanUp(): void
    {
        $this->clearEntities();
    }
}
