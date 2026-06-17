<?php

declare(strict_types=1);

namespace Sparkframe\Attributes;

use Attribute;
use Sparkframe\Tools\RequestMethod;

/**
 * Attribute that maps a controller method to an HTTP route.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
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
