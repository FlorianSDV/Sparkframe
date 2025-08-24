<?php

namespace Sparkframe\Database\QueryBuilder;

interface SelectQueryBuilder extends QueryWithWhere
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
}
