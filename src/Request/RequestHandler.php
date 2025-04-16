<?php

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
        $method_route = Router::routeToMethod($this->request);
        
        $controller = Globals::getController($method_route->getController());

        $method = $method_route->getMethodName();
        return $controller->$method();
    }
}
