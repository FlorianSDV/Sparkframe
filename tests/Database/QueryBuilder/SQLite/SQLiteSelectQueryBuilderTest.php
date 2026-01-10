<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Sqlite;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteSelectQueryBuilder;
use Sparkframe\Database\SqliteDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\MockEntity;

class SQLiteSelectQueryBuilderTest extends TestCase
{
    private SQLiteSelectQueryBuilder $sqlite_select_query_builder;

    public function setUp(): void 
    {
        $this->sqlite_select_query_builder = new SqliteDatabaseWrapper($this->createStub(Sqlite::class))
            ->selectQuery('users', MockEntity::class);    
    }

    public function testSelectAll(): void
    {
        $expected_query = 'select * from users   ';
        $query = $this->sqlite_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testSelect(): void
    {    
        $expected_query = 'select id, name from users   ';

        $this->sqlite_select_query_builder->select(
            MockEntity::ID,
            MockEntity::NAME
        );

        $query = $this->sqlite_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }
}
