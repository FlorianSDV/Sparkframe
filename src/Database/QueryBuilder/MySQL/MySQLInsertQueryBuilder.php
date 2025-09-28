<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\MySQL;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;
use Sparkframe\Database\QueryBuilder\Traits\QueryWithEntitiesTrait;

class MySQLInsertQueryBuilder implements InsertQueryBuilderInterface
{
    use QueryBuilderTrait;
    use QueryWithEntitiesTrait;

    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class) { }

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

    public function cleanUp(): void
    {
        $this->clearEntities();
    }
}
