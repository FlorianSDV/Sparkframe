<?php

declare(strict_types=1);

namespace Sparkframe\Tools;

/**
 * The supported request methods for a Route.
 */
enum RequestMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
}
