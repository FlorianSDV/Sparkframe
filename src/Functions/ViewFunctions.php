<?php

declare(strict_types=1);

namespace Sparkframe\Functions;

use Exception;
use Sparkframe\Bootstrap\Globals;

function view(string $view_name, array $data = []): void
{
    $view_path = Globals::getRootdir() . '/src/View/' . $view_name . '.php';

    if (!file_exists($view_path)) {
        throw new Exception('View not found');
    }
    extract($data);
    require $view_path;
}
