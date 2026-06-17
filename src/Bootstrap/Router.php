<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Request\Request;
use Sparkframe\Tools\RouteToClassMethodMap;

final class Router
{
    /**
     * @var array<string, RouteToClassMethodMap[]>
     */
    private static array $routes;

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Extracts the routes for all controllers and makes them available.
     * @return void
     */
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
     * Maps the incoming request to a RouteToClassMethodMap.
     * @throws Exception
     */
    public static function routeToMethod(Request $request): RouteToClassMethodMap
    {
        $request_method = $request->getRequestMethod();
        $request_uri = explode('/', $request->getUri());

        if (!isset(self::$routes[$request_method])) {
            throw new Exception('Not found', 404);
        }

        foreach (self::$routes[$request_method] as $method_route) {
            if ($method_route->matchUri($request_uri)) {
                return $method_route;
            }
        }

        throw new Exception('Not found', 404);
    }
}
