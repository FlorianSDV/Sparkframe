<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Database\BaseDatabaseInfoCollection;
use Sparkframe\Database\DatabaseWrapperFactory;

abstract class BaseBootstrapper
{
    protected static BaseBootstrapper $instance;
    protected static bool $bootstrapped = false;
    protected static bool $session_started = false;
    protected static bool $globals_initialized = false;

    protected function __construct()
    {
    }

    protected function __clone()
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
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
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

    public function startSession(): void
    {
        // Start the session only once
        if (static::$session_started) {
            return;
        }

        session_start();

        static::$session_started = true;
    }

    public function initializeGlobals(string $root_dir, string $controllers_dir): void
    {
        // Initialize globals only once
        if (static::$globals_initialized) {
            return;
        }

        // env variables
        // db connection strings
        $globals = Globals::getInstance();
        $globals->initialize($root_dir, $controllers_dir);

        static::$globals_initialized = true;
    }

    protected function setupControllers(): void
    {
        if (!static::$globals_initialized) {
            throw new Exception("Not allowed to set up controllers before initializing globals.");
        }
        $globals = Globals::getInstance();
        $globals->initializeControllers();
    }
    /**
     * @throws Exception
     */
    protected function setupDatabaseWrappers(BaseDatabaseInfoCollection $baseDatabaseInfoCollection): void
    {
        foreach ($baseDatabaseInfoCollection->getDatabaseInfoCollection() as $database_name => $base_database_info) {
            $databaseWrapper = DatabaseWrapperFactory::createDatabaseWrapper($base_database_info);
            Globals::addDatabaseWrapper($database_name, $databaseWrapper);
        }
    }

    protected function setupRouter(): void
    {
        Router::setRoutes();
    }
}
