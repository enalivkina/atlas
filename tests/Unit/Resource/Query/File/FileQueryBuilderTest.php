<?php

declare(strict_types=1);

namespace Atlas\tests\Unit\Resource\Query\File;

use Atlas\Resource\Query\File\FileQueryBuilder;
use Atlas\Resource\Query\Operator;
use BadMethodCallException;
use Codeception\PHPUnit\TestCase;
use InvalidArgumentException;

final class FileQueryBuilderTest extends TestCase
{
    private FileQueryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new FileQueryBuilder();
    }

    public function testResetReturnsSelf(): void
    {
        $result = $this->builder->reset();
        $this->assertSame($this->builder, $result);
    }

    public function testSelectWithString(): void
    {
        $this->builder->select('id')->from('users.json');
        $statement = $this->builder->getStatement();
        $this->assertEquals(['id'], $statement->selectFields);
    }

    public function testSelectWithArray(): void
    {
        $this->builder->select(['id', 'name', 'email'])->from('users.json');
        $statement = $this->builder->getStatement();
        $this->assertEquals(['id', 'name', 'email'], $statement->selectFields);
    }

    public function testSelectOverwritesPreviousFields(): void
    {
        $this->builder->select(['id', 'name'])->select(['email', 'age'])->from('users.json');
        $statement = $this->builder->getStatement();
        $this->assertEquals(['email', 'age'], $statement->selectFields);
    }

    public function testSelectReturnsSelf(): void
    {
        $result = $this->builder->select('id');
        $this->assertSame($this->builder, $result);
    }

    public function testFromWithString(): void
    {
        $this->builder->from('users.json');
        $statement = $this->builder->getStatement();
        $this->assertEquals('users.json', $statement->resource);
    }

    public function testFromWithArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ресурс должен быть строкой');
        $this->builder->from(['users.json']);
    }

    public function testFromReturnsSelf(): void
    {
        $result = $this->builder->from('users.json');
        $this->assertSame($this->builder, $result);
    }

    public function testFromOverwritesPreviousResource(): void
    {
        $this->builder->from('users.json')->from('products.json');
        $statement = $this->builder->getStatement();
        $this->assertEquals('products.json', $statement->resource);
    }

    public function testWhereWithSimpleCondition(): void
    {
        $this->builder->from('users.json')->where(['status' => 'active']);
        $statement = $this->builder->getStatement();
        $this->assertEquals(['status' => [Operator::EQ->value => 'active']], $statement->whereClause);
    }

    public function testWhereWithMultipleConditions(): void
    {
        $this->builder->from('users.json')->where([
            'status' => 'active',
            'age' => 25,
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::EQ->value => 'active'],
            'age' => [Operator::EQ->value => 25],
        ], $statement->whereClause);
    }

    public function testWhereWithOperators(): void
    {
        $this->builder->from('users.json')->where([
            'age' => [Operator::GT->value => 18],
            'score' => [Operator::LTE->value => 100],
            'name' => [Operator::LIKE->value => 'john'],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'age' => [Operator::GT->value => 18],
            'score' => [Operator::LTE->value => 100],
            'name' => [Operator::LIKE->value => 'john'],
        ], $statement->whereClause);
    }

    public function testWhereMergesConditionsForSameField(): void
    {
        $this->builder
            ->from('users.json')
            ->where(['age' => [Operator::GT->value => 18]])
            ->where(['age' => [Operator::LT->value => 65]]);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'age' => [
                Operator::GT->value => 18,
                Operator::LT->value => 65,
            ],
        ], $statement->whereClause);
    }

    public function testWhereMergesMultipleOperatorsForSameField(): void
    {
        $this->builder->from('users.json')->where([
            'age' => [
                Operator::GTE->value => 18,
                Operator::LTE->value => 65,
            ],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'age' => [
                Operator::GTE->value => 18,
                Operator::LTE->value => 65,
            ],
        ], $statement->whereClause);
    }

    public function testWhereWithInOperator(): void
    {
        $this->builder->from('users.json')->where([
            'status' => [Operator::IN->value => ['active', 'pending', 'approved']],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::IN->value => ['active', 'pending', 'approved']],
        ], $statement->whereClause);
    }

    public function testWhereWithNotInOperator(): void
    {
        $this->builder->from('users.json')->where([
            'status' => [Operator::NIN->value => ['deleted', 'banned']],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::NIN->value => ['deleted', 'banned']],
        ], $statement->whereClause);
    }

    public function testWhereWithNullValue(): void
    {
        $this->builder->from('users.json')->where(['deleted_at' => null]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'deleted_at' => [Operator::EQ->value => null],
        ], $statement->whereClause);
    }

    public function testWhereWithComplexConditions(): void
    {
        $this->builder->from('users.json')->where([
            'status' => 'active',
            'age' => [Operator::GTE->value => 18],
            'name' => [Operator::LIKE->value => 'john'],
            'role' => [Operator::IN->value => ['admin', 'moderator']],
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::EQ->value => 'active'],
            'age' => [Operator::GTE->value => 18],
            'name' => [Operator::LIKE->value => 'john'],
            'role' => [Operator::IN->value => ['admin', 'moderator']],
        ], $statement->whereClause);
    }

    public function testWhereReturnsSelf(): void
    {
        $result = $this->builder->where(['status' => 'active']);
        $this->assertSame($this->builder, $result);
    }

    public function testWhereIn(): void
    {
        $this->builder
            ->from('users.json')
            ->whereIn('status', ['active', 'pending', 'approved']);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::IN->value => ['active', 'pending', 'approved']],
        ], $statement->whereClause);
    }

    public function testWhereInMergesWithExistingConditions(): void
    {
        $this->builder
            ->from('users.json')
            ->where(['status' => 'active'])
            ->whereIn('status', ['pending', 'approved']);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [
                Operator::EQ->value => 'active',
                Operator::IN->value => ['pending', 'approved'],
            ],
        ], $statement->whereClause);
    }

    public function testWhereInWithMultipleFields(): void
    {
        $this->builder
            ->from('users.json')
            ->whereIn('status', ['active', 'pending'])
            ->whereIn('role', ['admin', 'moderator']);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::IN->value => ['active', 'pending']],
            'role' => [Operator::IN->value => ['admin', 'moderator']],
        ], $statement->whereClause);
    }

    public function testWhereInReturnsSelf(): void
    {
        $result = $this->builder->whereIn('status', ['active']);
        $this->assertSame($this->builder, $result);
    }

    public function testJoinThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Join не реализуется для файлов');
        $this->builder->join('inner', 'orders.json', 'users.id = orders.user_id');
    }

    public function testOrderByWithSimpleArray(): void
    {
        $this->builder->from('users.json')->orderBy(['name', 'age']);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'name' => 'asc',
            'age' => 'asc',
        ], $statement->orderByClause);
    }

    public function testOrderByWithAssociativeArray(): void
    {
        $this->builder->from('users.json')->orderBy([
            'name' => 'asc',
            'age' => 'desc',
            'created_at' => 'asc',
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'name' => 'asc',
            'age' => 'desc',
            'created_at' => 'asc',
        ], $statement->orderByClause);
    }

    public function testOrderByWithMixedArray(): void
    {
        $this->builder->from('users.json')->orderBy([
            'name' => 'asc',
            'age',
            'created_at' => 'desc',
        ]);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'name' => 'asc',
            'age' => 'asc',
            'created_at' => 'desc',
        ], $statement->orderByClause);
    }

    public function testOrderByOverwritesPreviousOrderBy(): void
    {
        $this->builder
            ->from('users.json')
            ->orderBy(['name' => 'asc'])
            ->orderBy(['age' => 'desc']);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'name' => 'asc',
            'age' => 'desc',
        ], $statement->orderByClause);
    }

    public function testOrderByWithNumericKeys(): void
    {
        $this->builder->from('users.json')->orderBy(['name', 'email', 'age']);
        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'name' => 'asc',
            'email' => 'asc',
            'age' => 'asc',
        ], $statement->orderByClause);
    }

    public function testOrderByReturnsSelf(): void
    {
        $result = $this->builder->orderBy(['name' => 'asc']);
        $this->assertSame($this->builder, $result);
    }

    public function testLimitWithPositiveNumber(): void
    {
        $this->builder->from('users.json')->limit(10);
        $statement = $this->builder->getStatement();
        $this->assertEquals(10, $statement->limit);
    }

    public function testLimitWithZero(): void
    {
        $this->builder->from('users.json')->limit(0);
        $statement = $this->builder->getStatement();
        $this->assertEquals(0, $statement->limit);
    }

    public function testLimitWithNegativeNumberSetsNull(): void
    {
        $this->builder->from('users.json')->limit(-5);
        $statement = $this->builder->getStatement();
        $this->assertNull($statement->limit);
    }

    public function testLimitOverwritesPreviousLimit(): void
    {
        $this->builder->from('users.json')->limit(10)->limit(20);
        $statement = $this->builder->getStatement();
        $this->assertEquals(20, $statement->limit);
    }

    public function testLimitReturnsSelf(): void
    {
        $result = $this->builder->limit(10);
        $this->assertSame($this->builder, $result);
    }

    public function testOffsetWithPositiveNumber(): void
    {
        $this->builder->from('users.json')->offset(5);
        $statement = $this->builder->getStatement();
        $this->assertEquals(5, $statement->offset);
    }

    public function testOffsetWithZero(): void
    {
        $this->builder->from('users.json')->offset(0);
        $statement = $this->builder->getStatement();
        $this->assertEquals(0, $statement->offset);
    }

    public function testOffsetWithNegativeNumberSetsNull(): void
    {
        $this->builder->from('users.json')->offset(-5);
        $statement = $this->builder->getStatement();
        $this->assertNull($statement->offset);
    }

    public function testOffsetOverwritesPreviousOffset(): void
    {
        $this->builder->from('users.json')->offset(5)->offset(10);
        $statement = $this->builder->getStatement();
        $this->assertEquals(10, $statement->offset);
    }

    public function testOffsetReturnsSelf(): void
    {
        $result = $this->builder->offset(5);
        $this->assertSame($this->builder, $result);
    }

    public function testLimitAndOffsetCombination(): void
    {
        $this->builder
            ->from('users.json')
            ->limit(10)
            ->offset(20);

        $statement = $this->builder->getStatement();
        $this->assertEquals(10, $statement->limit);
        $this->assertEquals(20, $statement->offset);
    }

    public function testFullQueryWithAllFeatures(): void
    {
        $this->builder
            ->select(['id', 'name', 'email'])
            ->from('users.json')
            ->where([
                'status' => 'active',
                'age' => [Operator::GTE->value => 18],
                'role' => [Operator::IN->value => ['admin', 'moderator']],
            ])
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy(['name' => 'asc', 'age' => 'desc'])
            ->limit(10)
            ->offset(20);

        $statement = $this->builder->getStatement();

        $this->assertEquals('users.json', $statement->resource);
        $this->assertEquals(['id', 'name', 'email'], $statement->selectFields);
        $this->assertEquals([
            'status' => [
                Operator::EQ->value => 'active',
                Operator::IN->value => ['pending', 'approved'],
            ],
            'age' => [Operator::GTE->value => 18],
            'role' => [Operator::IN->value => ['admin', 'moderator']],
        ], $statement->whereClause);
        $this->assertEquals([
            'name' => 'asc',
            'age' => 'desc',
        ], $statement->orderByClause);
        $this->assertEquals(10, $statement->limit);
        $this->assertEquals(20, $statement->offset);
    }

    public function testMultipleWhereCallsWithDifferentFields(): void
    {
        $this->builder
            ->from('users.json')
            ->where(['status' => 'active'])
            ->where(['age' => [Operator::GT->value => 18]])
            ->where(['role' => 'admin']);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::EQ->value => 'active'],
            'age' => [Operator::GT->value => 18],
            'role' => [Operator::EQ->value => 'admin'],
        ], $statement->whereClause);
    }

    public function testOrderByWithEmptyArray(): void
    {
        $this->builder->from('users.json')->orderBy([]);
        $statement = $this->builder->getStatement();
        $this->assertEmpty($statement->orderByClause);
    }

    public function testSelectWithEmptyArray(): void
    {
        $this->builder->from('users.json')->select([]);
        $statement = $this->builder->getStatement();
        $this->assertEmpty($statement->selectFields);
    }

    public function testWhereWithEmptyArray(): void
    {
        $this->builder->from('users.json')->where([]);
        $statement = $this->builder->getStatement();
        $this->assertEmpty($statement->whereClause);
    }

    public function testWhereInWithEmptyArray(): void
    {
        $this->builder
            ->from('users.json')
            ->whereIn('status', []);

        $statement = $this->builder->getStatement();
        $this->assertEquals([
            'status' => [Operator::IN->value => []],
        ], $statement->whereClause);
    }
}
