<?php

declare(strict_types=1);

function getFromSession(string $key): mixed
{
    return $_SESSION[$key];
}

function setInSession(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}
