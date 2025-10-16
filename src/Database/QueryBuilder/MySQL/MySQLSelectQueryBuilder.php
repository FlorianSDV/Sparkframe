<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\MySQL;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Builders\SelectQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;

class MySQLSelectQueryBuilder implements SelectQueryBuilderInterface
{
    use QueryBuilderTrait;
    protected array $select_columns = ['*'];
    protected int|null $limit_amount = null;
    protected array $where_conditions = [];
    protected array $where_not_in_conditions = [];
    protected array $or_conditions = [];
    protected int $prepared_statement_index = 0;

    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class) { }

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
    public function where(array $filter_criteria): self
    {
        foreach ($filter_criteria as $column => $filter_criterion) {
            if (!is_string($column)) {
                throw new Exception('Column name must be a string!');
            }
            $this->where_conditions[] = [
                'column' => $column,
                'filter_criterion' => $filter_criterion
            ];
        }

        return $this;
    }

    public function or(array $filter_criteria): self
    {
        if (count($this->where_conditions) == 0) {
            throw new Exception('Cannot use or without where conditions!');
        }
        foreach ($filter_criteria as $column => $filter_criterion) {
            if (!is_string($column)) {
                throw new Exception('Column name must be a string!');
            }
            $this->or_conditions[] = [
                'column' => $column,
                'filter_criterion' => $filter_criterion
            ];
        }

        return $this;
    }

    public function whereNotIn(string $column_name, SelectQueryBuilderInterface|array $values): self
    {
        if (is_array($values) && !empty($values)) {
            $values = array_map(fn($value) => ['value' => $value], $values);
            $this->where_not_in_conditions[] = [
                'column' => $column_name,
                'values' => $values
            ];
        }

        if ($values instanceof MySQLSelectQueryBuilder) {
            $this->where_not_in_conditions[] = [
                'column' => $column_name,
                'values' => $values
            ];
        }

        return $this;
    }

    protected function getPreparedWherePart(): string
    {
        if (count($this->where_conditions) == 0 && count($this->where_not_in_conditions) == 0) {
            return '';
        }

        $where_array = [];
        $where_part = 'where ';
        foreach ($this->where_conditions as &$where_condition) {
            $where_array[] = $where_condition['column'] . ' = :' . $this->prepared_statement_index;
            $where_condition['prepared_statement_index'] = $this->prepared_statement_index;
            $this->prepared_statement_index++;
        }

        foreach ($this->where_not_in_conditions as &$where_not_in_condition) {
            if ($where_not_in_condition['values'] instanceof MySQLSelectQueryBuilder) {
                $where_array[] = $where_not_in_condition['column'] . ' not in (' . $where_not_in_condition['values']->getQuery($this->prepared_statement_index) . ')';
                $this->prepared_statement_index = $where_not_in_condition['values']->getPreparedStatementIndex();
            } else {
                $indexes = [];
                foreach ($where_not_in_condition['values'] as &$value) {
                    $value['prepared_statement_index'] = $this->prepared_statement_index;
                    $indexes[] = $this->prepared_statement_index;
                    $this->prepared_statement_index++;
                }
                $where_array[] = $where_not_in_condition['column'] . ' not in (:' . implode(', :', $indexes) . ')';
            }
        }
        $where_part .= implode(' and ', $where_array);

        return $where_part;
    }

    public function getPreparedWherePartStatements(): array
    {
        if (count($this->where_conditions) == 0 && count($this->where_not_in_conditions) == 0) {
            return [];
        }

        $prepared_statements = [];
        foreach ($this->where_conditions as $where_condition) {
            $parameter_name = ':' . $where_condition['prepared_statement_index'];
            $prepared_statements[$parameter_name] = $where_condition['filter_criterion'];
        }

        foreach ($this->where_not_in_conditions as $where_not_in_condition) {
            if ($where_not_in_condition['values'] instanceof MySQLSelectQueryBuilder) {
                $prepared_statements = array_merge($prepared_statements, $where_not_in_condition['values']->getPreparedWherePartStatements());
            } else {
                foreach ($where_not_in_condition['values'] as &$value) {
                    $parameter_name = ':' . $value['prepared_statement_index'];
                    $prepared_statements[$parameter_name] = $value['value'];
                }
            }
        }

        return $prepared_statements;
    }

    protected function getPreparedOrPart(): string
    {
        $empty_where_part = count($this->where_conditions) == 0 && count($this->where_not_in_conditions) == 0;
        $empty_or_part = count($this->or_conditions) == 0;
        if ($empty_where_part || $empty_or_part) {
            return '';
        }

        $or_array = [];
        $or_part = 'or ';
        foreach ($this->or_conditions as &$or_condition) {
            $or_array[] = $or_condition['column'] . ' = :' . $this->prepared_statement_index;
            $or_condition['prepared_statement_index'] = $this->prepared_statement_index;
            $this->prepared_statement_index++;
        }
        $or_part .= implode(' and ', $or_array);
        return $or_part;
    }

    public function getPreparedOrPartStatements(): array
    {
        if (count($this->or_conditions) == 0) {
            return [];
        }
        $prepared_statements = [];
        foreach ($this->or_conditions as $or_condition) {
            $parameter_name = ':' . $or_condition['prepared_statement_index'];
            $prepared_statements[$parameter_name] = $or_condition['filter_criterion'];
        }
        return $prepared_statements;
    }

    public function clearWhere(): void
    {
        $this->where_conditions = [];
        $this->where_not_in_conditions = [];
    }

    public function clearOr(): void
    {
        $this->or_conditions = [];
    }

    /**
     * @throws Exception
     */
    public function getQuery(int $prepared_statement_index = 0): string
    {
        $this->prepared_statement_index = $prepared_statement_index;
        $query_string = $this->getSelectPart();
        $query_string .= 'from '.$this->getTargetTable().' ';
        $query_string .= $this->getPreparedWherePart() . ' ';
        $query_string .= $this->getPreparedOrPart() . ' ';
        $query_string .= $this->getLimitPart();

        return $query_string;
    }

    /**
     * Generates the select part string of the query.
     * DOES NOT PREVENT SQL INJECTION! ONLY USE YOUR OWN VALUES!
     * @return string
     */
    public function getSelectPart(): string
    {
        return 'select ' . implode(', ', $this->select_columns) . ' ';
    }

    /**
     * @throws Exception
     */
    function execute(): array
    {
        if (empty($this->entity_class)) {
            throw new Exception("Tried to execute select query without Entity class being set.");
        }

        $query_string = $this->getQuery();
        $query = $this->PDO
            ->prepare($query_string);

        $prepared_where_statements = $this->getPreparedWherePartStatements();
        $prepared_or_statements = $this->getPreparedOrPartStatements();
        $prepared_statements = array_merge($prepared_where_statements, $prepared_or_statements);

        $query->execute($prepared_statements);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $hydrated_result = [];
        $entity_class = $this->entity_class;
        foreach ($result as $row) {
            $hydrated_result[] = new $entity_class($row);
        }

        $this->cleanUp();
        return $hydrated_result;
    }

    /**
     * @param int $limit_amount
     * @return MySQLSelectQueryBuilder
     */
    public function limit(int $limit_amount): MySQLSelectQueryBuilder
    {
        $this->limit_amount = $limit_amount;
        return $this;
    }

    private function getLimitPart(): string
    {
        if ($this->limit_amount == null) {
            return '';
        }

        return " limit $this->limit_amount";
    }

    public function getPreparedStatementIndex(): int
    {
        return $this->prepared_statement_index;
    }

    public function cleanUp(): void
    {
        $this->select_columns = ['*'];
        $this->clearWhere();
        $this->clearOr();
        $this->prepared_statement_index = 0;
    }
}
