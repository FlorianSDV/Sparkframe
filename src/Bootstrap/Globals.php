<?php

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Controller\Controller;

class Globals
{
    private static Globals $instance;
    private static string $root_dir;

    private Router $router;

    /**
     * @var Controller[]
     */
    private static array $controllers = [];

    private function __construct() {}

    public static function getInstance(): Globals
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function initialize(string $root_dir): void
    {
        self::$root_dir = $root_dir;
        $this->loadEnv();
        $this->initializeControllers();
    }

    public static function getRootdir(): string
    {
        return self::$root_dir;
    }

    protected function __clone() {}

    public function __wakeup(): void
    {
        throw new Exception("Cannot unserialize singleton");
    }

    private function initializeControllers(): void
    {
        $controllers_dir = self::$root_dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller';

        foreach (glob($controllers_dir . DIRECTORY_SEPARATOR . '*.php') as $file) {
            $className = basename($file, '.php');

            if ($className === 'BaseController') {
                continue;
            }

            $fullClass = 'App\\Controller\\' . $className;

            if (!class_exists($fullClass)) {
                throw new \RuntimeException("Class $fullClass not found. Composer autoloading correct ingesteld?");
            }

            $controller = new $fullClass;
            // only allow controllers to be added.
            if (!($controller instanceof Controller)) {
                continue;
            }
            self::$controllers[$fullClass] = $controller;
        }
    }

    /**
     * @return Controller[]
     */
    public static function getControllers(): array
    {
        return self::$controllers;
    }

    /**
     * @param string $controllerName
     * @return Controller
     * @throws Exception
     */
    public static function getController(string $controllerName): Controller
    {
        if (!isset(self::$controllers[$controllerName])){
            throw new Exception("Controller $controllerName not found.");
        }

        return self::$controllers[$controllerName];
    }

    private function loadEnv(): void
    {
        $env_filepath = self::$root_dir . DIRECTORY_SEPARATOR . '.env';

        if (is_readable($env_filepath)) {
            $lines = file($env_filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '/')) {
                    continue;
                }

                putenv($line);

                [$key, $value] = explode('=', $line, 2);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

}
