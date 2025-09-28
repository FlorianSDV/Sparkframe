<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Builders\DeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;
use Sparkframe\Database\QueryBuilder\Traits\QueryWithEntitiesTrait;

class SQLiteDeleteQueryBuilder implements DeleteQueryBuilder
{
    use QueryBuilderTrait;
    use QueryWithEntitiesTrait;

    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class) { }
    
    public function execute()
    {
        if (empty($this->entities)) {
            throw new Exception("Tried to execute update query without any Entities set.");
        }

        if (empty($this->entity_class)) {
            throw new Exception("Tried to execute update query without Entity class being set.");
        }
        
        $primary_key_column_name = $this->entity_class::getPrimaryKeyColumnName();
        $query_string = $this->getQuery($primary_key_column_name);
        
        $query = $this->PDO->prepare($query_string);
        $all_primary_keys = array_map(fn($entity) => $entity->$primary_key_column_name, $this->entities);
        $query->execute($all_primary_keys);
        $this->cleanUp();
    }

    private function getQuery(string $primary_key_column_name): string
    {
        $placeholder = str_repeat('?, ', count($this->entities) - 1) . '?';
        $where_part = "where $primary_key_column_name in ($placeholder)";

        $target_table_name = $this->getTargetTable();
        $sql_string = "delete from {$target_table_name} $where_part";

        return $sql_string;
    }


    public function cleanUp(): void
    {
        $this->clearEntities();
    }
}
