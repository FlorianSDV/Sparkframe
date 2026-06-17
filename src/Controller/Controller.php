<?php

declare(strict_types=1);

namespace Sparkframe\Controller;

use ReflectionClass;
use Sparkframe\Attributes\Route;
use Sparkframe\Request\Request;
use Sparkframe\Tools\RouteToClassMethodMap;

use function Sparkframe\Functions\view;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Returns an array of RouteToClassMethodMaps for the controller.
     * @return array<string, RouteToClassMethodMap[]>
     */
    public function getRoutes(): array
    {
        $controller_routes = [];
        $reflection = new ReflectionClass(static::class);
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $routes = $method->getAttributes(name: Route::class);

            if (count($routes) === 0) {
                continue;
            }

            foreach ($routes as $route) {
                /**
                 * @var Route $route_instance
                 */
                $route_instance = $route->newInstance();

                $controller_routes[$route_instance->getRequestMethod()->value][] = new RouteToClassMethodMap(
                    $route_instance->getRoute(),
                    $method->class,
                    $method->name
                );
            }
        }

        return $controller_routes;
    }

    /**
     * Render a view with the given data.
     * @param string $view_name The name of the view to render.
     * @param array $data The data to pass to the view.
     */
    public function render(string $view_name, array $data = []): void
    {
        view($view_name, $data);
    }

    /**
     * Redirect to the given location.
     * @param string $location The location to redirect to.
     */
    protected function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
