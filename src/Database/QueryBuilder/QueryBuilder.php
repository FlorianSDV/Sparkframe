<?php

namespace Sparkframe\Database\QueryBuilder;

use PDO;

abstract class QueryBuilder
{
    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class)
    {
    }

    abstract public function getTargetTable(): string;

    abstract function execute();

    abstract protected function cleanUp(): void;
}