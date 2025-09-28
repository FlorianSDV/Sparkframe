<?php

declare(strict_types=1);

namespace Sparkframe\Request;

class Request
{
    private string $uri;
    private string $request_method;
    private string $request_body;
    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->request_body = file_get_contents('php://input');
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getRequestMethod(): string
    {
        return $this->request_method;
    }

    public function getRequestBody(): string
    {
        return $this->request_body;
    }
}