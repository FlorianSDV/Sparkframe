<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use Sparkframe\Database\DatabaseWrapper;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Entity\Entity;

class SQLiteInsertQueryBuilder extends SQLiteQueryBuilder implements InsertQueryBuilder
{
    /** @var class-string<Entity> $this ->entity_class */
    private string $entity_class;

    /** @var Entity[] $this ->entities */
    private array $entities = [];

    public function __construct(DatabaseWrapper $databaseWrapper, string $target_table_name, string $entity_class)
    {
        $this->entity_class = $entity_class;
        parent::__construct($databaseWrapper, $target_table_name);
    }

    /**
     * @throws Exception
     */
    public function addEntity(Entity $entity): InsertQueryBuilder
    {
        // Todo: dit kan naar een andere class of een trait. Het kan hoe dan ook hergebruikt worden.
        // Todo: dit geld ook voor de clearEntities methode. En voor de $entities property.
        $class_name = $entity::class;
        if ($this->entity_class !== $class_name) {
            throw new Exception("Entity class $class_name does not match the expected class {$this->entity_class}.");
        }
        $this->entities[] = $entity;

        return $this;
    }

    public function clearEntities(): InsertQueryBuilder
    {
        unset($this->entities);

        return $this;
    }

    public function clearEntityClass(): InsertQueryBuilder
    {
        unset($this->entity_class);

        return $this;
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

        $columns = $this->entity_class::getColumnNames();

        $sql = $this->getQuery($columns);

        try {
            $pdo = $this->databaseWrapper->getPdo();
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
