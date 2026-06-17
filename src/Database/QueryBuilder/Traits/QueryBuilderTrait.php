<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Traits;

/**
 * Shared trait for query builders that target a database table.
 */
trait QueryBuilderTrait
{
    /**
     * @return string Returns the target table name that will be used in the FROM clause of the query.
     */
    public function getTargetTable(): string
    {
        return $this->target_table_name;
    }
}
