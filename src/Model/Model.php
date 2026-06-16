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
    protected DatabaseWrapperInterface $database_wrapper;
    protected const string TABLE_NAME = '';

    /**
     * @return void
     */
    public function __construct(protected string $entity_class, ?string $database_name = null)
    {
        $this->database_wrapper = Globals::getDatabaseWrapper($database_name);
    }


    private function assertReadyForQuery(): void
    {
        if ($this::TABLE_NAME === '') {
            throw new Exception('Cannot create querybuilder if TABLE_NAME on ' . $this::class . ' is not set.', 500);
        }
    }

    /**
     * @throws Exception
     */
    public function selectQuery(): SelectQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->database_wrapper->selectQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception
     */
    public function insertQuery(): InsertQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->database_wrapper->insertQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception
     */
    public function updateQuery(): UpdateQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->database_wrapper->updateQuery($this::TABLE_NAME, $this->entity_class);
    }

    public function deleteQuery(): DeleteQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->database_wrapper->deleteQuery($this::TABLE_NAME, $this->entity_class);
    }
}
