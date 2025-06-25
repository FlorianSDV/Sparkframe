<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Database\DataBaseConnection;

abstract class QueryBuilder
{
    protected array $where_conditions = [];

    public function __construct(protected DataBaseConnection $dataBaseConnection, protected string $from_table_name)
    {
    }

    abstract public function getFromPart(): string;

    abstract function where(array $filter_criteria): self;

    abstract function execute();

    abstract function getPreparedWherePart(): string;
    abstract function getPreparedWherePartStatements(): array;
}