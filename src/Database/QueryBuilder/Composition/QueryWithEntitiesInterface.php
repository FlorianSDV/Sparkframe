<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use Sparkframe\Entity\Entity;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\UpdateQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\DeleteQueryBuilderInterface;

interface QueryWithEntitiesInterface
{
    /**
     * Adds an entity that will be inserted once the query is executed.
     * @param Entity $entity
     * @return InsertQueryBuilderInterface | UpdateQueryBuilderInterface | DeleteQueryBuilderInterface
     */
    function addEntity(Entity $entity): InsertQueryBuilderInterface | UpdateQueryBuilderInterface | DeleteQueryBuilderInterface;

    function clearEntities(): InsertQueryBuilderInterface | UpdateQueryBuilderInterface | DeleteQueryBuilderInterface;
}
