<?php

namespace Sparkframe\Database\QueryBuilder;

use Sparkframe\Entity\Entity;

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