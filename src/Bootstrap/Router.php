<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Request\Request;
use Sparkframe\Tools\MethodRoute;

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
    public static function routeToMethod(Request $request): MethodRoute
    {
        $request_method = $request->getRequestMethod();
        $request_uri = explode('/', $request->getUri());

        if (!isset(self::$routes[$request_method])) {
            throw new Exception('404 not found');
        }

        foreach (self::$routes[$request_method] as $method_route) {
            /**
             * @var MethodRoute $method_route
             */
            if ($method_route->matchUri($request_uri)) {
                return $method_route;
            }
        }

        throw new Exception('404 not found');
    }
}