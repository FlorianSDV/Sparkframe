<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Entity\Entity;

interface QueryWithEntities
{
    /**
     * Adds an entity that will be inserted once the query is executed.
     * @param Entity $entity
     * @return self
     */
    function addEntity(Entity $entity): self;

    function clearEntities(): self;
}