<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Dotenv\Dotenv;
use Exception;
use Sparkframe\Controller\Controller;
use Sparkframe\Database\DatabaseWrapperInterface;

class Globals
{
    private static Globals $instance;
    private static string $root_dir;
    private static string $controllers_dir;

    /**
     * @var DatabaseWrapperInterface[]
     */
    private static array $databases;
    private static bool $initialized = false;

    /**
     * @var Controller[]
     */
    private static array $controllers = [];

    private function __construct()
    {
    }

    public static function getInstance(): Globals
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function initialize(string $root_dir, string $controllers_dir): void
    {
        // Initialize once
        if (static::$initialized) {
            return;
        }

        self::$root_dir = $root_dir;
        self::$controllers_dir = $controllers_dir;
        $dotenv = Dotenv::createImmutable(self::$root_dir);
        $dotenv->load();

        static::$initialized = true;
    }

    public static function getRootdir(): string
    {
        return self::$root_dir;
    }

    protected function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public function initializeControllers(): void
    {
        if (!isset(self::$controllers_dir)) {
            throw new Exception('Cannot initialize controllers before setting controllers dir');
        }

        foreach (glob(self::$controllers_dir . DIRECTORY_SEPARATOR . '*.php') as $file) {
            $className = basename($file, '.php');

            if ($className === 'BaseController') {
                continue;
            }

            $fullClass = 'App\\Controller\\' . $className;

            if (!class_exists($fullClass)) {
                throw new \RuntimeException("Class $fullClass not found. Is Composer autoloading configured correctly?");
            }

            $controller = new $fullClass();
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
        if (!isset(self::$controllers[$controllerName])) {
            throw new Exception("Controller $controllerName not found.");
        }

        return self::$controllers[$controllerName];
    }

    public static function addDatabaseWrapper(string $database_name, DatabaseWrapperInterface $databaseWrapper): void
    {
        static::$databases[$database_name] = $databaseWrapper;
    }

    public static function getDatabaseWrapper(string $database_name): ?DatabaseWrapperInterface
    {
        return static::$databases[$database_name];
    }
}
