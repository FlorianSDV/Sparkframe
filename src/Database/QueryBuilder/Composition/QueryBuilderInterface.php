<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use PDO;

/**
 * Base interface for all query builders.
 */
interface QueryBuilderInterface
{
    public function __construct(PDO $pdo, string $target_table_name, string $entity_class);

    /**
     * @return string Returns the target table name that will be used in the FROM clause of the query.
     */
    public function getTargetTable(): string;

    /**
     * Executes the query.
     * @return mixed|void The result of the query if the query returns a result.
     */
    public function execute();

    /**
     * Cleans up the query builder so it can be reused.
     */
    public function cleanUp(): void;
}
