<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Dotenv\Dotenv;
use Exception;
use ReflectionClass;
use Sparkframe\Controller\Controller;
use Sparkframe\Database\DatabaseWrapperInterface;

/**
 * Singleton holding application-wide configuration, controllers, and database wrappers.
 */
final class Globals
{
    private static Globals $instance;
    private static string $root_dir;
    private static string $controllers_dir;
    private static string $view_dir;

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
    public function initialize(string $root_dir, string $controllers_dir, ?string $view_dir = null): void
    {
        // Initialize once
        if (self::$initialized) {
            return;
        }

        self::$root_dir = $root_dir;
        self::$controllers_dir = $controllers_dir;
        self::$view_dir = $view_dir;
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

    public static function getViewDir(): string
    {
        return self::$view_dir;
    }

    protected function __clone(): void
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
            $class_name = basename($file, '.php');

            $full_class = 'App\\Controller\\' . $class_name;

            if (!class_exists($full_class)) {
                throw new \RuntimeException("Class $full_class not found. Is Composer autoloading configured correctly?");
            }

            if (new ReflectionClass($full_class)->isAbstract()) {
                continue;
            }

            $controller = new $full_class();
            // only allow controllers to be added.
            if (!($controller instanceof Controller)) {
                continue;
            }
            self::$controllers[$full_class] = $controller;
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
    public static function getController(string $controller_name): Controller
    {
        if (!isset(self::$controllers[$controller_name])) {
            throw new Exception("Controller $controller_name not found.");
        }

        return self::$controllers[$controller_name];
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
