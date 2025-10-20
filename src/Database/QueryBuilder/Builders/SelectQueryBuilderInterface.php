<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Builders;

use Sparkframe\Database\QueryBuilder\Composition\QueryBuilderInterface;

interface SelectQueryBuilderInterface extends QueryBuilderInterface
{
    /**
     * @param string ...$column_names any number of column names
     * @return self
     */
    public function select(string ...$column_names): self;

    /**
     * @param int $limit_amount
     * @return self
     */
    public function limit(int $limit_amount): self;

    /**
     * @param array $filter_criteria
     * @return self
     */
    public function where(array $filter_criteria): self;

    /**
     * @param string $column_name
     * @param SelectQueryBuilderInterface|array $values
     * @return self
     */
    public function whereIn(string $column_name, SelectQueryBuilderInterface|array $values): self;

    /**
     * @param string $column_name
     * @param SelectQueryBuilderInterface|array $values
     * @return self
     */
    public function whereNotIn(string $column_name, SelectQueryBuilderInterface|array $values): self;

    public function or(array $filter_criteria): self;

    /**
     * @param array<string, SelectQueryBuilderInterface|array> $filter_criteria
     * @return self
     */
    public function orIn(array $filter_criteria): self;

    public function getPreparedStatementIndex(): int;

    public function getQuery(int $prepared_statement_index = 0): string;
}
