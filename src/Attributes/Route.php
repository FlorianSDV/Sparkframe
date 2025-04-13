<?php

namespace Sparkframe\Attributes;

use Attribute;

#[\Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(private string $route, private string $request_method)
    {

    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getRequestMethod(): string
    {
        return $this->request_method;
    }
}
