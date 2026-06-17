<?php

declare(strict_types=1);

namespace Sparkframe\Database;

/**
 * Abstract collection of database connection info keyed by database name.
 */
abstract class BaseDatabaseInfoCollection
{
    /**
     * @return array<string, DatabaseInfo>
     */
    protected array $database_info_collection;

    /**
     * @return array<string, DatabaseInfo>
     */
    public function getDatabaseInfoCollection(): array
    {
        return $this->database_info_collection;
    }
}
