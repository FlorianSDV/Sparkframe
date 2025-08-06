<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Database\DatabaseWrapper;

abstract class QueryBuilder
{
    public function __construct(protected DatabaseWrapper $databaseWrapper, protected string $target_table_name)
    {
    }

    abstract public function getTargetTable(): string;

    abstract function execute();
}