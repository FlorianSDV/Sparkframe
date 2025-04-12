<?php

namespace Sparkframe\Bootstrap;

use Exception;

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
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): BaseBootstrapper
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function bootstrap(string $root_dir): void
    {
        $this->initializeGlobals($root_dir);
        $this->setupDatabaseConnections();
        $this->setupRouter();
    }
    
    protected function initializeGlobals(string $root_dir): void
    {
        // env variables
        // db connection strings
        $globals = Globals::getInstance();
        $globals->initialize($root_dir);
    }

    protected function setupDatabaseConnections(): void
    {

    }

    private function setupRouter(): void
    {
        Router::setRoutes();
    }
}
