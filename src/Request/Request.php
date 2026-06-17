<?php

declare(strict_types=1);

namespace Sparkframe\Request;

/**
 * The incoming http request
 */
class Request
{
    private string $uri;
    private string $request_method;
    private string $request_body;
    private array $request_post;
    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->request_body = file_get_contents('php://input');
        $this->request_post = $_POST;
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

    /**
     * @return array The post body
     */
    public function getRequestPost(): array
    {
        return $this->request_post;
    }
}
