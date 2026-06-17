<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\MySQL;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Builders\SelectQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;
use Sparkframe\Entity\Entity;
use Sparkframe\Exceptions\IncorrectSubquerySelectException;

/**
 * A QueryBuilder class for creating select queries.
 */
class MySQLSelectQueryBuilder implements SelectQueryBuilderInterface
{
    use QueryBuilderTrait;

    /** @property string[] $select_columns */
    protected array $select_columns = ['*'];

    protected int|null $limit_amount = null;

    /** @property array<string, mixed> $where_conditions */
    protected array $where_conditions = [];

    /** @property array<string, array<mixed>|SelectQueryBuilderInterface> $where_in_conditions */
    protected array $where_in_conditions = [];

    /** @property array<string, mixed> $or_conditions */
    protected array $or_conditions = [];

    /** @property array<string, array<mixed>|SelectQueryBuilderInterface> $where_in_conditions */
    protected array $or_in_conditions = [];

    protected int $prepared_statement_index = 0;

    /**
     * @param class-string<Entity> $entity_class
     */
    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class)
    {
    }

    public function select(string ...$column_names): MySQLSelectQueryBuilder
    {
        if ($this->select_columns === ['*'] && $column_names !== []) {
            $this->select_columns = [];
        }

        foreach ($column_names as $column_name) {
            $this->select_columns[] = $column_name;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function where(array $filter_criteria): MySQLSelectQueryBuilder
    {
        foreach ($filter_criteria as $expression => $filter_criterion) {
            if (!is_string($expression)) {
                throw new Exception('Expression must be a string!');
            }
            $this->where_conditions[] = [
                'expression' => $expression,
                'filter_criterion' => $filter_criterion
            ];
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function or(array $filter_criteria): MySQLSelectQueryBuilder
    {
        if (count($this->where_conditions) == 0 && count($this->where_in_conditions) == 0) {
            throw new Exception('Cannot use or without where conditions!');
        }
        $conditions = [];

        foreach ($filter_criteria as $expression => $filter_criterion) {
            if (!is_string($expression)) {
                throw new Exception('Expression must be a string!');
            }
            $conditions[] = [
                'expression' => $expression,
                'filter_criterion' => $filter_criterion
            ];
        }
        $this->or_conditions[] = $conditions;

        return $this;
    }

    /**
     * @throws IncorrectSubquerySelectException If a subquery does not have exactly one column in the SELECT clause.
     */
    public function orIn(string $column_name, SelectQueryBuilderInterface|array $values): MySQLSelectQueryBuilder
    {
        $this->addOrIn($column_name, $values);

        return $this;
    }

    /**
     * @throws IncorrectSubquerySelectException If a subquery does not have exactly one column in the SELECT clause.
     */
    public function orNotIn(string $column_name, SelectQueryBuilderInterface|array $values): MySQLSelectQueryBuilder
    {
        $this->addOrIn($column_name . ' not ', $values);

        return $this;
    }

    /**
     * @throws IncorrectSubquerySelectException If a subquery does not have exactly one column in the SELECT clause.
     */
    protected function addOrIn(string $column_name, SelectQueryBuilderInterface|array $values): void
    {
        if (is_array($values) && !empty($values)) {
            $values = array_map(fn ($value) => ['value' => $value], $values);

            $this->or_in_conditions[] = [
                'column' => $column_name,
                'values' => $values
            ];
        }

        if ($values instanceof MySQLSelectQueryBuilder) {
            if (!$values->readyForSubQuery()) {
                throw new IncorrectSubquerySelectException($values->getQuery());
            }

            $this->or_in_conditions[] = [
                'column' => $column_name,
                'values' => $values
            ];
        }
    }

    /**
     * @throws IncorrectSubquerySelectException If a subquery does not have exactly one column in the SELECT clause.
     */
    public function whereIn(string $column_name, SelectQueryBuilderInterface|array $values): MySQLSelectQueryBuilder
    {
        $this->addWhereIn($column_name, $values);

        return $this;
    }

    /**
     * @throws IncorrectSubquerySelectException If a subquery does not have exactly one column in the SELECT clause.
     */
    public function whereNotIn(string $column_name, SelectQueryBuilderInterface|array $values): MySQLSelectQueryBuilder
    {
        $this->addWhereIn($column_name . ' not ', $values);

        return $this;
    }

    /**
     * @throws IncorrectSubquerySelectException If a subquery does not have exactly one column in the SELECT clause.
     */
    protected function addWhereIn(string $column_name, MySQLSelectQueryBuilder|array $values): void
    {
        if (is_array($values) && !empty($values)) {
            $values = array_map(fn ($value) => ['value' => $value], $values);
            $this->where_in_conditions[] = [
                'column' => $column_name,
                'values' => $values
            ];
        }

        if ($values instanceof MySQLSelectQueryBuilder) {
            if (!$values->readyForSubQuery()) {
                throw new IncorrectSubquerySelectException($values->getQuery());
            }

            $this->where_in_conditions[] = [
                'column' => $column_name,
                'values' => $values
            ];
        }
    }

    /**
     * Checks if this query can be used as a subquery.
     * @return bool Returns True if there is exactly one value in the SELECT clause.
     */
    public function readyForSubQuery(): bool
    {
        $count = count($this->select_columns) === 1;
        $not_select_all = $this->select_columns !== ['*'];

        return $count && $not_select_all;
    }

    /**
     * Generates the WHERE part of the query string.
     * Also generates the query strings of the subqueries.
     * @return string
     */
    protected function getPreparedWherePart(): string
    {
        if (count($this->where_conditions) == 0 && count($this->where_in_conditions) == 0) {
            return '';
        }

        $where_array = [];
        $where_part = 'where ';

        foreach ($this->where_conditions as &$where_condition) {
            $where_array[] = $where_condition['expression'] . ' :' . $this->prepared_statement_index;
            $where_condition['prepared_statement_index'] = $this->prepared_statement_index;
            $this->prepared_statement_index++;
        }

        foreach ($this->where_in_conditions as &$where_in_condition) {
            if ($where_in_condition['values'] instanceof MySQLSelectQueryBuilder) {
                $column = $where_in_condition['column'];
                $subquery = $where_in_condition['values']->getQuery($this->prepared_statement_index);
                $where_array[] = $column . ' in (' . $subquery . ')';
                $this->prepared_statement_index = $where_in_condition['values']->getPreparedStatementIndex();
            } else {
                $indexes = [];

                foreach ($where_in_condition['values'] as &$value) {
                    $value['prepared_statement_index'] = $this->prepared_statement_index;
                    $indexes[] = $this->prepared_statement_index;
                    $this->prepared_statement_index++;
                }
                $where_array[] = $where_in_condition['column'] . ' in (:' . implode(', :', $indexes) . ')';
            }
        }
        $where_part .= implode(' and ', $where_array);

        return $where_part;
    }

    /**
     * Generates an arrayfor the WHERE clauses where the keys are the prepared statements in the sql query string.
     * The values will be inserted by PDO.
     * Also generates the prepared statements of the subqueries.
     * @return array
     */
    protected function getPreparedWherePartStatements(): array
    {
        if (count($this->where_conditions) == 0 && count($this->where_in_conditions) == 0) {
            return [];
        }

        $prepared_statements = [];

        foreach ($this->where_conditions as $where_condition) {
            $parameter_name = ':' . $where_condition['prepared_statement_index'];
            $prepared_statements[$parameter_name] = $where_condition['filter_criterion'];
        }

        foreach ($this->where_in_conditions as $where_in_condition) {
            if ($where_in_condition['values'] instanceof MySQLSelectQueryBuilder) {
                $subquery_prepared_statements = $where_in_condition['values']->getPreparedStatements();
                $prepared_statements = array_merge($prepared_statements, $subquery_prepared_statements);
            } else {
                foreach ($where_in_condition['values'] as &$value) {
                    $parameter_name = ':' . $value['prepared_statement_index'];
                    $prepared_statements[$parameter_name] = $value['value'];
                }
            }
        }

        return $prepared_statements;
    }

    /**
     * Generates the OR part of the query string.
     * Also generates the query strings of the subqueries.
     * @return string
     */
    protected function getPreparedOrPart(): string
    {
        $empty_where_part = count($this->where_conditions) == 0 && count($this->where_in_conditions) == 0;
        $empty_or_part = count($this->or_conditions) == 0 && count($this->or_in_conditions) == 0;

        if ($empty_where_part || $empty_or_part) {
            return '';
        }

        $or_array = [];
        $or_part = 'or ';

        foreach ($this->or_conditions as &$or_condition_array) {
            $temp_or_array = [];

            foreach ($or_condition_array as &$or_condition) {
                $temp_or_array[] = $or_condition['expression'] . ' :' . $this->prepared_statement_index;
                $or_condition['prepared_statement_index'] = $this->prepared_statement_index;
                $this->prepared_statement_index++;
            }
            $or_array[] = implode(' and ', $temp_or_array);
        }

        foreach ($this->or_in_conditions as &$or_in_condition) {
            $temp_or_in_array = [];

            if ($or_in_condition['values'] instanceof MySQLSelectQueryBuilder) {
                $column = $or_in_condition['column'];
                $subquery = $or_in_condition['values']->getQuery($this->prepared_statement_index);
                $temp_or_in_array[] = $column . ' in (' . $subquery . ')';
                $this->prepared_statement_index = $or_in_condition['values']->getPreparedStatementIndex();
            } else {
                $indexes = [];

                foreach ($or_in_condition['values'] as &$value) {
                    $value['prepared_statement_index'] = $this->prepared_statement_index;
                    $indexes[] = $this->prepared_statement_index;
                    $this->prepared_statement_index++;
                }
                $temp_or_in_array[] = $or_in_condition['column'] . ' in (:' . implode(', :', $indexes) . ')';
            }
            $or_array[] = implode(' and ', $temp_or_in_array);
        }

        $or_part .= implode(' or ', $or_array);

        return $or_part;
    }

    /**
     * Generates an array for the OR clauses where the keys are the prepared statements in the sql query string.
     * The values will be inserted by PDO.
     * Also generates the prepared statements of the subqueries.
     * @return array
     */
    protected function getPreparedOrPartStatements(): array
    {
        if (count($this->or_conditions) == 0 && count($this->or_in_conditions) == 0) {
            return [];
        }
        $prepared_statements = [];

        foreach ($this->or_conditions as $or_condition_array) {
            foreach ($or_condition_array as $or_condition) {
                $parameter_name = ':' . $or_condition['prepared_statement_index'];
                $prepared_statements[$parameter_name] = $or_condition['filter_criterion'];
            }
        }

        foreach ($this->or_in_conditions as $or_in_condition) {
            if ($or_in_condition['values'] instanceof MySQLSelectQueryBuilder) {
                $subquery_prepared_statements = $or_in_condition['values']->getPreparedStatements();
                $prepared_statements = array_merge($prepared_statements, $subquery_prepared_statements);
            } else {
                foreach ($or_in_condition['values'] as &$value) {
                    $parameter_name = ':' . $value['prepared_statement_index'];
                    $prepared_statements[$parameter_name] = $value['value'];
                }
            }
        }

        return $prepared_statements;
    }

    /**
     * Generates an array for the OR and WHERE clauses where the keys are the prepared statements in the sql query string.
     * The values will be inserted by PDO.
     * Also generates the prepared statements of the subqueries.
     * @return array
     */
    public function getPreparedStatements(): array
    {
        $prepared_where_statements = $this->getPreparedWherePartStatements();
        $prepared_or_statements = $this->getPreparedOrPartStatements();

        return array_merge($prepared_where_statements, $prepared_or_statements);
    }

    /**
     * Clears the WHERE clauses of this query.
     */
    public function clearWhere(): void
    {
        $this->where_conditions = [];
        $this->where_in_conditions = [];
    }

    /**
     * Clears the OR clauses of this query.
     */
    public function clearOr(): void
    {
        $this->or_conditions = [];
        $this->or_in_conditions = [];
    }

    /**
     * Returns the query string with prepared statements.
     * @param int $prepared_statement_index Sets where the prepared statements start.
     *  Use this if you don't want to start at 0.
     * @return string The query string.
     * @throws Exception
     */
    public function getQuery(int $prepared_statement_index = 0): string
    {
        $this->prepared_statement_index = $prepared_statement_index;
        $query_string = $this->getSelectPart();
        $query_string .= 'from ' . $this->getTargetTable() . ' ';
        $query_string .= $this->getPreparedWherePart() . ' ';
        $query_string .= $this->getPreparedOrPart() . ' ';
        $query_string .= $this->getLimitPart();

        return $query_string;
    }

    /**
     * Generates the select part string of the query.
     * DOES NOT PREVENT SQL INJECTION! ONLY USE YOUR OWN VALUES!
     */
    protected function getSelectPart(): string
    {
        return 'select ' . implode(', ', $this->select_columns) . ' ';
    }

    /**
     * @throws Exception
     */
    public function execute(): array
    {
        if (empty($this->entity_class)) {
            throw new Exception('Tried to execute select query without Entity class being set.');
        }

        $query_string = $this->getQuery();
        $query = $this->PDO
            ->prepare($query_string);

        $prepared_statements = $this->getPreparedStatements();

        $query->execute($prepared_statements);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $hydrated_result = [];
        $entity_class = $this->entity_class;

        foreach ($result as $row) {
            $hydrated_result[] = new $entity_class($row);
        }

        return $hydrated_result;
    }

    public function limit(int $limit_amount): MySQLSelectQueryBuilder
    {
        $this->limit_amount = $limit_amount;

        return $this;
    }

    /**
     * Generates the limit part string of the query.
     * @return string The limit part string.
     */
    protected function getLimitPart(): string
    {
        if ($this->limit_amount == null) {
            return '';
        }

        return " limit $this->limit_amount";
    }

    /**
     * @return int The current index of the prepared statements.
     */
    public function getPreparedStatementIndex(): int
    {
        return $this->prepared_statement_index;
    }

    /**
     * Cleans up the query builder so it can be reused.
     */
    public function cleanUp(): void
    {
        $this->select_columns = ['*'];
        $this->clearWhere();
        $this->clearOr();
        $this->prepared_statement_index = 0;
    }
}
