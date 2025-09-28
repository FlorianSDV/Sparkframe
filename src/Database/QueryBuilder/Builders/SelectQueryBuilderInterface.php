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
}
