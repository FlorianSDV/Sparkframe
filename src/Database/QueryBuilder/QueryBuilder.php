<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Database\DataBaseConnection;

abstract class QueryBuilder
{
    public function __construct(protected DataBaseConnection $dataBaseConnection, protected string $from_table_name)
    {
    }

    abstract public function getFromPart(): string;

    abstract function execute();
}