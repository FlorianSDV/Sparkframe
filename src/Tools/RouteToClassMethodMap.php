<?php

declare(strict_types=1);

namespace Sparkframe\Tools;

/**
 * An object containing a uri and the class method it belongs to.
 * @package Sparkframe\Tools
 */
class RouteToClassMethodMap
{
    /**
     * @var string[]
     */
    private array $uri;

    /**
     * @var string The complete uri
     */
    private string $uri_string;

    /**
     * @var int|string[]
     */
    private array $variables = [];

    /**
     * @param class-string $controller The class string of the controller.
     */
    public function __construct(
        string $uri,
        private readonly string $controller,
        private readonly string $method_name
    ) {
        $this->uri = explode('/', $uri);
        $this->uri_string = $uri;
    }

    public function getUriString(): string
    {
        return $this->uri_string;
    }

    /**
     * @return class-string The class string of the controller.
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string The name of the Controller method.
     */
    public function getMethodName(): string
    {
        return $this->method_name;
    }

    /**
     * @return string[] The variable placeholders in the uri.
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param string[] $incoming_request_uri
     */
    public function matchUri(array $incoming_request_uri): bool
    {
        if ($this->uri === $incoming_request_uri) {
            return true;
        }

        $this_uri_count = count($this->uri) - 1;
        $incoming_request_uri_count = count($incoming_request_uri) - 1;

        if ($this_uri_count > $incoming_request_uri_count) {
            return false;
        }

        $this->variables = [];

        for ($i = 0; $i <= $this_uri_count; $i++) {
            $this_uri_step = $this->uri[$i];

            if ($this_uri_step === WILDCARD_ROUTE_PROPERTY) {
                return true;
            }

            $at_last_step = $this_uri_count === $i && $incoming_request_uri_count === $i;
            $incoming_request_uri_step = $incoming_request_uri[$i];

            if ($this_uri_step === INT_ROUTE_PROPERTY && is_numeric($incoming_request_uri_step)) {
                $this->variables[] = intval($incoming_request_uri_step);

                if ($at_last_step) {
                    return true;
                }
                continue;
            }

            if ($this_uri_step === STR_ROUTE_PROPERTY) {
                $this->variables[] = $incoming_request_uri_step;

                if ($at_last_step) {
                    return true;
                }
                continue;
            }

            if ($this_uri_step !== $incoming_request_uri_step) {
                return false;
            }

            if ($at_last_step) {
                return true;
            }
        }

        return false;
    }
}
