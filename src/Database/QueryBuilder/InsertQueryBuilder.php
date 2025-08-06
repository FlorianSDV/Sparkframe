<?php
declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Database\DataBaseConnection;
use Sparkframe\Entity\Entity;

interface InsertQueryBuilder
{
    public function __construct(DataBaseConnection $dataBaseConnection, string $target_table_name, string $entity_class);
    /**
     * Adds an entity that will be inserted once the query is executed.
     * @param Entity $entity
     * @return self
     */
    function addEntity(Entity $entity): self;

    function clearEntities(): self;
    function clearEntityClass(): self;
}
