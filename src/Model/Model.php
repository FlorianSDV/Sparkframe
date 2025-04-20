<?php

namespace Sparkframe\Model;

use Sparkframe\Bootstrap\Globals;
use Sparkframe\Database\DataBaseConnection;
use Sparkframe\Entity\Entity;

class Model
{
    protected ?DataBaseConnection $database_connection = null;
    public function __construct(protected Entity $entity, ?string $database_name = null)
    {
        $this->database_connection = null;
        if ($database_name !== null) {
            $this->database_connection = Globals::getDatabaseConnection($database_name);
        }
    }
}
