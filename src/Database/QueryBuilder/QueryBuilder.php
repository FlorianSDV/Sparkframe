<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Database\DataBaseConnection;

abstract class QueryBuilder
{
    protected array $where_columns = [];

    public function __construct(protected DataBaseConnection $dataBaseConnection, protected string $from_table_name)
    {
    }

    abstract public function getFromPart(): string;

    abstract function where(): self;

    abstract function execute();

    abstract function getWherePart(): string;
}