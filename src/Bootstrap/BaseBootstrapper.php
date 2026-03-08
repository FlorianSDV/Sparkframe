<?php

declare(strict_types=1);

namespace Sparkframe\Bootstrap;

use Exception;
use Sparkframe\Database\BaseDatabaseInfoCollection;
use Sparkframe\Database\DatabaseWrapperFactory;

abstract class BaseBootstrapper
{
    protected static BaseBootstrapper $instance;

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

    public function startSession(): void
    {
        session_start();
    }

    public function initializeGlobals(string $root_dir): void
    {
        // env variables
        // db connection strings
        $globals = Globals::getInstance();
        $globals->initialize($root_dir);
    }

    public function setupControllers(): void
    {
        $globals = Globals::getInstance();
        $globals->initializeControllers();
    }
    /**
     * @throws Exception
     */
    public function setupDatabaseWrappers(BaseDatabaseInfoCollection $baseDatabaseInfoCollection): void
    {
        foreach ($baseDatabaseInfoCollection->getDatabaseInfoCollection() as $database_name => $base_database_info) {
            $databaseWrapper = DatabaseWrapperFactory::createDatabaseWrapper($base_database_info);
            Globals::addDatabaseWrapper($database_name, $databaseWrapper);
        }
    }

    public function setupRouter(): void
    {
        Router::setRoutes();
    }
}
