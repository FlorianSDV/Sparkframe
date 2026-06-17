<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Builders;

use Sparkframe\Database\QueryBuilder\Composition\QueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Composition\QueryWithEntitiesInterface;

/**
 * Interface for building INSERT queries.
 */
interface InsertQueryBuilderInterface extends QueryBuilderInterface, QueryWithEntitiesInterface
{
}
