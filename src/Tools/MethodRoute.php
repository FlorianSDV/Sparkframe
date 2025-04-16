<?php

namespace Sparkframe\Tools;

class MethodRoute
{
    /**
     * @var string[] 
     */
    private array $uri;
    public function __construct(
        string                  $uri,
        private readonly string $controller,
        private readonly string $method_name
    )
    {
        $this->uri = explode('/', $uri);
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getMethodName(): string
    {
        return $this->method_name;
    }

    /**
     * @param string[] $incoming_request_uri
     * @return bool
     */
    public function matchUri(array $incoming_request_uri): bool
    {
        if ($this->uri === $incoming_request_uri){
            return true;
        }
        return false;
    }
}
