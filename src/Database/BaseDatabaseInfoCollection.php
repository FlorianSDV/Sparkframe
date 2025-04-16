<?php

namespace Sparkframe\Database;

abstract class BaseDatabaseInfoCollection
{
    /**
     * @var BaseDatabaseInfo[]
     */
    protected array $database_info_collection;

    public function getDatabaseInfoCollection(): array
    {
        return $this->database_info_collection;
    }
}
