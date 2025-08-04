<?php

namespace Sparkframe\Database\QueryBuilder;

interface QueryWithWhere
{
    function where(array $filter_criteria): self;
    function getPreparedWherePart(): string;
    function getPreparedWherePartStatements(): array;
}
