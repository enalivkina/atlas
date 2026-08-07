<?php

declare(strict_types=1);

namespace Atlas\tests\Unit\Resource\Query\MySql;

use Atlas\Resource\Query\MySql\DbQueryBuilder;
use Atlas\Resource\Query\Operator;
use Codeception\PHPUnit\TestCase;
use InvalidArgumentException;

final class DbQueryBuilderTest extends TestCase
{
    private DbQueryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new DbQueryBuilder();
    }

    public function testResetReturnsSelf(): void
    {
        $result = $this->builder->reset();
        $this->assertSame($this->builder, $result);
    }

    public function testSelectWithString(): void
    {
        $this->builder->select('id')->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.`id` FROM `users`', $statement->sql);
    }

    public function testSelectWithArray(): void
    {
        $this->builder->select(['id', 'name', 'email'])->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.`id`, `users`.`name`, `users`.`email` FROM `users`', $statement->sql);
    }

    public function testSelectWithAlias(): void
    {
        $this->builder->select(['id AS user_id', 'name AS user_name'])->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.`id` AS `user_id`, `users`.`name` AS `user_name` FROM `users`', $statement->sql);
    }

    public function testSelectWithAsterisk(): void
    {
        $this->builder->select('*')->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users`', $statement->sql);
    }

    public function testSelectWithQualifiedField(): void
    {
        $this->builder->select(['users.id', 'users.name'])->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.`id` AS `users.id`, `users`.`name` AS `users.name` FROM `users`', $statement->sql);
    }

    public function testFromWithString(): void
    {
        $this->builder->select('*')->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users`', $statement->sql);
    }

    public function testFromWithAlias(): void
    {
        $this->builder->select('*')->from(['u' => 'users']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT ``users``.* FROM `users` AS `u`', $statement->sql);
    }

    public function testFromWithInvalidArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FROM с массивом должен содержать [alias => table]');
        $this->builder->from(['users', 'u']);
    }

    public function testWhereWithSimpleCondition(): void
    {
        $this->builder->select('*')->from('users')->where(['status' => 'active']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` WHERE `users`.`status` = :where_0', $statement->sql);
        $this->assertEquals(['where_0' => 'active'], $statement->bindings);
    }

    public function testWhereWithMultipleConditions(): void
    {
        $this->builder->select('*')->from('users')->where([
            'status' => 'active',
            'age' => 25,
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` WHERE `users`.`status` = :where_0 AND `users`.`age` = :where_1', $statement->sql);
        $this->assertEquals(['where_0' => 'active', 'where_1' => 25], $statement->bindings);
    }

    public function testWhereWithOperators(): void
    {
        $this->builder->select('*')->from('users')->where([
            'age' => [Operator::GT->value => 18],
            'score' => [Operator::LTE->value => 100],
            'name' => [Operator::LIKE->value => 'john'],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` WHERE `users`.`age` > :where_0 AND `users`.`score` <= :where_1 AND `users`.`name` LIKE :where_2',
            $statement->sql,
        );
        $this->assertEquals(['where_0' => 18, 'where_1' => 100, 'where_2' => '%john%'], $statement->bindings);
    }

    public function testWhereWithInOperator(): void
    {
        $this->builder->select('*')->from('users')->where([
            'status' => [Operator::IN->value => ['active', 'pending', 'approved']],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` WHERE `users`.`status` IN (:where_0, :where_1, :where_2)',
            $statement->sql,
        );
        $this->assertEquals(['where_0' => 'active', 'where_1' => 'pending', 'where_2' => 'approved'], $statement->bindings);
    }

    public function testWhereWithNotInOperator(): void
    {
        $this->builder->select('*')->from('users')->where([
            'status' => [Operator::NIN->value => ['deleted', 'banned']],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` WHERE `users`.`status` NOT IN (:where_0, :where_1)',
            $statement->sql,
        );
        $this->assertEquals(['where_0' => 'deleted', 'where_1' => 'banned'], $statement->bindings);
    }

    public function testWhereWithEmptyInArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Оператор $in требует непустой массив');
        $this->builder->where(['status' => [Operator::IN->value => []]]);
    }

    public function testWhereWithNullValue(): void
    {
        $this->builder->select('*')->from('users')->where(['deleted_at' => null]);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` WHERE `users`.`deleted_at` IS NULL', $statement->sql);
        $this->assertEmpty($statement->bindings);
    }

    public function testWhereWithNotNullValue(): void
    {
        $this->builder->select('*')->from('users')->where([
            'deleted_at' => [Operator::NE->value => null],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` WHERE `users`.`deleted_at` IS NOT NULL', $statement->sql);
        $this->assertEmpty($statement->bindings);
    }

    public function testWhereWithInvalidOperatorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Неизвестный оператор: invalid');
        $this->builder->where(['status' => ['invalid' => 'value']]);
    }

    public function testWhereWithQualifiedField(): void
    {
        $this->builder->select('*')->from('users')->where(['users.status' => 'active']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` WHERE `users`.`status` = :where_0', $statement->sql);
        $this->assertEquals(['where_0' => 'active'], $statement->bindings);
    }

    public function testJoinWithString(): void
    {
        $this->builder
            ->select('*')
            ->from('users')
            ->join('inner', 'orders', 'users.id = orders.user_id');
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` INNER JOIN `orders` ON users.id = orders.user_id',
            $statement->sql,
        );
    }

    public function testJoinWithAlias(): void
    {
        $this->builder
            ->select('*')
            ->from('users')
            ->join('left', ['o' => 'orders'], 'users.id = o.user_id');
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` LEFT JOIN `orders` AS `o` ON users.id = o.user_id',
            $statement->sql,
        );
    }

    public function testJoinWithInvalidTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный тип JOIN\'а');
        $this->builder->join('invalid', 'orders', 'users.id = orders.user_id');
    }

    public function testMultipleJoins(): void
    {
        $this->builder
            ->select('*')
            ->from('users')
            ->join('inner', 'orders', 'users.id = orders.user_id')
            ->join('left', 'products', 'orders.product_id = products.id');
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` INNER JOIN `orders` ON users.id = orders.user_id LEFT JOIN `products` ON orders.product_id = products.id',
            $statement->sql,
        );
    }

    public function testOrderByWithSimpleColumn(): void
    {
        $this->builder->select('*')->from('users')->orderBy(['name' => 'ASC']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` ORDER BY `name` ASC', $statement->sql);
    }

    public function testOrderByWithMultipleColumns(): void
    {
        $this->builder->select('*')->from('users')->orderBy([
            'name' => 'ASC',
            'age' => 'DESC',
            'created_at' => 'ASC',
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.* FROM `users` ORDER BY `name` ASC, `age` DESC, `created_at` ASC',
            $statement->sql,
        );
    }

    public function testOrderByWithNumericArray(): void
    {
        $this->builder->select('*')->from('users')->orderBy(['name ASC', 'age DESC']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` ORDER BY `name` ASC, `age` DESC', $statement->sql);
    }

    public function testOrderByWithQualifiedColumn(): void
    {
        $this->builder->select('*')->from('users')->orderBy(['users.name' => 'ASC']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` ORDER BY `users`.`name` ASC', $statement->sql);
    }

    public function testOrderByWithInvalidDirectionThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный формат распределения');
        $this->builder->orderBy(['name' => 'INVALID']);
    }

    public function testOrderByWithEmptyColumnThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Имя колонны должно быть заполнено');
        $this->builder->orderBy(['' => 'ASC']);
    }

    public function testOrderByWithEmptyArray(): void
    {
        $this->builder->select('*')->from('users')->orderBy([]);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users`', $statement->sql);
    }

    public function testLimit(): void
    {
        $this->builder->select('*')->from('users')->limit(10);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` LIMIT 10', $statement->sql);
    }

    public function testOffset(): void
    {
        $this->builder->select('*')->from('users')->limit(10)->offset(20);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` LIMIT 10 OFFSET 20', $statement->sql);
    }

    public function testOffsetWithoutLimit(): void
    {
        $this->builder->select('*')->from('users')->offset(20);
        $statement = $this->builder->getStatement();
        $this->assertEquals('SELECT `users`.* FROM `users` OFFSET 20', $statement->sql);
    }

    public function testGetStatementWithoutFromThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Имя ресурса не задано');
        $this->builder->select('*')->getStatement();
    }

    public function testGetStatementWithoutSelect(): void
    {
        $this->builder->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals('FROM `users`', $statement->sql);
    }

    public function testGetStatementWithFullQuery(): void
    {
        $this->builder
            ->select(['u.id', 'u.name', 'o.total'])
            ->from(['u' => 'users'])
            ->join('inner', ['o' => 'orders'], 'u.id = o.user_id')
            ->where(['u.status' => 'active', 'o.total' => [Operator::GT->value => 100]])
            ->orderBy(['u.name' => 'ASC'])
            ->limit(10)
            ->offset(20);

        $statement = $this->builder->getStatement();

        $expectedSql = 'SELECT `u`.`id` AS `u.id`, `u`.`name` AS `u.name`, `o`.`total` AS `o.total` FROM `users` AS `u` INNER JOIN `orders` AS `o` ON u.id = o.user_id WHERE `u`.`status` = :where_0 AND `o`.`total` > :where_1 ORDER BY `u`.`name` ASC LIMIT 10 OFFSET 20';

        $this->assertEquals($expectedSql, $statement->sql);
        $this->assertEquals(['where_0' => 'active', 'where_1' => 100], $statement->bindings);
    }

    public function testGetRawSql(): void
    {
        $this->builder
            ->select('*')
            ->from('users')
            ->where(['status' => 'active', 'name' => "O'Reilly"]);

        $rawSql = $this->builder->getRawSql();
        $this->assertEquals("SELECT `users`.* FROM `users` WHERE `users`.`status` = 'active' AND `users`.`name` = 'O''Reilly'", $rawSql);
    }

    public function testGetRawSqlWithNullValue(): void
    {
        $this->builder
            ->select('*')
            ->from('users')
            ->where(['deleted_at' => null]);

        $rawSql = $this->builder->getRawSql();
        $this->assertEquals('SELECT `users`.* FROM `users` WHERE `users`.`deleted_at` IS NULL', $rawSql);
    }

    public function testGetRawSqlWithInOperator(): void
    {
        $this->builder
            ->select('*')
            ->from('users')
            ->where(['status' => [Operator::IN->value => ['active', 'pending']]]);

        $rawSql = $this->builder->getRawSql();
        $this->assertEquals("SELECT `users`.* FROM `users` WHERE `users`.`status` IN ('active', 'pending')", $rawSql);
    }

    public function testWhereInLegacyMethod(): void
    {
        $this->builder->from('users')->whereIn('status', ['active', 'pending', 'approved']);
        $statement = $this->builder->getStatement();
        $this->assertEquals('FROM `users`  WHERE status IN ( active, pending, approved )', $statement->sql);
    }

    public function testChaining(): void
    {
        $result = $this->builder
            ->select('*')
            ->from('users')
            ->where(['status' => 'active'])
            ->limit(10)
            ->offset(5);

        $this->assertSame($this->builder, $result);
    }

    public function testEscapeFieldWithSpecialCharacters(): void
    {
        $this->builder
            ->select(['field-with-dash', 'field with space'])
            ->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.`field-with-dash`, `users`.`field with space` FROM `users`',
            $statement->sql,
        );
    }

    public function testEscapeFieldWithBackticks(): void
    {
        $this->builder
            ->select(['field`with`backticks'])
            ->from('users');
        $statement = $this->builder->getStatement();
        $this->assertEquals(
            'SELECT `users`.`field``with``backticks` FROM `users`',
            $statement->sql,
        );
    }

    public function testComplexQueryWithAllFeatures(): void
    {
        $this->builder
            ->select(['u.id', 'u.name', 'u.email', 'o.total AS order_total'])
            ->from(['u' => 'users'])
            ->join('left', ['o' => 'orders'], 'u.id = o.user_id')
            ->where([
                'u.status' => 'active',
                'u.age' => [Operator::GTE->value => 18],
                'o.status' => [Operator::IN->value => ['completed', 'shipped']],
                'u.deleted_at' => null,
            ])
            ->orderBy(['u.name' => 'ASC', 'o.created_at' => 'DESC'])
            ->limit(20)
            ->offset(40);

        $statement = $this->builder->getStatement();

        $expectedSql = 'SELECT `u`.`id` AS `u.id`, `u`.`name` AS `u.name`, `u`.`email` AS `u.email`, `o`.`total` AS `order_total AS o.total AS order_total` FROM `users` AS `u` LEFT JOIN `orders` AS `o` ON u.id = o.user_id WHERE `u`.`status` = :where_0 AND `u`.`age` >= :where_1 AND `o`.`status` IN (:where_2, :where_3) AND `u`.`deleted_at` IS NULL ORDER BY `u`.`name` ASC, `o`.`created_at` DESC LIMIT 20 OFFSET 40';

        $this->assertEquals($expectedSql, $statement->sql);
        $this->assertEquals(
            ['where_0' => 'active', 'where_1' => 18, 'where_2' => 'completed', 'where_3' => 'shipped'],
            $statement->bindings,
        );
    }
}
