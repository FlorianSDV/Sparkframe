<?php

declare(strict_types=1);

namespace Sparkframe\Exceptions;

use Exception;

/**
 * An exception thrown if a subquery does not have exactly one column in the SELECT clause.
 */
class IncorrectSubquerySelectException extends Exception
{
    public function __construct(string $query)
    {
        $message = 'Incorrect subquery. Make sure you select exactly one column! Query: ' . $query;
        parent::__construct($message);
    }
}
