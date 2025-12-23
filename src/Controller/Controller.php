<?php

declare(strict_types=1);
namespace Sparkframe\Controller;

use Sparkframe\Attributes\Route;
use Sparkframe\Request\Request;
use Sparkframe\Tools\MethodRoute;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
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

                $controller_routes[$new_method_route_instance->getRequestMethod()->value][] = new MethodRoute(
                    $new_method_route_instance->getRoute(),
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
}
