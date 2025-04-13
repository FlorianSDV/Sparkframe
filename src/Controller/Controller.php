<?php

namespace Sparkframe\Controller;

use Sparkframe\Attributes\Route;
use Sparkframe\Model\Model;

abstract class Controller
{
    // een controller moet een model hebben
//    protected Model $model;
//
//    public function __construct(Model $model)
//    {
//        $this->model = $model;
//    }

//todo: zorg dat de controller de request bevat
    public function __construct()
    {
        //todo: een controller heeft toegang nodig tot de request.
        // Dus de request moet al bestaan in de globals.
    }


    /**
     * Method to check if a class is a controller
     * @return bool
     */
    public function controllerMethod(): bool
    {
        return true;
    }

    public function getRoutes(): array
    {
        $controller_routes = [];
        $reflection = new \ReflectionClass(static::class);
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $method_routes = $method->getAttributes(name: Route::class);
            if (count($method_routes) === 0) {
                continue;
            }

            foreach ($method_routes as $method_route) {
                /**
                 * @var Route $new_method_route_instance
                 */
                $new_method_route_instance = $method_route->newInstance();
                $controller_routes[$new_method_route_instance->getRequestMethod()][$new_method_route_instance->getRoute()] = [
                    'controller' => $method->class,
                    'method' => $method->name
                ];
            }
        }

        return $controller_routes;
    }
}
