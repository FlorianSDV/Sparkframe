<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\SQLite;

use Exception;
use Pdo\Mysql;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sparkframe\Database\MySQLDatabaseWrapper;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLSelectQueryBuilder;
use Sparkframe\Tests\Mocks\Entities\NoteMockEntity;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

class MySQLSelectQueryBuilderTest extends TestCase
{
    private MySQLSelectQueryBuilder $mysql_select_query_builder;
    private MySQLDatabaseWrapper $mysql_database_wrapper;

    public function setUp(): void
    {
        $this->mysql_database_wrapper = new MySQLDatabaseWrapper($this->createStub(Mysql::class));
        $this->mysql_select_query_builder = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class);
    }

    /**
     * Replace the placeholders in the query with actual values.
     * @return string The query with the values replaced.
    */
    private function getQueryWithValues(): string
    {
        $query = $this->mysql_select_query_builder->getQuery();
        $prepared_statements = $this->mysql_select_query_builder->getPreparedStatements();

        foreach ($prepared_statements as $index => $value) {
            $query = str_replace($index, (string) $value, $query);
        }

        return $query;
    }

    public function testSelectAll(): void
    {
        $expected_query = 'select * from users   ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testSelect(): void
    {
        $expected_query = 'select id, name from users   ';

        $this->mysql_select_query_builder->select(
            UserMockEntity::ID,
            UserMockEntity::NAME
        );

        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testLimit(): void
    {
        $expected_query = 'select * from users    limit 10';
        $query = $this->mysql_select_query_builder
            ->limit(10)
            ->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhere(): void
    {
        $this->mysql_select_query_builder->where([UserMockEntity::ID . ' = ' => 1]);

        // Test raw
        $expected_query = 'select * from users where id =  :0  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1  ';
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereWithAnd(): void
    {
        $this->mysql_select_query_builder->where([
            UserMockEntity::ID . ' = ' => 1,
            UserMockEntity::NAME . ' = ' => "'John'"
        ]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 and name =  :1  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id =  1 and name =  'John'  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereIn(): void
    {
        $this->mysql_select_query_builder->whereIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"]);

        // Test raw
        $expected_query = 'select * from users where name in (:0, :1, :2)  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where name in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereInWithAnd(): void
    {
        $this->mysql_select_query_builder
            ->whereIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"])
            ->where([UserMockEntity::AGE . ' > ' => 20]);

        // Test raw
        $expected_query = 'select * from users where age >  :0 and name in (:1, :2, :3)  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where age >  20 and name in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereInWithSubquery(): void
    {
        $sub_query = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

        $this->mysql_select_query_builder->whereIn(UserMockEntity::ID, $sub_query);

        // Test raw
        $expected_query = 'select * from users where id in (select user_id from notes where title =  :0  )  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id in (select user_id from notes where title =  'Groceries'  )  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereInWithMultipleSubqueries(): void
    {
        $sub_query_1 = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

        $sub_query_2 = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20]);

        $this->mysql_select_query_builder
            ->whereIn(UserMockEntity::ID, $sub_query_1)
            ->whereIn(UserMockEntity::ID, $sub_query_2);

        // Test raw
        $expected_query = 'select * from users where id in (select user_id from notes where title =  :0  ) and id in (select id from users where age >  :1  )  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id in (select user_id from notes where title =  'Groceries'  ) and id in (select id from users where age >  20  )  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotIn(): void
    {
        $this->mysql_select_query_builder->whereNotIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"]);

        // Test raw
        $expected_query = 'select * from users where name not  in (:0, :1, :2)  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where name not  in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotInWithAnd(): void
    {
        $this->mysql_select_query_builder
            ->whereNotIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"])
            ->where([UserMockEntity::AGE . ' > ' => 20]);

        // Test raw
        $expected_query = 'select * from users where age >  :0 and name not  in (:1, :2, :3)  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where age >  20 and name not  in ('John', 'Jane', 'Jim')  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotInWithSubquery(): void
    {
        $sub_query = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

        $this->mysql_select_query_builder->whereNotIn(UserMockEntity::ID, $sub_query);

        // Test raw
        $expected_query = 'select * from users where id not  in (select user_id from notes where title =  :0  )  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id not  in (select user_id from notes where title =  'Groceries'  )  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testWhereNotInWithMultipleSubqueries(): void
    {
        $sub_query_1 = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

        $sub_query_2 = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20]);

        $this->mysql_select_query_builder
            ->whereNotIn(UserMockEntity::ID, $sub_query_1)
            ->whereNotIn(UserMockEntity::ID, $sub_query_2);

        // Test raw
        $expected_query = 'select * from users where id not  in (select user_id from notes where title =  :0  ) and id not  in (select id from users where age >  :1  )  ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id not  in (select user_id from notes where title =  'Groceries'  ) and id not  in (select id from users where age >  20  )  ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testOr(): void
    {
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->or([UserMockEntity::AGE . ' > ' => 20]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age >  :1 ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age >  20 ';
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testOrWithAnd(): void
    {
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->or([
                UserMockEntity::AGE . ' > ' => 20,
                UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
            ]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age >  :1 and email_address :2 ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id =  1 or age >  20 and email_address 'example@test.com' ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testMultipleOrWithAnd(): void
    {
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->or([
                UserMockEntity::AGE . ' > ' => 20,
                UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
            ])
            ->or([
                UserMockEntity::AGE . ' > ' => 30,
                UserMockEntity::EMAIL_ADDRESS => "'example_2@test.com'"
            ]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age >  :1 and email_address :2 or age >  :3 and email_address :4 ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = "select * from users where id =  1 or age >  20 and email_address 'example@test.com' or age >  30 and email_address 'example_2@test.com' ";
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testOrIn(): void
    {
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->orIn([UserMockEntity::AGE => [20, 30]]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age in (:1, :2) ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age in (20, 30) ';
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testMultipleOrIn(): void
    {
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->orIn([UserMockEntity::AGE => [20, 30]])
            ->orIn([UserMockEntity::ID => [2, 3]]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age in (:1, :2) or id in (:3, :4) ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age in (20, 30) or id in (2, 3) ';
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public function testClearWhere(): void
    {
        $expected_query = 'select * from users   ';
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->clearWhere();

        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testClearOr(): void
    {
        $expected_query = 'select * from users where id =  :0  ';
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->or([UserMockEntity::AGE . ' > ' => 20])
            ->clearOr();

        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public static function getPreparedStatementIndexDataProvider(): array
    {
        return [
            'single where' => [
                'where_array' => [UserMockEntity::ID . ' = ' => 1],
                'expected_index' => 1
            ],
            'two wheres' => [
                'where_array' => [UserMockEntity::ID . ' = ' => 1, UserMockEntity::AGE . ' = ' => 30],
                'expected_index' => 2
            ],
        ];
    }

    #[DataProvider('getPreparedStatementIndexDataProvider')]
    public function testGetPreparedStatementIndex($where_array, $expected_index): void
    {
        $this->mysql_select_query_builder
            ->where($where_array)
            ->getQuery();

        $this->assertEquals($expected_index, $this->mysql_select_query_builder->getPreparedStatementIndex());
    }

    public function testGetQueryWithDifferentIndex(): void
    {
        $index = 10;
        $expected_index = $index + 1;
        $expected_query = "select * from users where id =  :$index  ";
        $query = $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->getQuery($index);

        $this->assertEquals($expected_query, $query);

        $index = $this->mysql_select_query_builder->getPreparedStatementIndex();

        $this->assertEquals($expected_index, $index);
    }

    public function testCleanUpp(): void
    {
        $expected_query = 'select * from users   ';
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->whereIn(UserMockEntity::AGE, [20, 30])
            ->or([UserMockEntity::AGE . ' > ' => 20])
            ->orIn([UserMockEntity::EMAIL_ADDRESS => ["'test@example.com'"]])
            ->cleanUp();

        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);
    }

    public function testReadyForSubQuery(): void
    {
        $this->mysql_select_query_builder->select(UserMockEntity::ID);
        $this->assertTrue($this->mysql_select_query_builder->readyForSubQuery());
    }

    public function testNotReadyForSubQuery(): void
    {
        $this->assertFalse($this->mysql_select_query_builder->readyForSubQuery());

        $this->mysql_select_query_builder->select(UserMockEntity::ID, UserMockEntity::NAME);
        $this->assertFalse($this->mysql_select_query_builder->readyForSubQuery());
    }

    public function testOrQueryFails(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot use or without where conditions!');

        $this->mysql_select_query_builder->or([UserMockEntity::ID => 1]);
    }

    public function testAllOptions(): void
    {
        $sub_query_1 = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

        $sub_query_2 = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20, UserMockEntity::PHONE_NUMBER => 123456789]);

        $sub_query_3 = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'To Do'"]);

        $sub_query_4 = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 60]);

        $query = $this->mysql_select_query_builder
            ->select(
                UserMockEntity::ID,
                UserMockEntity::NAME,
                UserMockEntity::EMAIL_ADDRESS,
                UserMockEntity::AGE,
                UserMockEntity::PHONE_NUMBER,
            )
            ->where([UserMockEntity::EMAIL_ADDRESS => "'test@example.com'"])
            ->where([
                UserMockEntity::ID . ' = ' => 1,
                UserMockEntity::NAME . ' = ' => "'John'"
            ])
            ->whereIn(UserMockEntity::NAME, ["'John'", "'Jane'", "'Jim'"])
            ->whereIn(UserMockEntity::ID, $sub_query_1)
            ->whereIn(UserMockEntity::ID, $sub_query_2)
            ->whereNotIn(UserMockEntity::NAME, ["'Tom'", "'Kevin'"])
            ->whereNotIn(UserMockEntity::ID, $sub_query_3)
            ->whereNotIn(UserMockEntity::ID, $sub_query_4)
            ->or([
                UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
            ])
            ->or([
                UserMockEntity::AGE . ' > ' => 30,
                UserMockEntity::EMAIL_ADDRESS => "'example_2@test.com'"
            ])
            ->orIn([UserMockEntity::AGE => [20, 30]])
            ->orIn([UserMockEntity::ID => [2, 3]])
            ->limit(1)
            ->getQuery();

        // Test raw
        $expected_query = 'select id, name, email_address, age, phone_number from users where email_address :0 and id =  :1 and name =  :2 and name in (:3, :4, :5) and id in (select user_id from notes where title =  :6  ) and id in (select id from users where age >  :7 and phone_number :8  ) and name not  in (:9, :10) and id not  in (select user_id from notes where title =  :11  ) and id not  in (select id from users where age >  :12  ) or email_address :13 or age >  :14 and email_address :15 or age in (:16, :17) or id in (:18, :19)  limit 1';
        $this->assertEquals($expected_query, $query);

        //Test with values
        $expected_query = "select id, name, email_address, age, phone_number from users where email_address 'test@example.com' and id =  1 and name =  'John' and name in ('John', 'Jane', 'Jim') and id in (select user_id from notes where title =  'Groceries'  ) and id in (select id from users where age >  20 and phone_number 123456789  ) and name not  in ('Tom', 10) and id not  in (select user_id from notes where title =  11  ) and id not  in (select id from users where age >  12  ) or email_address 13 or age >  14 and email_address 15 or age in (16, 17) or id in (18, 19)  limit 1";
        $query = $this->getQueryWithValues();
        $this->assertEquals($expected_query, $query);

        // Test prepared statement indexes
        $expected_query_index = 20;
        $query_index = $this->mysql_select_query_builder->getPreparedStatementIndex();
        $this->assertEquals($expected_query_index, $query_index);

        $expected_sub_query_1_index = 7;
        $sub_query_1_index = $sub_query_1->getPreparedStatementIndex();
        $this->assertEquals($expected_sub_query_1_index, $sub_query_1_index);

        $expected_sub_query_2_index = 9;
        $sub_query_2_index = $sub_query_2->getPreparedStatementIndex();
        $this->assertEquals($expected_sub_query_2_index, $sub_query_2_index);

        $expected_sub_query_3_index = 12;
        $sub_query_3_index = $sub_query_3->getPreparedStatementIndex();
        $this->assertEquals($expected_sub_query_3_index, $sub_query_3_index);

        $expected_sub_query_4_index = 13;
        $sub_query_4_index = $sub_query_4->getPreparedStatementIndex();
        $this->assertEquals($expected_sub_query_4_index, $sub_query_4_index);
    }
}
