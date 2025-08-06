<?php

namespace Sparkframe\Database\QueryBuilder;

use PDO;

abstract class QueryBuilder
{
    public function __construct(protected PDO $PDO, protected string $target_table_name)
    {
    }

    abstract public function getTargetTable(): string;

    abstract function execute();
}