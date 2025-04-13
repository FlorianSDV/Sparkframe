<?php

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Request\Request;

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

        foreach (Globals::getControllers() as $controller) {
            $controller_routes = $controller->getRoutes();
            if ($controller_routes === []) {
                continue;
            }
            self::$routes = array_merge_recursive(self::$routes, $controller_routes);
        }
    }

    /**
     * @throws Exception
     */
    public static function routeToMethod(Request $request): array
    {
        $request_method = $request->getRequestMethod();
        $request_uri = $request->getUri();

        if (!isset(self::$routes[$request_method][$request_uri])) {
            throw new Exception('404 not found');
        }

        return self::$routes[$request_method][$request_uri];
    }
}