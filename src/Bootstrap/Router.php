<?php

namespace Sparkframe\Bootstrap;

class Router
{
    private static array $routes;

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function setRoutes(): void
    {
        self::$routes = [];
    }

}