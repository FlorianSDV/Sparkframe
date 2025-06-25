<?php

namespace Sparkframe\Database\QueryBuilder\SQLite;

use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;

class SQLiteSelectQueryBuilder extends SqliteQueryBuilder implements SelectQueryBuilder
{
    protected array $select_columns = ['*'];
    protected int|null $limit_amount = null;

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

    public function getQuery(): string
    {
        $query_string = $this->getSelectPart();
        $query_string .= $this->getFromPart();
        $query_string .= $this->getWherePart();
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

    function execute(): array
    {
        $query = $this->getQuery();
        return $this->dataBaseConnection->query($query)->fetchAll();
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
}