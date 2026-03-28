<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use Sparkframe\Entity\Entity;

interface QueryWithEntitiesInterface
{
    /**
     * Adds an entity to the query.
     */
    public function addEntity(Entity $entity): static;

    public function clearEntities(): void;
}
