<?php

declare(strict_types=1);

namespace Sparkframe\Attributes;

use Attribute;

/**
 * Attribute that marks an entity property as a database column.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
}
