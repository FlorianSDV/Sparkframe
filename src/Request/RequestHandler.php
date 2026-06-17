<?php

declare(strict_types=1);

namespace Sparkframe\Request;

use Exception;
use Sparkframe\Bootstrap\Globals;
use Sparkframe\Bootstrap\Router;

/**
 * Maps the incoming request to the correct Controller method.
 * @package Sparkframe\Request
 */
class RequestHandler
{
    private Request $request;
    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Map the incoming request to the correct Controller method.
     * @throws Exception
     */
    public function handle()
    {
        // Get the controller method belonging to this route.
        $routeToControllerMethodMap = Router::routeToMethod($this->request);

        // Fetch the correct controller, method name and variables from the uri.
        $controller = Globals::getController($routeToControllerMethodMap->getController());
        $method = $routeToControllerMethodMap->getMethodName();
        $variables = $routeToControllerMethodMap->getVariables();

        // Invoke the method with the correct variables.
        return $controller->$method(...$variables);
    }
}
