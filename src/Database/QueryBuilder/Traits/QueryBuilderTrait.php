<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Traits;

trait QueryBuilderTrait
{
    public function getTargetTable(): string
    {
        return $this->target_table_name;
    }
}
