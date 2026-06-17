<?php

declare(strict_types=1);

namespace Sparkframe\Functions;

use Exception;
use Sparkframe\Bootstrap\Globals;

/**
 * Render a view file and pass data to it
 * @param string $view_name The filename of the view file. Do not pass the entire path.
 * @param array $data An array of key value pairs. These can be used in the view file as variables.
 */
function view(string $view_name, array $data = []): void
{
    $view_path = Globals::getViewDir() . DIRECTORY_SEPARATOR . $view_name . '.php';

    if (!file_exists($view_path)) {
        throw new Exception('View not found');
    }
    extract($data);
    require $view_path;
}
