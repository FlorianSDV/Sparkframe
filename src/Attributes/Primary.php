<?php

declare(strict_types=1);

namespace Sparkframe\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Primary extends Column
{
}
