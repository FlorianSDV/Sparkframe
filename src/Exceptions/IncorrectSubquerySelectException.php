<?php

declare(strict_types=1);

namespace Sparkframe\Exceptions;

use Exception;

class IncorrectSubquerySelectException extends Exception
{
    public function __construct(string $query)
    {
        $message = "Incorrect subquery. Make sure you select exactly one column! Query: " . $query;
        parent::__construct($message);
    }
}
