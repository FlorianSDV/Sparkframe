<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Pdo\Sqlite;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\QueryBuilder\SQLite\SQLiteSelectQueryBuilder;
use Sparkframe\Database\SqliteDatabaseWrapper;
use Sparkframe\Tests\Mocks\Entities\NoteMockEntity;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

class SQLiteSelectQueryBuilderTest extends TestCase
{
    private SQLiteSelectQueryBuilder $sqlite_select_query_builder;
    private SqliteDatabaseWrapper $sqlite_database_wrapper;

    public function setUp(): void 
    {
        $this->sqlite_database_wrapper = new SqliteDatabaseWrapper($this->createStub(Sqlite::class));
        $this->sqlite_select_query_builder = $this->sqlite_database_wrapper->selectQuery('users', UserMockEntity::class);
    }

    /** 
     * Replace the placeholders in the query with actual values.
     * @return string The query with the values replaced.
    */
    private function getQueryWithValues(): string 
    {
        $query = $this->sqlite_select_query_builder->getQuery();
        $prepared_statements = $this->sqlite_select_query_builder->getPreparedStatements();

        foreach ($prepared_statements as $index => $value) {
            $query = str_replace($index, (string) $value, $query);
        }

        return $query;
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
            UserMockEntity::ID,
            UserMockEntity::NAME
        );

        $query = $this->sqlite_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testLimit(): void
    {
        $query = $this->sqlite_select_query_builder
            ->limit(10)
            ->getQuery();
        
        $expected_query = 'select * from users    limit 10';
        $this->assertEquals($expected_query, $query);
    }

    public function testWhere(): void
    {
        $this->sqlite_select_query_builder->where([UserMockEntity::ID . " = " => 1]);

        // Test raw
        $expected_query = 'select * from users where id =  :0  ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1  ';
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereWithAnd(): void
    {
        $this->sqlite_select_query_builder->where([
            UserMockEntity::ID . " = " => 1,
            UserMockEntity::NAME . " = " => "'John'"
        ]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 and name =  :1  ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id =  1 and name =  'John'  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereIn(): void
    {
        $this->sqlite_select_query_builder->whereIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"]);

        // Test raw
        $expected_query = 'select * from users where name in (:0, :1, :2)  ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where name in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }
    
    public function testWhereInWithAnd(): void
    {
        $this->sqlite_select_query_builder
            ->whereIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"])
            ->where([UserMockEntity::AGE . " > " => 20]);

        // Test raw
        $expected_query = 'select * from users where age >  :0 and name in (:1, :2, :3)  ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where age >  20 and name in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereInWithSubquery(): void
    {
        $sub_query = $this->sqlite_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . " = " => "'Groceries'"]);

        $this->sqlite_select_query_builder->whereIn(UserMockEntity::ID, $sub_query);

        // Test raw
        $expected_query = "select * from users where id in (select user_id from notes where title =  :0  )  ";
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where id in (select user_id from notes where title =  'Groceries'  )  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereInWithMultipleSubqueries(): void
    {
        $sub_query_1 = $this->sqlite_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . " = " => "'Groceries'"]);

        $sub_query_2 = $this->sqlite_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . " > " => 20]);

        $this->sqlite_select_query_builder
            ->whereIn(UserMockEntity::ID, $sub_query_1)
            ->whereIn(UserMockEntity::ID, $sub_query_2);

        // Test raw
        $expected_query = "select * from users where id in (select user_id from notes where title =  :0  ) and id in (select id from users where age >  :1  )  ";
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where id in (select user_id from notes where title =  'Groceries'  ) and id in (select id from users where age >  20  )  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotIn(): void
    {
        $this->sqlite_select_query_builder->whereNotIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"]);

        // Test raw
        $expected_query = 'select * from users where name not  in (:0, :1, :2)  ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where name not  in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }
    
    public function testWhereNotInWithAnd(): void
    {
        $this->sqlite_select_query_builder
            ->whereNotIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"])
            ->where([UserMockEntity::AGE . " > " => 20]);

        // Test raw
        $expected_query = 'select * from users where age >  :0 and name not  in (:1, :2, :3)  ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where age >  20 and name not  in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotInWithSubquery(): void
    {
        $sub_query = $this->sqlite_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . " = " => "'Groceries'"]);

        $this->sqlite_select_query_builder->whereNotIn(UserMockEntity::ID, $sub_query);

        // Test raw
        $expected_query = "select * from users where id not  in (select user_id from notes where title =  :0  )  ";
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where id not  in (select user_id from notes where title =  'Groceries'  )  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotInWithMultipleSubqueries(): void
    {
        $sub_query_1 = $this->sqlite_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . " = " => "'Groceries'"]);

        $sub_query_2 = $this->sqlite_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . " > " => 20]);

        $this->sqlite_select_query_builder
            ->whereNotIn(UserMockEntity::ID, $sub_query_1)
            ->whereNotIn(UserMockEntity::ID, $sub_query_2);

        // Test raw
        $expected_query = "select * from users where id not  in (select user_id from notes where title =  :0  ) and id not  in (select id from users where age >  :1  )  ";
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);
        
        // Test with values
        $expected_query = "select * from users where id not  in (select user_id from notes where title =  'Groceries'  ) and id not  in (select id from users where age >  20  )  ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testOr(): void
    {
        $this->sqlite_select_query_builder
            ->where([UserMockEntity::ID . " = " => 1])
            ->or([UserMockEntity::AGE . " > " => 20]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age >  :1 ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age >  20 ';
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testOrWithAnd(): void
    {
        $this->sqlite_select_query_builder
            ->where([UserMockEntity::ID . " = " => 1])
            ->or([
                UserMockEntity::AGE . " > " => 20,
                UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
            ]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age >  :1 and email_address :2 ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id =  1 or age >  20 and email_address 'example@test.com' ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testMultipleOrWithAnd(): void
    {
        $this->sqlite_select_query_builder
            ->where([UserMockEntity::ID . " = " => 1])
            ->or([
                UserMockEntity::AGE . " > " => 20,
                UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
            ])
            ->or([
                UserMockEntity::AGE . " > " => 30,
                UserMockEntity::EMAIL_ADDRESS => "'example_2@test.com'"
            ]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age >  :1 and email_address :2 or age >  :3 and email_address :4 ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id =  1 or age >  20 and email_address 'example@test.com' or age >  30 and email_address 'example_2@test.com' ";
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testOrIn(): void
    {
        $this->sqlite_select_query_builder
            ->where([UserMockEntity::ID . " = " => 1])
            ->orIn([UserMockEntity::AGE => [20, 30]]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age in (:1, :2) ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age in (20, 30) ';
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }

    public function testMultipleOrIn(): void
    {
        $this->sqlite_select_query_builder
            ->where([UserMockEntity::ID . " = " => 1])
            ->orIn([UserMockEntity::AGE => [20, 30]])
            ->orIn([UserMockEntity::ID => [2, 3]]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age in (:1, :2) or id in (:3, :4) ';
        $query = $this->sqlite_select_query_builder->getQuery();
        
        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age in (20, 30) or id in (2, 3) ';
        $query = $this->getQueryWithValues();
        
        $this->assertEquals($expected_query, $query);
    }
}
