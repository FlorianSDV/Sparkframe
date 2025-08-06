<?php

namespace Sparkframe\Model;

use Exception;
use Sparkframe\Bootstrap\Globals;
use Sparkframe\Database\DataBaseConnection;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;
use Sparkframe\Entity\Entity;

class Model
{
    protected ?DataBaseConnection $database_connection = null;
    protected const string TABLE_NAME = '';
    
    public function __construct(protected Entity $entity, ?string $database_name = null)
    {
        $this->database_connection = null;
        if ($database_name !== null) {
            $this->database_connection = Globals::getDatabaseConnection($database_name);
        }
    }

    private function assertReadyForQuery(): bool
    {
        $database_connection_correct = $this->database_connection instanceof DataBaseConnection;
        $table_name_set = $this::TABLE_NAME !== '';
        return $database_connection_correct && $table_name_set;
    }

    /**
     * @throws Exception
     */
    public function selectQuery(): SelectQueryBuilder
    {
        if (!$this->assertReadyForQuery()){
            throw new Exception('Cannot create query without database connection');
        }
        
        return $this->database_connection->selectQuery($this::TABLE_NAME);
    }

    /**
     * @throws Exception
     */
    public function insertQuery(): InsertQueryBuilder
    {
        if (!$this->assertReadyForQuery()){
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_connection->insertQuery($this::TABLE_NAME);
    }
}
