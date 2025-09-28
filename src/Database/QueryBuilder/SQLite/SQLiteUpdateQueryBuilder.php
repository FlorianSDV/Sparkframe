<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;
use Sparkframe\Database\QueryBuilder\Traits\QueryWithEntitiesTrait;
use Sparkframe\Database\QueryBuilder\Builders\UpdateQueryBuilderInterface;

class SQLiteUpdateQueryBuilder implements UpdateQueryBuilderInterface
{
    use QueryBuilderTrait;
    use QueryWithEntitiesTrait;

    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class) { }


    /**
     * @throws Exception
     */
    function execute(): void
    {
        if (empty($this->entities)) {
            throw new Exception("Tried to execute update query without any Entities set.");
        }

        if (empty($this->entity_class)) {
            throw new Exception("Tried to execute update query without Entity class being set.");
        }

        $primary_key_column_name = $this->entity_class::getPrimaryKeyColumnName();
        $sql = $this->getQuery($primary_key_column_name);

        try {
            $pdo = $this->PDO;
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);

            foreach ($this->entities as $entity) {
                $update_values = $entity->getValuesArray();
                $where = [$primary_key_column_name => $entity->$primary_key_column_name];
                $final_array = array_merge($update_values, $where);

                $stmt->execute($final_array);
            }
            $pdo->commit();


        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception("Failed to execute update query: " . $e->getMessage(), 0, $e);
        }

        $this->cleanUp();
    }

    /**
     * @throws Exception
     */
    private function getQuery($primary_key_column_name): string
    {
        $columns = $this->entity_class::getColumnNames();

        $set_part = '';
        foreach ($columns as $key => $column) {
            $set_part .= "$column = :$column";
            if (array_key_last($columns) == $key) {
                break;
            }
            
            $set_part .= ', ';
        }

        $where_part = "where $primary_key_column_name = :$primary_key_column_name";

        return "update $this->target_table_name set $set_part $where_part";
    }
    
    public function cleanUp(): void
    {
        $this->clearEntities();
    }
}