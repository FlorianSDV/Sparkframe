<?php

namespace Sparkframe\Model;

use Exception;
use Sparkframe\Bootstrap\Globals;
use Sparkframe\Database\DatabaseWrapper;
use Sparkframe\Database\QueryBuilder\DeleteQueryBuilder;
use Sparkframe\Database\QueryBuilder\InsertQueryBuilder;
use Sparkframe\Database\QueryBuilder\SelectQueryBuilder;
use Sparkframe\Database\QueryBuilder\UpdateQueryBuilder;

class Model
{
    protected ?DatabaseWrapper $database_wrapper = null;
    protected const string TABLE_NAME = '';
    
    public function __construct(protected string $entity_class, ?string $database_name = null)
    {
        $this->database_wrapper = null;
        if ($database_name !== null) {
            $this->database_wrapper = Globals::getDatabaseWrapper($database_name);
        }
    }

    private function assertReadyForQuery(): bool
    {
        $database_wrapper_correct = $this->database_wrapper instanceof DatabaseWrapper;
        $table_name_set = $this::TABLE_NAME !== '';
        return $database_wrapper_correct && $table_name_set;
    }

    /**
     * @throws Exception
     */
    public function selectQuery(): SelectQueryBuilder
    {
        if (!$this->assertReadyForQuery()){
            throw new Exception('Cannot create query without database connection');
        }
        
        return $this->database_wrapper->selectQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception
     */
    public function insertQuery(): InsertQueryBuilder
    {
        if (!$this->assertReadyForQuery()){
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->insertQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception
     */
    public function updateQuery(): UpdateQueryBuilder
    {
        if (!$this->assertReadyForQuery()){
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->updateQuery($this::TABLE_NAME, $this->entity_class);
    }

    public function deleteQuery(): DeleteQueryBuilder
    {
        if (!$this->assertReadyForQuery()){
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->deleteQuery($this::TABLE_NAME, $this->entity_class);
    }
}
