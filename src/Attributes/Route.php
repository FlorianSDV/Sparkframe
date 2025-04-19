<?php

namespace Sparkframe\Attributes;

use Attribute;
use Sparkframe\Tools\RequestMethod;

#[\Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(private string $route, private RequestMethod $request_method)
    {
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getRequestMethod(): RequestMethod
    {
        return $this->request_method;
    }
}
