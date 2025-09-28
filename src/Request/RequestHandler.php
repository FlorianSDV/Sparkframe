<?php

declare(strict_types=1);

namespace Sparkframe\Request;

use Exception;
use Sparkframe\Bootstrap\Globals;
use Sparkframe\Bootstrap\Router;

class RequestHandler
{
    private Request $request;
    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        // Get the method belonging to this route.
        $method_route = Router::routeToMethod($this->request);

        // Fetch the correct controller, method name and variables from the uri.
        $controller = Globals::getController($method_route->getController());
        $method = $method_route->getMethodName();
        $variables = $method_route->getVariables();

        // Invoke the method with the correct variables.
        return $controller->$method(...$variables);
    }
}
