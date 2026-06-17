<?php

declare(strict_types=1);

namespace Sparkframe\Attributes;

use Attribute;

/**
 * Attribute that marks an entity property as the primary key column.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Primary extends Column
{
}
