<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Exception;
use PDO;
use Sparkframe\Database\QueryBuilder\Builders\SelectQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Traits\QueryBuilderTrait;

class SQLiteSelectQueryBuilder implements SelectQueryBuilderInterface
{
    use QueryBuilderTrait;
    protected array $select_columns = ['*'];
    protected int|null $limit_amount = null;
    protected array $where_conditions = [];
    protected int $prepared_statement_index = 0;

    public function __construct(protected PDO $PDO, protected string $target_table_name, protected string $entity_class) { }

    public function select(string ...$column_names): SQLiteSelectQueryBuilder
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

    public function whereNotIn(string $column_name, SelectQueryBuilderInterface|array $values): self
    {
        return $this;
    }

    protected function getPreparedWherePart(): string
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

    protected function getPreparedWherePartStatements(): array
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

    public function clearWhere(): void
    {
        $this->where_conditions = [];
    }

    /**
     * @throws Exception
     */
    public function getQuery(): string
    {
        $query_string = $this->getSelectPart();
        $query_string .= 'from '.$this->getTargetTable().' ';
        $query_string .= $this->getPreparedWherePart();
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
        $select_string = 'select';
        foreach ($this->select_columns as $key => $select_column) {
            $select_string .= " $select_column";

            if (array_key_last($this->select_columns) == $key){
                break;
            }
            $select_string .= ', ';
        }

        return $select_string . ' ';
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
        $query->execute($this->getPreparedWherePartStatements());
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $hydrated_result = [];
        $entity_class = $this->entity_class;
        foreach ($result as $row) {
            $hydrated_result[] = new $entity_class($row);
        }


        $this->cleanUp();
        // Todo: implement hydration
        return $hydrated_result;
    }

    /**
     * @param int $limit_amount
     * @return SQLiteSelectQueryBuilder
     */
    public function limit(int $limit_amount): SQLiteSelectQueryBuilder
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
    
    public function setPreparedStatementIndex(int $prepared_statement_index): SQLiteSelectQueryBuilder
    {
        $this->prepared_statement_index = $prepared_statement_index;
        return $this;
    }

    public function getPreparedStatementIndex(): int
    {
        return $this->prepared_statement_index;
    }

    public function cleanUp(): void
    {
        $this->select_columns = ['*'];
        $this->clearWhere();
    }
}