<?php

namespace Sparkframe\Request;

class Request
{
    private string $uri;
    private string $request_method;
    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->request_method = $_SERVER['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getRequestMethod(): string
    {
        return $this->request_method;
    }
}