<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Database\BaseDatabaseInfoCollection;
use Sparkframe\Database\DatabaseWrapperFactory;

/**
 * Abstract base class for application bootstrapping.
 */
abstract class BaseBootstrapper
{
    protected static BaseBootstrapper $instance;
    protected static bool $bootstrapped = false;
    protected static bool $session_started = false;
    protected static bool $globals_initialized = false;

    protected function __construct()
    {
    }

    protected function __clone(): void
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize singleton');
    }

    public static function getInstance(): BaseBootstrapper
    {
        // Use static because this class will be inherited.
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Bootstraps the aplication. Can only be run once.
     * @param null|BaseDatabaseInfoCollection $baseDatabaseInfoCollection
     * @throws Exception
     * @return void
     */
    public function bootstrap(?BaseDatabaseInfoCollection $baseDatabaseInfoCollection = null): void
    {
        // Bootstrap the application once
        if (static::$bootstrapped) {
            return;
        }

        if (!static::$globals_initialized) {
            throw new Exception("Trying to bootstrap before globals are initialized.");
        }

        if ($baseDatabaseInfoCollection !== null) {
            $this->setupDatabaseWrappers($baseDatabaseInfoCollection);
        }
        $this->setupControllers();
        $this->setupRouter();

        static::$bootstrapped = true;
    }

    /**
     * Starts a session, can only be run once.
     * @return void
     */
    public function startSession(): void
    {
        // Start the session only once
        if (static::$session_started) {
            return;
        }

        session_start();

        static::$session_started = true;
    }

    /**
     * Initializes the environment variables, can only be run once.
     * @param string $root_dir
     * @param string $controllers_dir
     * @return void
     */

    public function initializeGlobals(string $root_dir, string $controllers_dir, ?string $view_dir = null): void
    {
        // Initialize globals only once
        if (static::$globals_initialized) {
            return;
        }

        $globals = Globals::getInstance();
        $globals->initialize($root_dir, $controllers_dir, $view_dir);

        static::$globals_initialized = true;
    }

    /**
     * Sets up the controllers, can only be run after initializing globals.
     * @throws Exception
     */
    protected function setupControllers(): void
    {
        if (!static::$globals_initialized) {
            throw new Exception("Not allowed to set up controllers before initializing globals.");
        }
        $globals = Globals::getInstance();
        $globals->initializeControllers();
    }
    /**
     * Sets up the database wrappers, can only be run after initializing globals.
     * @throws Exception
     */
    protected function setupDatabaseWrappers(BaseDatabaseInfoCollection $baseDatabaseInfoCollection): void
    {
        foreach ($baseDatabaseInfoCollection->getDatabaseInfoCollection() as $database_name => $baseDatabaseInfo) {
            $databaseWrapper = DatabaseWrapperFactory::createDatabaseWrapper($baseDatabaseInfo);
            Globals::addDatabaseWrapper($database_name, $databaseWrapper);
        }
    }

    /**
     * Sets up the router, can only be run after setting up controllers.
     * @return void
     */
    protected function setupRouter(): void
    {
        Router::setRoutes();
    }
}
