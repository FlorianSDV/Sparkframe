<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use Sparkframe\Entity\Entity;

/**
 * Interface for query builders that operate on entity instances.
 */
interface QueryWithEntitiesInterface
{
    /**
     * Adds an entity to the query.
     */
    public function addEntity(Entity $entity): static;

    /**
     * Adds multiple entities to the query.
     * @param $entities Entity[]
     */
    public function addEntities(array $entities): static;

    /**
     * Clears the entities from the query.
     */
    public function clearEntities(): void;
}
