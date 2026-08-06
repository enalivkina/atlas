<?php

declare(strict_types=1);

namespace Atlas\Resource\File;

use Atlas\Resource\Connection\Contract\DataBaseConnectionInterface;
use Atlas\Resource\Contract\ResourceDataFilterInterface;
use Atlas\Resource\Query\QueryBuilderInterface;
use BadMethodCallException;

final class ResourceDataFilter implements ResourceDataFilterInterface
{
    private ?string $resourceName = null;
    private ?array $accessibleFields = null;
    private ?array $accessibleFilters = null;

    public function __construct(
        private readonly DataBaseConnectionInterface $connection,
        private readonly QueryBuilderInterface $queryBuilder,
    ) {}


    public function setResourceName(string $name): static
    {
        $this->resourceName = $name;

        return $this;
    }

    public function setAccessibleFields(array $fieldNames): static
    {
        $this->accessibleFields = $fieldNames;

        return $this;
    }

    public function setAccessibleFilters(array $filterNames): static
    {
        $this->accessibleFilters = $filterNames;

        return $this;
    }

    public function filterAll(array $condition): array
    {
        $this->checkConditionOnAccessible($condition);

        $query = $this->buildQuery($condition);

        return $this->connection->select($query);
    }

    public function filterOne(array $condition): array|null
    {
        $this->checkConditionOnAccessible($condition);

        $query = $this->buildQuery($condition);

        return $this->connection->selectOne($query);
    }

    private function checkConditionOnAccessible(array $condition): void
    {
        if (isset($condition['fields']) === true) {
            $this->checkFieldsOnAccessible($condition['fields']);
        }

        if (isset($condition['filter']) === true) {
            $this->checkFiltersOnAccessible(array_keys($condition['filter']));
        }
    }

    private function checkFieldsOnAccessible(array $fields): void
    {
        foreach ($fields as $field) {
            if (in_array($field, $this->accessibleFields, true) === false) {
                throw new \InvalidArgumentException("Поле '{$field}' недоступно для выборки");
            }
        }
    }

    private function checkFiltersOnAccessible(array $fields): void
    {
        foreach ($fields as $field) {
            if (in_array($field, $this->accessibleFilters, true) === false) {
                throw new \InvalidArgumentException("Поле '{$field}' недоступно для фильтрации");
            }
        }
    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $this->queryBuilder->select(empty($condition['fields']) === true ? $this->accessibleFields : $condition['fields']);
        $this->queryBuilder->from($this->resourceName);
        $this->queryBuilder->where($condition['filter'] ?? []);
        $this->queryBuilder->orderBy($condition['order'] ?? []);
        $this->queryBuilder->limit(isset($condition['limit']) === true ? (int) $condition['limit'] : -1);
        $this->queryBuilder->offset(isset($condition['offset']) === true ? (int) $condition['offset'] : -1);

        return $this->queryBuilder;
    }

    public function setRelationships(array $relationships): static
    {
        throw new BadMethodCallException('Связи не реализуются для файлов');
    }
}
