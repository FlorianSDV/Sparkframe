<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder;

use PDO;

interface QueryBuilder
{
    public function __construct(PDO $PDO, string $target_table_name, string $entity_class);

    public function getTargetTable(): string;

    public function execute();

    public function cleanUp(): void;
}