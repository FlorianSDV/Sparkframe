<?php

namespace Sparkframe\Database;

abstract class BaseDatabaseInfo
{
    protected string $database_url;
    protected string $user;
    protected string $password;

    public function __construct($database_url, $user, $password){
        $this->database_url = $database_url;
        $this->user = $user;
        $this->password = $password;
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