<?php

declare(strict_types=1);

namespace Sparkframe\Request;

class Request
{
    private string $uri;
    private string $request_method;
    private string $request_body;
    private array $request_post;
    private array $session;
    public function __construct()
    {
        session_start();
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->request_body = file_get_contents('php://input');
        $this->request_post = $_POST;
        $this->session = $_SESSION;
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

    public function getRequestPost(): array
    {
        return $this->request_post;
    }

    public function getSession(): array
    {
        return $this->session;
    }

    public function getFromSession(string $key): mixed
    {
        return $this->session[$key];
    }
}
