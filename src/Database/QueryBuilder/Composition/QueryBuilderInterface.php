<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use PDO;

interface QueryBuilderInterface
{
    public function __construct(PDO $PDO, string $target_table_name, string $entity_class);

    /**
     * @return string Returns the target table name that will be used in the FROM clause of the query.
     */
    public function getTargetTable(): string;

    /**
     * Executes the query.
     */
    public function execute();

    /**
     * Cleans up the query builder so it can be reused.
     */
    public function cleanUp(): void;
}
