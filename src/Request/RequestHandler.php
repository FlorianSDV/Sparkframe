<?php

namespace Sparkframe\Request;

use Exception;
use Sparkframe\Bootstrap\Globals;
use Sparkframe\Bootstrap\Router;
use Sparkframe\Controller\Controller;

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
        $info = Router::routeToMethod($this->request);
        
        $controller = Globals::getController($info['controller']);

        $method = $info['method'];
        return $controller->$method();
    }
}
