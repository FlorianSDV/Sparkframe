<?php

declare(strict_types=1);

namespace Sparkframe\Database;

class DatabaseInfo
{
    public function __construct(protected string $database_url, protected string $user, protected string $password)
    {
    }

    public function getDatabaseUrl(): string
    {
        return $this->database_url;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
