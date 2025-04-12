<?php

namespace Sparkframe\Attributes;

use Attribute;

#[\Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(string $route, string $method)
    {

    }
}
