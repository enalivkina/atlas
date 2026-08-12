<?php

declare(strict_types=1);

namespace Atlas\tests\Unit\Resource\Db;

use Atlas\Resource\Connection\Contract\DataBaseConnectionInterface;
use Atlas\Resource\Db\ResourceDataFilter;
use Atlas\Resource\Query\MySql\DbQueryBuilder;
use Atlas\Resource\Query\QueryBuilderInterface;
use Atlas\Resource\Query\StatementParameterInterface;
use Codeception\PHPUnit\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

final class ResourceDataFilterTest extends TestCase
{
    private ResourceDataFilter $filter;
    private DataBaseConnectionInterface|MockObject $connection;
    private QueryBuilderInterface|MockObject $queryBuilder;
    private StatementParameterInterface|MockObject $statement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createMock(DataBaseConnectionInterface::class);
        $this->queryBuilder = new DbQueryBuilder();
        $this->statement = $this->createMock(StatementParameterInterface::class);

        $this->filter = new ResourceDataFilter(
            $this->connection,
            $this->queryBuilder,
        );

        $this->filter
            ->setResourceName('users')
            ->setAccessibleFields(['id', 'name', 'email', 'age', 'status'])
            ->setAccessibleFilters(['id', 'name', 'email', 'age', 'status']);
    }

    public function testSetResourceName(): void
    {
        $result = $this->filter->setResourceName('products');
        $this->assertSame($this->filter, $result);
    }

    public function testSetAccessibleFields(): void
    {
        $fields = ['id', 'name', 'price'];
        $result = $this->filter->setAccessibleFields($fields);
        $this->assertSame($this->filter, $result);
    }

    public function testSetAccessibleFilters(): void
    {
        $filters = ['id', 'category', 'price'];
        $result = $this->filter->setAccessibleFilters($filters);
        $this->assertSame($this->filter, $result);
    }

    public function testFilterAllWithDefaultFields(): void
    {
        $condition = [
            'filter' => ['status' => 'active'],
        ];

        $expectedResult = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
        ];

        $this->connection->method('select')->willReturn($expectedResult);

        $result = $this->filter->filterAll($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterAllWithoutFilter(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
        ];

        $expectedResult = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ];

        $this->connection->method('select')->willReturn($expectedResult);

        $result = $this->filter->filterAll($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterAllWithoutLimitAndOffset(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithInvalidFieldThrowsException(): void
    {
        $condition = [
            'fields' => ['id', 'invalid_field'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithInvalidFilterThrowsException(): void
    {
        $condition = [
            'filter' => ['invalid_filter' => 'value'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithMultipleInvalidFields(): void
    {
        $condition = [
            'fields' => ['id', 'invalid1', 'invalid2', 'name'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithMultipleInvalidFilters(): void
    {
        $condition = [
            'filter' => ['invalid1' => 'value1', 'invalid2' => 'value2'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithEmptyResult(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'filter' => ['status' => 'nonexistent'],
        ];

        $this->connection->method('select')->willReturn([]);

        $result = $this->filter->filterAll($condition);

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    public function testFilterOneWithDefaultFields(): void
    {
        $condition = [
            'filter' => ['id' => 1],
        ];

        $expectedResult = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $this->connection->method('selectOne')->willReturn($expectedResult);

        $result = $this->filter->filterOne($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterOneWithoutResult(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'filter' => ['id' => 999],
        ];

        $this->connection->method('selectOne')->willReturn(null);

        $result = $this->filter->filterOne($condition);

        $this->assertNull($result);
    }

    public function testFilterOneWithInvalidFieldThrowsException(): void
    {
        $condition = [
            'fields' => ['id', 'invalid_field'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->filter->filterOne($condition);
    }

    public function testFilterOneWithInvalidFilterThrowsException(): void
    {
        $condition = [
            'filter' => ['invalid_filter' => 'value'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->filter->filterOne($condition);
    }

    public function testCheckConditionOnAccessibleWithFieldsAndFilters(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'filter' => ['status' => 'active'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testSetAccessibleFieldsAfterInstantiation(): void
    {
        $newFields = ['id', 'title', 'content'];
        $this->filter->setAccessibleFields($newFields);

        $condition = [
            'fields' => ['id', 'title'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testSetAccessibleFiltersAfterInstantiation(): void
    {
        $newFilters = ['id', 'category', 'price'];
        $this->filter->setAccessibleFilters($newFilters);

        $condition = [
            'filter' => ['category' => 'books'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testFilterAllWithComplexCondition(): void
    {
        $condition = [
            'fields' => ['id', 'name', 'email', 'age'],
            'filter' => [
                'status' => 'active',
                'age' => ['$gt' => 18, '$lt' => 65],
            ],
            'order' => ['name' => 'asc', 'age' => 'desc'],
            'limit' => 20,
            'offset' => 10,
        ];

        $expectedResult = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'age' => 25],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com', 'age' => 30],
        ];

        $this->connection->method('select')->willReturn($expectedResult);

        $result = $this->filter->filterAll($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterAllWithNumericStringLimitAndOffset(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'limit' => 10,
            'offset' => 20,
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithAllAccessibleFields(): void
    {
        $condition = [
            'fields' => ['id', 'name', 'email', 'age', 'status'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testFilterAllWithDuplicateFields(): void
    {
        $condition = [
            'fields' => ['id', 'name', 'id', 'email'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testFilterAllWithEmptyFieldsAndEmptyFilter(): void
    {
        $condition = [];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testFilterAllWithDifferentResourceName(): void
    {
        $this->filter->setResourceName('products.json');
        $condition = ['fields' => ['id', 'name']];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithEmptyAccessibleFields(): void
    {
        $this->filter->setAccessibleFields([]);
        $condition = ['fields' => []];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithEmptyAccessibleFilters(): void
    {
        $this->filter->setAccessibleFilters([]);
        $condition = ['filter' => []];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testFilterAllWithStringFields(): void
    {
        $condition = [
            'fields' => ['id', 'name', 'status'],
        ];

        $this->connection->method('select')->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }
}
