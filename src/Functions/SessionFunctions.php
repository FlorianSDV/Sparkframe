<?php

declare(strict_types=1);

namespace Sparkframe\Functions;

/**
 * Retrieve a value stored in the current session.
 */
function getFromSession(string $key): mixed
{
    return $_SESSION[$key];
}

/**
 * Store a value in the current session.
 */
function setInSession(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}
