<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use Sparkframe\Database\QueryBuilder\Builders\DeleteQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\UpdateQueryBuilderInterface;
use Sparkframe\Entity\Entity;

interface QueryWithEntitiesInterface
{
    /**
     * Adds an entity to the query.
     */
    public function addEntity(Entity $entity): QueryWithEntitiesInterface;

    public function clearEntities(): void;
}
