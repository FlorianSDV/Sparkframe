<?php

declare(strict_types=1);

namespace Sparkframe\Database;

abstract class BaseDatabaseInfoCollection
{
    /**
     * @var DatabaseInfo[]
     */
    protected array $database_info_collection;

    /**
     * @return DatabaseInfo[]
     */
    public function getDatabaseInfoCollection(): array
    {
        return $this->database_info_collection;
    }
}
