<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;
use Sparkframe\Database\QueryBuilder\Traits\QueryWithEntitiesTrait;
use Sparkframe\Entity\Entity;

class SQLiteInsertQueryBuilder implements InsertQueryBuilderInterface
{
    use QueryBuilderTrait;
    use QueryWithEntitiesTrait;

    /**
     * @param class-string<Entity> $entity_class
     */
    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class)
    {
    }

    /**
     * Generates the SQL query string for inserting a single entity into the target table.
     */
    private function getQuery(array $columns): string
    {
        $sql_columns = implode(', ', $columns);
        $values = array_map(fn ($column) => ":$column", $columns);
        $sql_values_part = implode(', ', $values);

        return "insert into {$this->getTargetTable()} ($sql_columns) values ($sql_values_part)";
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        if (empty($this->entities)) {
            throw new Exception('Tried to execute insert query without any Entities set.');
        }

        if (empty($this->entity_class)) {
            throw new Exception('Tried to execute insert query without Entity class being set.');
        }

        $columns = $this->entity_class::getColumnNames();

        $sql = $this->getQuery($columns);

        $primary_key_data_type = $this->entity_class::getPrimaryKeyDataType();

        $pdo = $this->PDO;
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);

            foreach ($this->entities as $entity) {
                $values = $entity->getValuesArray();
                $stmt->execute($values);
                $last_insert_id = $pdo->lastInsertId();
                $last_insert_id = $this->convertIdToDataType($last_insert_id, $primary_key_data_type);
                $entity->setId($last_insert_id);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();

            throw new Exception('Failed to execute insert query: ' . $e->getMessage(), 0, $e);
        }

        $this->cleanUp();
    }

    private function convertIdToDataType(string|int $id, string $data_type): string|int
    {
        switch ($data_type) {
            case 'int':
                return (int) $id;
            case 'string':
                return (string) $id;
        }

        return $id;
    }

    public function cleanUp(): void
    {
        $this->clearEntities();
    }
}
