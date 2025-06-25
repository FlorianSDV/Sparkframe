<?php

namespace Sparkframe\Database\QueryBuilder;

interface SelectQueryBuilder
{
    /**
     * @param string ...$column_names any amount of column names
     * @return self
     */
    public function select(string ...$column_names): self;
}
