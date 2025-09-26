<?php

namespace Sparkframe\Database\QueryBuilder;

use PDO;
use Sparkframe\Entity\Entity;

interface UpdateQueryBuilder extends QueryWithWhere, QueryWithEntities
{
}