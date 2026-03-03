<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Builders;

use Sparkframe\Database\QueryBuilder\Composition\QueryBuilderInterface;

interface SelectQueryBuilderInterface extends QueryBuilderInterface
{
    /**
     * @param string ...$column_names any number of column names
     */
    public function select(string ...$column_names): SelectQueryBuilderInterface;

    public function limit(int $limit_amount): SelectQueryBuilderInterface;

    public function where(array $filter_criteria): SelectQueryBuilderInterface;

    public function whereIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    public function whereNotIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    public function or(array $filter_criteria): SelectQueryBuilderInterface;

    public function orIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    public function orNotIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    public function getPreparedStatementIndex(): int;

    public function getQuery(int $prepared_statement_index = 0): string;
}
