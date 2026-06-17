<?php

declare(strict_types=1);

namespace Sparkframe\Database;

use PDO;
use Sparkframe\Database\QueryBuilder\Builders\DeleteQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\SelectQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\UpdateQueryBuilderInterface;
use Sparkframe\Entity\Entity;

/**
 * Interface DatabaseWrapperInterface
 * A DatabaseWrapperInterface provides all you need to interact with a database.
 * It provides a PDO instance and methods to create query builders.
 */
interface DatabaseWrapperInterface
{
    public function getPDO(): PDO;

    /** @param class-string<Entity> $entity_class */
    public function selectQuery(string $from_table_name, string $entity_class): SelectQueryBuilderInterface;

    /** @param class-string<Entity> $entity_class */
    public function insertQuery(string $insert_into_table_name, string $entity_class): InsertQueryBuilderInterface;

    /** @param class-string<Entity> $entity_class */
    public function updateQuery(string $update_table_name, string $entity_class): UpdateQueryBuilderInterface;

    /** @param class-string<Entity> $entity_class */
    public function deleteQuery(string $delete_from_table_name, string $entity_class): DeleteQueryBuilderInterface;
}
