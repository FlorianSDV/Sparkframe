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
use Sparkframe\Entity\Entity;

/**
 * A class representing a single table in the database. Can be used to provide querybuilders.
 */
class Model
{
    /**
    * @property DatabaseWrapperInterface $databaseWrapper The database wrapper that will be used to create the querybuilders.
    * The FROM clause of the Query will be automatically set to the table name of the Model.
    */
    protected DatabaseWrapperInterface $databaseWrapper;

    /** @property string $TABLE_NAME The name of the table that this class represents. */
    protected const string TABLE_NAME = '';

    /**
     * @param class-string<Entity> $entity_class The name of the entity class that belongs to this table.
     * @param string $database_name The name of the database to use. The database must exist in the globals.
     * @throws Exception If the database does not exist in the globals.
     */
    public function __construct(protected string $entity_class, string $database_name)
    {
        $this->databaseWrapper = Globals::getDatabaseWrapper($database_name);
    }


    /**
     * Check if the TABLE_NAME is set. Throw an Exception if that is not the case.
     * @throws Exception
     */
    private function assertReadyForQuery(): void
    {
        if ($this::TABLE_NAME === '') {
            $class_name = $this::class;
            throw new Exception('Cannot create querybuilder if TABLE_NAME on ' . $class_name . ' is not set. Add it as a property to the ' . $class_name . ' class.', 500);
        }
    }

    /**
     * @throws Exception If TABLE_NAME is not set.
     */
    public function selectQuery(): SelectQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->databaseWrapper->selectQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception If TABLE_NAME is not set.
     */
    public function insertQuery(): InsertQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->databaseWrapper->insertQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception If TABLE_NAME is not set.
     */
    public function updateQuery(): UpdateQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->databaseWrapper->updateQuery($this::TABLE_NAME, $this->entity_class);
    }

    /**
     * @throws Exception If TABLE_NAME is not set.
     */
    public function deleteQuery(): DeleteQueryBuilderInterface
    {
        $this->assertReadyForQuery();

        return $this->databaseWrapper->deleteQuery($this::TABLE_NAME, $this->entity_class);
    }
}
