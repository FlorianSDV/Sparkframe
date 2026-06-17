<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Dotenv\Dotenv;
use Exception;
use ReflectionClass;
use Sparkframe\Controller\Controller;
use Sparkframe\Database\DatabaseWrapperInterface;

final class Globals
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
        // use self because there is ever only one Globals.
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initializes all environment variables and sets the paths. Can only be run once.
     * @param string $root_dir The root directory of the project using Sparkframe.
     * @param string $controllers_dir The directory containing the controllers.
     */
    public function initialize(string $root_dir, string $controllers_dir): void
    {
        // Initialize once
        if (self::$initialized) {
            return;
        }

        self::$root_dir = $root_dir;
        self::$controllers_dir = $controllers_dir;
        $dotenv = Dotenv::createImmutable(self::$root_dir);
        $dotenv->load();

        self::$initialized = true;
    }

    /**
     * @return string The root directory of the project using Sparkframe.
     */
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

    /**
     * Initializes the controllers. Can only be run after setting the controllers dir.
     * @return void
     */
    public function initializeControllers(): void
    {
        if (!isset(self::$controllers_dir)) {
            throw new Exception('Cannot initialize controllers before setting controllers dir');
        }

        foreach (glob(self::$controllers_dir . DIRECTORY_SEPARATOR . '*.php') as $file) {
            $className = basename($file, '.php');

            $fullClass = 'App\\Controller\\' . $className;

            if (!class_exists($fullClass)) {
                throw new \RuntimeException("Class $fullClass not found. Is Composer autoloading configured correctly?");
            }

            if (new ReflectionClass($fullClass)->isAbstract()) {
                continue;
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
     * @throws Exception
     */
    public static function getController(string $controllerName): Controller
    {
        if (!isset(self::$controllers[$controllerName])) {
            throw new Exception("Controller $controllerName not found.");
        }

        return self::$controllers[$controllerName];
    }

    /**
     * Adds a database wrapper to the globals.
     * @param string $database_name The name of the database.
     * @param DatabaseWrapperInterface $databaseWrapper The database wrapper to add.
     */
    public static function addDatabaseWrapper(string $database_name, DatabaseWrapperInterface $databaseWrapper): void
    {
        self::$databases[$database_name] = $databaseWrapper;
    }

    /**
     * @throws Exception If the database does not exist in the globals.
     */
    public static function getDatabaseWrapper(string $database_name): ?DatabaseWrapperInterface
    {
        if (!isset(self::$databases[$database_name])) {
            throw new Exception("Database with name: $database_name not found!", 500);
        }
        return self::$databases[$database_name];
    }
}
