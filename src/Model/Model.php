<?php

declare(strict_types=1);

namespace Sparkframe\Model;

use Exception;
use Sparkframe\Bootstrap\Globals;
use Sparkframe\Database\DatabaseWrapperInterface;
use Sparkframe\Database\QueryBuilder\Builders\DeleteQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\InsertQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\SelectQueryBuilderInterface;
use Sparkframe\Database\QueryBuilder\Builders\UpdateQueryBuilderInterface;

class Model
{
    protected ?DatabaseWrapperInterface $database_wrapper = null;
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
        $database_wrapper_correct = $this->database_wrapper instanceof DatabaseWrapperInterface;
        $table_name_set = $this::TABLE_NAME !== '';

        return $database_wrapper_correct && $table_name_set;
    }

    /**
     * @throws Exception
     */
    public function selectQuery(): SelectQueryBuilderInterface
    {
        if (!$this->assertReadyForQuery()) {
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->selectQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception
     */
    public function insertQuery(): InsertQueryBuilderInterface
    {
        if (!$this->assertReadyForQuery()) {
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->insertQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception
     */
    public function updateQuery(): UpdateQueryBuilderInterface
    {
        if (!$this->assertReadyForQuery()) {
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->updateQuery($this::TABLE_NAME, $this->entity_class);
    }

    public function deleteQuery(): DeleteQueryBuilderInterface
    {
        if (!$this->assertReadyForQuery()) {
            throw new Exception('Cannot create query without database connection');
        }

        return $this->database_wrapper->deleteQuery($this::TABLE_NAME, $this->entity_class);
    }
}
