<?php
declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;

trait SQLiteWhereQueryTrait
{
    protected array $where_conditions = [];

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


    public function getPreparedWherePart(): string
    {
        if (count($this->where_conditions) == 0) {
            return '';
        }

        $where_part = 'where';
        foreach ($this->where_conditions as $key => $where_condition) {
            $where_part .= " $where_condition[column] = :$where_condition[column]";

            if (array_key_last($this->where_conditions) == $key) {
                break;
            }

            $where_part .= ' and';
        }

        return $where_part;
    }

    public function getPreparedWherePartStatements(): array
    {
        if (count($this->where_conditions) == 0) {
            return [];
        }

        $prepared_statements = [];
        foreach ($this->where_conditions as $where_condition) {
            $prepared_statements[$where_condition['column']] = $where_condition['filter_criterion'];
        }

        return $prepared_statements;
    }
}
