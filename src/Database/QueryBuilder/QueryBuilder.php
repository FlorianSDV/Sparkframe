<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Database\DataBaseConnection;

abstract class QueryBuilder
{
    public function __construct(protected DataBaseConnection $dataBaseConnection, protected string $target_table_name)
    {
    }

    abstract public function getTargetTable(): string;

    abstract function execute();
}