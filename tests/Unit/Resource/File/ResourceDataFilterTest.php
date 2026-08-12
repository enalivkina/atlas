<?php

declare(strict_types=1);

namespace Atlas\tests\Unit\Resource\File;

use Atlas\Resource\Connection\Contract\DataBaseConnectionInterface;
use Atlas\Resource\File\ResourceDataFilter;
use Atlas\Resource\Query\File\FileQueryBuilder;
use Atlas\Resource\Query\QueryBuilderInterface;
use Atlas\Resource\Query\StatementParameterInterface;
use BadMethodCallException;
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
        $this->queryBuilder = new FileQueryBuilder();
        $this->statement = $this->createMock(StatementParameterInterface::class);

        $this->filter = new ResourceDataFilter(
            $this->connection,
            $this->queryBuilder,
        );

        $this->filter
            ->setResourceName('users.json')
            ->setAccessibleFields(['id', 'name', 'email', 'age', 'status'])
            ->setAccessibleFilters(['id', 'name', 'email', 'age', 'status']);
    }

    public function testSetResourceName(): void
    {
        $result = $this->filter->setResourceName('products.json');
        $this->assertSame($this->filter, $result);
    }

    public function testSetAccessibleFields(): void
    {
        $fields = ['id', 'name', 'price', 'stock'];
        $result = $this->filter->setAccessibleFields($fields);
        $this->assertSame($this->filter, $result);
    }

    public function testSetAccessibleFilters(): void
    {
        $filters = ['id', 'category', 'price', 'in_stock'];
        $result = $this->filter->setAccessibleFilters($filters);
        $this->assertSame($this->filter, $result);
    }

    public function testChainingConfiguration(): void
    {
        $result = $this->filter
            ->setResourceName('products.json')
            ->setAccessibleFields(['id', 'name'])
            ->setAccessibleFilters(['id', 'category']);

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

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn($expectedResult);

        $result = $this->filter->filterAll($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterAllWithoutFilter(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
        ];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $result = $this->filter->filterAll($condition);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFilterAllWithoutLimitAndOffset(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
        ];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithEmptyResult(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'filter' => ['status' => 'nonexistent'],
        ];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $result = $this->filter->filterAll($condition);

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    public function testFilterAllWithNumericStringLimitAndOffset(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'limit' => '10',
            'offset' => '20',
        ];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterOneWithDefaultFields(): void
    {
        $condition = [
            'filter' => ['id' => 1],
        ];

        $expectedResult = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $this->connection
            ->expects($this->once())
            ->method('selectOne')
            ->willReturn($expectedResult);

        $result = $this->filter->filterOne($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterOneWithoutResult(): void
    {
        $condition = [
            'fields' => ['id', 'name'],
            'filter' => ['id' => 999],
        ];

        $this->connection
            ->expects($this->once())
            ->method('selectOne')
            ->willReturn(null);

        $result = $this->filter->filterOne($condition);

        $this->assertNull($result);
    }

    public function testFilterAllWithInvalidFieldThrowsException(): void
    {
        $condition = [
            'fields' => ['id', 'invalid_field', 'name'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Поле 'invalid_field' недоступно для выборки");

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithInvalidFilterThrowsException(): void
    {
        $condition = [
            'filter' => ['invalid_filter' => 'value'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Поле 'invalid_filter' недоступно для фильтрации");

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithMultipleInvalidFields(): void
    {
        $condition = [
            'fields' => ['id', 'invalid1', 'invalid2', 'name'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Поле 'invalid1' недоступно для выборки");

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithMultipleInvalidFilters(): void
    {
        $condition = [
            'filter' => ['invalid1' => 'value1', 'invalid2' => 'value2'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Поле 'invalid1' недоступно для фильтрации");

        $this->filter->filterAll($condition);
    }

    public function testFilterOneWithInvalidFieldThrowsException(): void
    {
        $condition = [
            'fields' => ['id', 'invalid_field'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Поле 'invalid_field' недоступно для выборки");

        $this->filter->filterOne($condition);
    }

    public function testFilterOneWithInvalidFilterThrowsException(): void
    {
        $condition = [
            'filter' => ['invalid_filter' => 'value'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Поле 'invalid_filter' недоступно для фильтрации");

        $this->filter->filterOne($condition);
    }

    // ==================== ТЕСТЫ СЛОЖНЫХ СЦЕНАРИЕВ ====================

    public function testFilterAllWithComplexCondition(): void
    {
        $condition = [
            'fields' => ['id', 'name', 'email', 'age'],
            'filter' => [
                'status' => 'active',
                'age' => ['gt' => 18, 'lt' => 65],
            ],
            'order' => ['name' => 'asc', 'age' => 'desc'],
            'limit' => 20,
            'offset' => 10,
        ];

        $expectedResult = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'age' => 25],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com', 'age' => 30],
        ];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn($expectedResult);

        $result = $this->filter->filterAll($condition);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterAllWithEmptyFieldsAndEmptyFilter(): void
    {
        $condition = [];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testFilterAllWithDifferentResourceName(): void
    {
        $this->filter->setResourceName('products.json');
        $condition = ['fields' => ['id', 'name']];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterOneWithDifferentResourceName(): void
    {
        $this->filter->setResourceName('products.json');
        $condition = ['filter' => ['id' => 1]];

        $this->connection
            ->expects($this->once())
            ->method('selectOne')
            ->willReturn(null);

        $this->filter->filterOne($condition);
    }

    public function testFilterAllWithEmptyAccessibleFields(): void
    {
        $this->filter->setAccessibleFields([]);
        $condition = ['fields' => []];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition);
    }

    public function testFilterAllWithEmptyAccessibleFilters(): void
    {
        $this->filter->setAccessibleFilters([]);
        $condition = ['filter' => []];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition);
        $this->assertTrue(true);
    }

    public function testSetRelationshipsThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Связи не реализуются для файлов');

        $this->filter->setRelationships(['orders' => 'user_id']);
    }

    public function testMultipleFilterAllCalls(): void
    {
        $condition1 = ['fields' => ['id', 'name']];
        $condition2 = ['fields' => ['id', 'email']];

        $this->connection
            ->expects($this->exactly(2))
            ->method('select')
            ->willReturn([]);

        $this->filter->filterAll($condition1);
        $this->filter->filterAll($condition2);
    }

    public function testFilterAllAndFilterOneCombination(): void
    {
        $condition = ['fields' => ['id', 'name']];

        $this->connection
            ->expects($this->once())
            ->method('select')
            ->willReturn([]);

        $this->connection
            ->expects($this->once())
            ->method('selectOne')
            ->willReturn(null);

        $this->filter->filterAll($condition);
        $this->filter->filterOne($condition);
    }
}
