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
     * Adds an entity that will be inserted once the query is executed.
     */
    public function addEntity(Entity $entity): InsertQueryBuilderInterface | UpdateQueryBuilderInterface | DeleteQueryBuilderInterface;

    public function clearEntities(): InsertQueryBuilderInterface | UpdateQueryBuilderInterface | DeleteQueryBuilderInterface;
}
