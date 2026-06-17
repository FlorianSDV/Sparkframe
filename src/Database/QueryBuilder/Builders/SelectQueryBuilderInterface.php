<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Builders;

use Sparkframe\Database\QueryBuilder\Composition\QueryBuilderInterface;

interface SelectQueryBuilderInterface extends QueryBuilderInterface
{
    /**
     * Specifies the columns to select.
     * DOES NOT PREVENT SQL INJECTION! ONLY USE YOUR OWN VALUES!
     * @param string ...$column_names any number of column names
     */
    public function select(string ...$column_names): SelectQueryBuilderInterface;

    /**
     * Specifies the amount of rows to limit the query to.
     * @param int $limit_amount The amount of rows to limit the query to.
     */
    public function limit(int $limit_amount): SelectQueryBuilderInterface;

    /**
     * Specifies the filter criteria to apply to the query.
     * @param array<string, mixed> $filter_criteria The filter criteria to apply to the query.
     * Must be in the following format:
     *  ['column_name' . 'operator' => 'value']
     * For example:
     *  ['id' . '=' => 1]
     */
    public function where(array $filter_criteria): SelectQueryBuilderInterface;

    /**
     * Add an IN filter to the query.
     * @param string $column_name The column name to apply the filter to.
     * @param SelectQueryBuilderInterface|array $values An array of values or another query that can be used as a subquery.
     * If using a query it must select a single column.
     */
    public function whereIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    /**
     * Add a NOT IN filter to the query.
     * @param string $column_name The column name to apply the filter to.
     * @param SelectQueryBuilderInterface|array $values An array of values or another query that can be used as a subquery.
     * If using a query it must select a single column.
     */
    public function whereNotIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    /**
     * Add an OR filter to the query.
     * @param array<string, mixed> $filter_criteria The filter criteria to apply to the query.
     * Must be in the following format:
     *  ['column_name' . 'operator' => 'value']
     * For example:
     *  ['id' . '=' => 1]
     */
    public function or(array $filter_criteria): SelectQueryBuilderInterface;

    /**
     * Add an OR IN filter to the query.
     * @param string $column_name The column name to apply the filter to.
     * @param SelectQueryBuilderInterface|array $values An array of values or another query that can be used as a subquery.
     * If using a query it must select a single column.
     */
    public function orIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    /**
     * Add an OR NOT IN filter to the query.
     * @param string $column_name The column name to apply the filter to.
     * @param SelectQueryBuilderInterface|array $values An array of values or another query that can be used as a subquery.
     * If using a query it must select a single column.
     */
    public function orNotIn(string $column_name, SelectQueryBuilderInterface|array $values): SelectQueryBuilderInterface;

    /**
     * @return int The index of the prepared statement. Used internally for subqueries.
     */
    public function getPreparedStatementIndex(): int;

    /**
     * Returns the query string with prepared statements.
     * @param int $prepared_statement_index Sets where the prepared statements start.
     *  Use this if you don't want to start at 0.
     * @return string The query string.
     */
    public function getQuery(int $prepared_statement_index = 0): string;
}
