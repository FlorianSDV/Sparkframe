<?php

namespace Sparkframe\Database;

use Sparkframe\Database\DataBaseConnection;
use SQLite3;

class Sqlite3DatabaseConnection implements DataBaseConnection
{
    public function __construct(private SQLite3 $SQLite3)
    {

    }
}