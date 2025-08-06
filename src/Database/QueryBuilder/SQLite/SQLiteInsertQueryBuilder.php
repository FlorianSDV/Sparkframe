<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\InsertQueryTrait;
use Sparkframe\Entity\Entity;

class SQLiteInsertQueryBuilder extends SQLiteQueryBuilder implements InsertQueryBuilder
{
    use InsertQueryTrait;

    public function __construct(PDO $PDO, string $target_table_name, string $entity_class)
    {
        $this->entity_class = $entity_class;
        parent::__construct($PDO, $target_table_name);
    }

    /**
     * Generates the SQL query string for inserting a single entity into the target table.
     * @param array $columns
     * @return string
     */
    private function getQuery(array $columns): string
    {
        $sql_columns = implode(', ', $columns);
        $values = array_map(fn() => '?', $columns);
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

        /** @var class-string<Entity> $this ->entity_class */
        $columns = $this->entity_class::getColumnNames();

        $sql = $this->getQuery($columns);

        try {
            $pdo = $this->PDO;
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);

            foreach ($this->entities as $entity) {
                $actual_values = $entity->getActualValues();
                $stmt->execute($actual_values);
                $entity->setId($pdo->lastInsertId());
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception("Failed to execute insert query: " . $e->getMessage(), 0, $e);
        }

        $this->clearEntities();
        $this->clearEntityClass();
    }
}
