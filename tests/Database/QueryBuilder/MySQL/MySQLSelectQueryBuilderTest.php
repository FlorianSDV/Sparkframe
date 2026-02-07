<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Database\QueryBuilder\Mysql;

use Exception;
use Pdo\Mysql;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Sparkframe\Database\MySQLDatabaseWrapper;
use Sparkframe\Database\QueryBuilder\MySQL\MySQLSelectQueryBuilder;
use Sparkframe\Tests\Mocks\Entities\NoteMockEntity;
use Sparkframe\Tests\Mocks\Entities\UserMockEntity;

class MySQLSelectQueryBuilderTest extends TestCase
{
    private MySQLSelectQueryBuilder $mysql_select_query_builder;
    private MySQLDatabaseWrapper $mysql_database_wrapper;
    private ReflectionMethod $addOrInMethodReflection;
    private ReflectionMethod $addWhereInMethodReflection;
    private ReflectionProperty $orInConditionsReflection;
    private ReflectionProperty $whereInConditionsReflection;

    public function setUp(): void
    {
        $this->mysql_database_wrapper = new MySQLDatabaseWrapper($this->createStub(Mysql::class));
        $this->mysql_select_query_builder = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class);

        $this->addOrInMethodReflection = new ReflectionMethod(
            $this->mysql_select_query_builder,
            'addOrIn'
        );
        $this->addWhereInMethodReflection = new ReflectionMethod(
            $this->mysql_select_query_builder,
            'addWhereIn'
        );

        $this->orInConditionsReflection = new ReflectionProperty(
            $this->mysql_select_query_builder,
            'or_in_conditions'
        );
        $this->whereInConditionsReflection = new ReflectionProperty(
            $this->mysql_select_query_builder,
            'where_in_conditions'
        );
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

    public static function selectDataProvider(): array
    {
        return [
            'Select all' => [
                'column_names' => [],
                'expected_query' => 'select * from users   '
            ],
            'Select specific' => [
                'column_names' => [
                    UserMockEntity::ID,
                    UserMockEntity::NAME
                ],
                'expected_query' => 'select id, name from users   '
            ],
        ];
    }

    #[DataProvider('selectDataProvider')]
    public function testSelect($column_names, $expected_query): void
    {
        $query = $this->mysql_select_query_builder
            ->select(...$column_names)
            ->getQuery();

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

    public static function whereDataProvider(): array
    {
        return [
            'Test where' => [
                'where' => [UserMockEntity::ID . ' = ' => 1],
                'expected_query' => 'select * from users where id =  :0  ',
                'expected_query_with_values' => 'select * from users where id =  1  ',
            ],
            'Test where with and' => [
                'where' => [
                    UserMockEntity::ID . ' = ' => 1,
                    UserMockEntity::NAME . ' = ' => "'John'"
                ],
                'expected_query' => 'select * from users where id =  :0 and name =  :1  ',
                'expected_query_with_values' => "select * from users where id =  1 and name =  'John'  ",
            ]
        ];
    }

    #[DataProvider('whereDataProvider')]
    public function testWhere($where, $expected_query, $expected_query_with_values): void
    {
        $this->mysql_select_query_builder->where($where);

        // Test raw
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query_with_values, $query);
    }

    public static function whereInDataProvider(): array
    {
        // These subqueries are wrapped in functions so they are only created during the test and not before
        $sub_query_1_fn = function () {
            $sub_query_1 = new MySQLDatabaseWrapper(static::createStub(Mysql::class))
                ->selectQuery('notes', NoteMockEntity::class)
                ->select(NoteMockEntity::USER_ID)
                ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);
            return ['column_name' => UserMockEntity::ID, 'values' => $sub_query_1];
        };

        $sub_query_2_fn = function () {
            $sub_query_2 = new MySQLDatabaseWrapper(static::createStub(Mysql::class))
                ->selectQuery('users', UserMockEntity::class)
                ->select(UserMockEntity::ID)
                ->where([UserMockEntity::AGE . ' > ' => 20]);
            return ['column_name' => UserMockEntity::ID, 'values' => $sub_query_2];
        };

        $where_ins_array_fn = function () {
            return [['column_name' => UserMockEntity::NAME, 'values' => ["'John'", "'Jane'", "'Jim'"]]];
        };

        $where_in_with_and = function () use ($sub_query_1_fn) {
            return [$sub_query_1_fn()];
        };

        $where_in_with_multiple_subqueries = function () use ($sub_query_1_fn, $sub_query_2_fn) {
            return [$sub_query_1_fn(), $sub_query_2_fn()];
        };

        return [
            'Single where in' => [
                'where' => [],
                'where_ins' => $where_ins_array_fn,
                'expected_query' => 'select * from users where name in (:0, :1, :2)  ',
                'expected_query_with_values' => "select * from users where name in ('John', 'Jane', 'Jim')  "
            ],
            'Where in with and' => [
                'where' => [UserMockEntity::AGE . ' > ' => 20],
                'where_ins' => $where_ins_array_fn,
                'expected_query' => 'select * from users where age >  :0 and name in (:1, :2, :3)  ',
                'expected_query_with_values' => "select * from users where age >  20 and name in ('John', 'Jane', 'Jim')  "
            ],
            'Where in with subquery' => [
                'where' => [],
                'where_ins' => $where_in_with_and,
                'expected_query' => 'select * from users where id in (select user_id from notes where title =  :0  )  ',
                'expected_query_with_values' => "select * from users where id in (select user_id from notes where title =  'Groceries'  )  "
            ],
            'Where in with multiple subqueries' => [
                'where' => [],
                'where_ins' => $where_in_with_multiple_subqueries,
                'expected_query' => 'select * from users where id in (select user_id from notes where title =  :0  ) and id in (select id from users where age >  :1  )  ',
                'expected_query_with_values' => "select * from users where id in (select user_id from notes where title =  'Groceries'  ) and id in (select id from users where age >  20  )  "
            ]
        ];
    }

    #[DataProvider('whereInDataProvider')]
    public function testWhereIn(array $where, $where_ins, string $expected_query, string $expected_query_with_values): void
    {
        $this->mysql_select_query_builder->where($where);

        foreach ($where_ins() as $where_in) {
            $column_name = $where_in['column_name'];
            $values = $where_in['values'];
            $this->mysql_select_query_builder->whereIn(column_name: $column_name, values: $values);
        }

        $query = $this->mysql_select_query_builder->getQuery();
        $this->assertEquals($expected_query, $query);

        $query_with_values = $this->getQueryWithValues();
        $this->assertEquals($expected_query_with_values, $query_with_values);
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
            ->orIn(UserMockEntity::AGE, [20, 30]);

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
            ->orIn(UserMockEntity::AGE, [20, 30])
            ->orIn(UserMockEntity::ID, [2, 3]);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or age in (:1, :2) or id in (:3, :4) ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or age in (20, 30) or id in (2, 3) ';
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query, $query);
    }

    public static function addOrInDataProvider(): array
    {
        $sub_query = new MySQLDatabaseWrapper(static::createStub(Mysql::class))
            ->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20]);
        return [
            'With array' => [
                'column_name' => UserMockEntity::AGE,
                'values' => [20, 30],
                'expected_array' => [[
                    'column' => UserMockEntity::AGE,
                     'values' => [
                        ['value' => 20],
                        ['value' => 30]
                    ]
                ]]
            ],
            'With subquery' => [
                'column_name' => UserMockEntity::AGE,
                'values' => $sub_query,
                'expected_array' => [[
                    'column' => UserMockEntity::AGE,
                     'values' => $sub_query
                ]]
            ]
        ];
    }

    #[DataProvider('addOrInDataProvider')]
    public function testAddOrIn($column_name, $values, $expected_array): void
    {
        $this->addOrInMethodReflection->invoke(
            $this->mysql_select_query_builder,
            column_name: $column_name,
            values: $values
        );

        $actual_or_in_conditions = $this->orInConditionsReflection->getValue(
            $this->mysql_select_query_builder
        );
        $this->assertEquals($expected_array, $actual_or_in_conditions);
    }


    public function testAddWhereInWithArray(): void
    {
        $this->addWhereInMethodReflection->invoke(
            $this->mysql_select_query_builder,
            column_name: UserMockEntity::AGE,
            values: [20, 30]
        );
        $expected_where_in_conditions = [
            [
                'column' => UserMockEntity::AGE,
                 'values' => [
                    ['value' => 20],
                    ['value' => 30]
                ]
            ]
        ];
        $where_in_conditions = $this->whereInConditionsReflection->getValue($this->mysql_select_query_builder);

        $this->assertEquals($expected_where_in_conditions, $where_in_conditions);
    }

    public function testAddWhereInWithSubquery(): void
    {
        $sub_query = $this->mysql_database_wrapper->selectQuery('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20]);

        $expected_where_in_conditions_with_subquery = [[
            'column' => UserMockEntity::AGE,
            'values' => $sub_query
        ]];

        $this->addWhereInMethodReflection->invoke(
            $this->mysql_select_query_builder,
            column_name: UserMockEntity::AGE,
            values: $sub_query
        );

        $where_in_conditions = $this->whereInConditionsReflection->getValue($this->mysql_select_query_builder);

        $this->assertEquals($expected_where_in_conditions_with_subquery, $where_in_conditions);
    }


    public function testOrInWithSubquery(): void
    {
        $sub_query = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);
        $this->mysql_select_query_builder
            ->where([UserMockEntity::ID . ' = ' => 1])
            ->orIn(UserMockEntity::ID, $sub_query);

        // Test raw
        $expected_query = 'select * from users where id =  :0 or id in (select user_id from notes where title =  :1  ) ';
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $expected_query = 'select * from users where id =  1 or id in (select user_id from notes where title =  \'Groceries\'  ) ';
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
            ->orIn(UserMockEntity::EMAIL_ADDRESS, ["'test@example.com'"])
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

        $sub_query_5 = $this->mysql_database_wrapper->selectQuery('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => 'Movies']);

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
            ->orIn(UserMockEntity::AGE, [20, 30])
            ->orIn(UserMockEntity::ID, [2, 3])
            ->orIn(UserMockEntity::ID, $sub_query_5)
            ->limit(1)
            ->getQuery();

        // Test raw
        $expected_query = 'select id, name, email_address, age, phone_number from users where email_address :0 and id =  :1 and name =  :2 and name in (:3, :4, :5) and id in (select user_id from notes where title =  :6  ) and id in (select id from users where age >  :7 and phone_number :8  ) and name not  in (:9, :10) and id not  in (select user_id from notes where title =  :11  ) and id not  in (select id from users where age >  :12  ) or email_address :13 or age >  :14 and email_address :15 or age in (:16, :17) or id in (:18, :19) or id in (select user_id from notes where title =  :20  )  limit 1';
        $this->assertEquals($expected_query, $query);

        //Test with values
        $expected_query = "select id, name, email_address, age, phone_number from users where email_address 'test@example.com' and id =  1 and name =  'John' and name in ('John', 'Jane', 'Jim') and id in (select user_id from notes where title =  'Groceries'  ) and id in (select id from users where age >  20 and phone_number 123456789  ) and name not  in ('Tom', 10) and id not  in (select user_id from notes where title =  11  ) and id not  in (select id from users where age >  12  ) or email_address 13 or age >  14 and email_address 15 or age in (16, 17) or id in (18, 19) or id in (select user_id from notes where title =  'John'0  )  limit 1";
        $query = $this->getQueryWithValues();
        $this->assertEquals($expected_query, $query);

        // Test prepared statement indexes
        $expected_query_index = 21;
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

        $expected_sub_query_5_index = 21;
        $sub_query_5_index = $sub_query_5->getPreparedStatementIndex();
        $this->assertEquals($expected_sub_query_5_index, $sub_query_5_index);
    }
}
