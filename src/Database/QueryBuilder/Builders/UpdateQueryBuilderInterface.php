<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Builders;

use Sparkframe\Database\QueryBuilder\Composition\QueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Composition\QueryWithEntitiesInterface;

/**
 * Interface for building UPDATE queries.
 */
interface UpdateQueryBuilderInterface extends QueryBuilderInterface, QueryWithEntitiesInterface
{
}
