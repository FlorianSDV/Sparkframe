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

    public function setUp(): void
    {
        $this->mysql_select_query_builder = static::createSelectQueryBuilder('users', UserMockEntity::class);
    }

    public static function createSelectQueryBuilder(string $table_name, string $entity_class): MySQLSelectQueryBuilder
    {
        return new MySQLDatabaseWrapper(static::createStub(Mysql::class))
            ->selectQuery($table_name, $entity_class);
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
    public function testSelect(array $column_names, string $expected_query): void
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
    public function testWhere(array $where, string $expected_query, string $expected_query_with_values): void
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
            $sub_query_1 = static::createSelectQueryBuilder('notes', NoteMockEntity::class)
                ->select(NoteMockEntity::USER_ID)
                ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);
            return ['column_name' => UserMockEntity::ID, 'values' => $sub_query_1];
        };

        $sub_query_2_fn = function () {
            $sub_query_2 = static::createSelectQueryBuilder('users', UserMockEntity::class)
                ->select(UserMockEntity::ID)
                ->where([UserMockEntity::AGE . ' > ' => 20]);
            return ['column_name' => UserMockEntity::ID, 'values' => $sub_query_2];
        };

        $where_ins_array_fn = fn () => [['column_name' => UserMockEntity::NAME, 'values' => ["'John'", "'Jane'", "'Jim'"]]];

        $where_in_with_and_fn = fn () => [$sub_query_1_fn()];

        $where_in_with_multiple_subqueries_fn = fn () => [$sub_query_1_fn(), $sub_query_2_fn()];

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
                'where_ins' => $where_in_with_and_fn,
                'expected_query' => 'select * from users where id in (select user_id from notes where title =  :0  )  ',
                'expected_query_with_values' => "select * from users where id in (select user_id from notes where title =  'Groceries'  )  "
            ],
            'Where in with multiple subqueries' => [
                'where' => [],
                'where_ins' => $where_in_with_multiple_subqueries_fn,
                'expected_query' => 'select * from users where id in (select user_id from notes where title =  :0  ) and id in (select id from users where age >  :1  )  ',
                'expected_query_with_values' => "select * from users where id in (select user_id from notes where title =  'Groceries'  ) and id in (select id from users where age >  20  )  "
            ]
        ];
    }

    #[DataProvider('whereInDataProvider')]
    public function testWhereIn(array $where, callable $where_ins, string $expected_query, string $expected_query_with_values): void
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

    public static function whereNotInDataProvider(): array
    {
        $sub_query_1_fn = function () {
            $sub_query_1 = static::createSelectQueryBuilder('notes', NoteMockEntity::class)
                ->select(NoteMockEntity::USER_ID)
                ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);
            return ['column_name' => UserMockEntity::ID, 'values' => $sub_query_1];
        };

        $sub_query_2_fn = function () {
            $sub_query_2 = static::createSelectQueryBuilder('users', UserMockEntity::class)
                ->select(UserMockEntity::ID)
                ->where([UserMockEntity::AGE . ' > ' => 20]);
            return ['column_name' => UserMockEntity::ID, 'values' => $sub_query_2];
        };

        $where_not_in_with_subquery_fn = fn () => [$sub_query_1_fn()];

        $where_not_in_with_multiple_subqueries_fn = fn () => [$sub_query_1_fn(), $sub_query_2_fn()];

        $where_not_ins_array_fn = fn () => [['column_name' => UserMockEntity::NAME, 'values' => ["'John'", "'Jane'", "'Jim'"]]];

        return [
            'Single where not in' => [
                'where' => [],
                'where_not_ins' => $where_not_ins_array_fn,
                'expected_query' => 'select * from users where name not  in (:0, :1, :2)  ',
                'expected_query_with_values' => "select * from users where name not  in ('John', 'Jane', 'Jim')  "
            ],
            'Where not in with and' => [
                'where' => [UserMockEntity::AGE . ' > ' => 20],
                'where_not_ins' => $where_not_ins_array_fn,
                'expected_query' => 'select * from users where age >  :0 and name not  in (:1, :2, :3)  ',
                'expected_query_with_values' => "select * from users where age >  20 and name not  in ('John', 'Jane', 'Jim')  "
            ],
            'Where not in with subquery' => [
                'where' => [],
                'where_not_ins' => $where_not_in_with_subquery_fn,
                'expected_query' => 'select * from users where id not  in (select user_id from notes where title =  :0  )  ',
                'expected_query_with_values' => "select * from users where id not  in (select user_id from notes where title =  'Groceries'  )  "
            ],
            'Where not in with multiple subqueries' => [
                'where' => [],
                'where_not_ins' => $where_not_in_with_multiple_subqueries_fn,
                'expected_query' => 'select * from users where id not  in (select user_id from notes where title =  :0  ) and id not  in (select id from users where age >  :1  )  ',
                'expected_query_with_values' => "select * from users where id not  in (select user_id from notes where title =  'Groceries'  ) and id not  in (select id from users where age >  20  )  "
            ],
        ];
    }

    #[DataProvider('whereNotInDataProvider')]
    public function testWhereNotIn(array $where, callable $where_not_ins, string $expected_query, string $expected_query_with_values): void
    {
        $this->mysql_select_query_builder->where($where);

        foreach ($where_not_ins() as $where_not_in) {
            $column_name = $where_not_in['column_name'];
            $values = $where_not_in['values'];
            $this->mysql_select_query_builder->whereNotIn(column_name: $column_name, values: $values);
        }

        $query = $this->mysql_select_query_builder->getQuery();
        $this->assertEquals($expected_query, $query);

        $query_with_values = $this->getQueryWithValues();
        $this->assertEquals($expected_query_with_values, $query_with_values);
    }

    public static function orDataProvider(): array
    {
        $where = [UserMockEntity::ID . ' = ' => 1];
        $empty_or_ins_fn = fn () => [];

        $empty_or_not_ins_fn = fn () => [];

        $test_or_in_fn = fn () => [['column_name' => UserMockEntity::AGE, 'values' => [20, 30]]];

        $test_multiple_or_ins_fn = fn () => [
            ['column_name' => UserMockEntity::AGE, 'values' => [20, 30]],
            ['column_name' => UserMockEntity::ID, 'values' => [2, 3]],
        ];

        $test_or_in_with_subquery_fn = function () {
            $sub_query = static::createSelectQueryBuilder('notes', NoteMockEntity::class)
                ->select(NoteMockEntity::USER_ID)
                ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

            return [['column_name' => UserMockEntity::ID, 'values' => $sub_query]];
        };

        return [
            'Test or' => [
                'where' => $where,
                'ors' => [[UserMockEntity::AGE . ' > ' => 20]],
                'or_ins' => $empty_or_ins_fn,
                'or_not_ins' => $empty_or_not_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or age >  :1 ',
                'expected_query_with_values' => "select * from users where id =  1 or age >  20 "
            ],
            'Test or with and' => [
                'where' => $where,
                'ors' => [[
                    UserMockEntity::AGE . ' > ' => 20,
                    UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
                ]],
                'or_ins' => $empty_or_ins_fn,
                'or_not_ins' => $empty_or_not_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or age >  :1 and email_address :2 ',
                'expected_query_with_values' => "select * from users where id =  1 or age >  20 and email_address 'example@test.com' "
            ],
            'Test multiple or with and' => [
                'where' => $where,
                'ors' => [[
                    UserMockEntity::AGE . ' > ' => 20,
                    UserMockEntity::EMAIL_ADDRESS => "'example@test.com'"
                ],[
                    UserMockEntity::AGE . ' > ' => 30,
                    UserMockEntity::EMAIL_ADDRESS => "'example_2@test.com'"
                ]],
                'or_ins' => $empty_or_ins_fn,
                'or_not_ins' => $empty_or_not_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or age >  :1 and email_address :2 or age >  :3 and email_address :4 ',
                'expected_query_with_values' => "select * from users where id =  1 or age >  20 and email_address 'example@test.com' or age >  30 and email_address 'example_2@test.com' "
            ],
            'Test or in' => [
                'where' => $where,
                'ors' => [],
                'or_ins' => $test_or_in_fn,
                'or_not_ins' => $empty_or_not_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or age in (:1, :2) ',
                'expected_query_with_values' => 'select * from users where id =  1 or age in (20, 30) '
            ],
            'Test multiple or in' => [
                'where' => $where,
                'ors' => [],
                'or_ins' => $test_multiple_or_ins_fn,
                'or_not_ins' => $empty_or_not_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or age in (:1, :2) or id in (:3, :4) ',
                'expected_query_with_values' => 'select * from users where id =  1 or age in (20, 30) or id in (2, 3) '
            ],
            'Test or in with subquery' => [
                'where' => $where,
                'ors' => [],
                'or_ins' => $test_or_in_with_subquery_fn,
                'or_not_ins' => $empty_or_not_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or id in (select user_id from notes where title =  :1  ) ',
                'expected_query_with_values' => 'select * from users where id =  1 or id in (select user_id from notes where title =  \'Groceries\'  ) '
            ],
            'Test or not in' => [
                'where' => $where,
                'ors' => [],
                'or_ins' => $empty_or_ins_fn,
                'or_not_ins' => $test_or_in_fn,
                'expected_query' => 'select * from users where id =  :0 or age not  in (:1, :2) ',
                'expected_query_with_values' => 'select * from users where id =  1 or age not  in (20, 30) '
            ],
            'Test multiple or not in' => [
                'where' => $where,
                'ors' => [],
                'or_ins' => $empty_or_ins_fn,
                'or_not_ins' => $test_multiple_or_ins_fn,
                'expected_query' => 'select * from users where id =  :0 or age not  in (:1, :2) or id not  in (:3, :4) ',
                'expected_query_with_values' => 'select * from users where id =  1 or age not  in (20, 30) or id not  in (2, 3) '
            ],
            'Test or not in with subquery' => [
                'where' => $where,
                'ors' => [],
                'or_ins' => $empty_or_ins_fn,
                'or_not_ins' => $test_or_in_with_subquery_fn,
                'expected_query' => 'select * from users where id =  :0 or id not  in (select user_id from notes where title =  :1  ) ',
                'expected_query_with_values' => 'select * from users where id =  1 or id not  in (select user_id from notes where title =  \'Groceries\'  ) '
            ],
        ];
    }

    #[DataProvider('orDataProvider')]
    public function testOr(array $where, array $ors, callable $or_ins, callable $or_not_ins, string $expected_query, string $expected_query_with_values): void
    {
        $this->mysql_select_query_builder
            ->where($where);

        foreach ($ors as $or) {
            $this->mysql_select_query_builder->or($or);
        }

        foreach ($or_ins() as $or_in) {
            $this->mysql_select_query_builder->orIn($or_in['column_name'], $or_in['values']);
        }

        foreach ($or_not_ins() as $or_not_in) {
            $this->mysql_select_query_builder->orNotIn($or_not_in['column_name'], $or_not_in['values']);
        }

        // Test raw
        $query = $this->mysql_select_query_builder->getQuery();

        $this->assertEquals($expected_query, $query);

        // Test with values
        $query = $this->getQueryWithValues();

        $this->assertEquals($expected_query_with_values, $query);
    }

    public static function addOrInDataProvider(): array
    {
        $sub_query_fn = fn () => static::createSelectQueryBuilder('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20]);
        return [
            'With array' => [
                'column_name' => UserMockEntity::AGE,
                'values' => fn () => [20, 30],
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
                'values' => $sub_query_fn,
                'expected_array' => [[
                    'column' => UserMockEntity::AGE,
                     'values' => $sub_query_fn()
                ]]
            ]
        ];
    }

    #[DataProvider('addOrInDataProvider')]
    public function testAddOrIn(string $column_name, callable $values, array $expected_array): void
    {
        $or_in_method_reflection = new ReflectionMethod(MySQLSelectQueryBuilder::class, 'addOrIn');
        $or_in_method_reflection->invoke(
            $this->mysql_select_query_builder,
            column_name: $column_name,
            values: $values()
        );

        $or_in_conditions_reflection = new ReflectionProperty(MySQLSelectQueryBuilder::class, 'or_in_conditions');
        $actual_or_in_conditions = $or_in_conditions_reflection->getValue($this->mysql_select_query_builder);
        $this->assertEquals($expected_array, $actual_or_in_conditions);
    }

    public static function addWhereInDataProvider(): array
    {
        $expected_where_in_conditions_fn = fn () => [[
            'column' => UserMockEntity::AGE,
            'values' => [
                ['value' => 20],
                ['value' => 30]
            ]
        ]];

        $where_in_subquery_fn = fn () => static::createSelectQueryBuilder('users', UserMockEntity::class)
                ->select(UserMockEntity::ID)
                ->where([UserMockEntity::AGE . ' > ' => 20]);

        $expected_where_in_conditions_with_subquery_fn = fn () => [[
            'column' => UserMockEntity::AGE,
            'values' => $where_in_subquery_fn()
        ]];

        return [
            'Add where in with array' => [
                'column_name' => UserMockEntity::AGE,
                'values' => fn () => [20, 30],
                'expected_where_in_conditions' => $expected_where_in_conditions_fn
            ],
            'Add where in with subquery' => [
                'column_name' => UserMockEntity::AGE,
                'values' => $where_in_subquery_fn,
                'expected_where_in_conditions' => $expected_where_in_conditions_with_subquery_fn
            ]
        ];
    }

    #[DataProvider('addWhereInDataProvider')]
    public function testAddwhereIn(string $column_name, callable $values, callable $expected_where_in_conditions): void
    {
        $add_where_in_method_reflection = new ReflectionMethod(MySQLSelectQueryBuilder::class, 'addWhereIn');
        $add_where_in_method_reflection->invoke(
            $this->mysql_select_query_builder,
            column_name: $column_name,
            values: $values()
        );
        $where_in_conditions_reflection = new ReflectionProperty(MySQLSelectQueryBuilder::class, 'where_in_conditions');
        $where_in_conditions = $where_in_conditions_reflection->getValue($this->mysql_select_query_builder);

        $this->assertEquals($expected_where_in_conditions(), $where_in_conditions);
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
    public function testGetPreparedStatementIndex(array $where_array, int $expected_index): void
    {
        $this->mysql_select_query_builder
            ->where($where_array)
            ->getQuery();

        $this->assertEquals($expected_index, $this->mysql_select_query_builder->getPreparedStatementIndex());
    }

    public function testGetQueryWithDifferentIndex(): void
    {
        $index = rand(0, 1000);
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

    public static function readyForSubQueryDataProvider(): array
    {
        $get_query_fn = fn () => static::createSelectQueryBuilder('users', UserMockEntity::class);
        $get_ready_for_subquery_fn = fn () => $get_query_fn()->select(UserMockEntity::ID);
        $get_not_ready_for_subquery_fn = fn () => $get_query_fn()->select(UserMockEntity::ID, UserMockEntity::NAME);

        return [
            'Ready' => [
                'get_query_builder' => $get_ready_for_subquery_fn,
                'expected_result' => true
            ],
            'Not ready, select all' => [
                'get_query_builder' => $get_query_fn,
                'expected_result' => false
            ],
            'Not ready, select some' => [
                'get_query_builder' => $get_not_ready_for_subquery_fn,
                'expected_result' => false
            ],
        ];
    }

    #[DataProvider('readyForSubQueryDataProvider')]
    public function testReadyForSubQuery(callable $get_query_builder, bool $expected_result): void
    {
        /** @var MySQLSelectQueryBuilder $query_builder */
        $query_builder = $get_query_builder();
        $this->assertEquals($expected_result, $query_builder->readyForSubQuery());
    }

    public function testOrQueryFails(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot use or without where conditions!');

        $this->mysql_select_query_builder->or([UserMockEntity::ID => 1]);
    }

    public function testAllOptions(): void
    {
        $sub_query_1 = static::createSelectQueryBuilder('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'Groceries'"]);

        $sub_query_2 = static::createSelectQueryBuilder('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 20, UserMockEntity::PHONE_NUMBER => 123456789]);

        $sub_query_3 = static::createSelectQueryBuilder('notes', NoteMockEntity::class)
            ->select(NoteMockEntity::USER_ID)
            ->where([NoteMockEntity::TITLE . ' = ' => "'To Do'"]);

        $sub_query_4 = static::createSelectQueryBuilder('users', UserMockEntity::class)
            ->select(UserMockEntity::ID)
            ->where([UserMockEntity::AGE . ' > ' => 60]);

        $sub_query_5 = static::createSelectQueryBuilder('notes', NoteMockEntity::class)
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
