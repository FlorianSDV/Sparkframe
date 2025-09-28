<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Composition;

use Sparkframe\Entity\Entity;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\Builders\UpdateQueryBuilder;
use Sparkframe\Database\QueryBuilder\Builders\DeleteQueryBuilder;

interface QueryWithEntities
{
    /**
     * Adds an entity that will be inserted once the query is executed.
     * @param Entity $entity
     * @return InsertQueryBuilder | UpdateQueryBuilder | DeleteQueryBuilder
     */
    function addEntity(Entity $entity): InsertQueryBuilder | UpdateQueryBuilder | DeleteQueryBuilder;

    function clearEntities(): InsertQueryBuilder | UpdateQueryBuilder | DeleteQueryBuilder;
}