<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Builders;

use Sparkframe\Database\QueryBuilder\Composition\QueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Composition\QueryWithEntitiesInterface;

interface UpdateQueryBuilderInterface extends QueryBuilderInterface, QueryWithEntitiesInterface
{
}
