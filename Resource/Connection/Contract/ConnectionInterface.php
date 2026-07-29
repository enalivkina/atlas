<?php

declare(strict_types=1);

namespace Atlas\Resource\Connection\Contract;

interface ConnectionInterface
{
    public function select(QueryBuilderInterface $query): array;

    public function selectOne(QueryBuilderInterface $query): null|array;

    public function selectColumn(QueryBuilderInterface $query): array;

    public function selectScalar(QueryBuilderInterface $query): mixed;

    public function update(string $resource, array $data, array $condition): int;

    public function insert(string $resource, array $data): ?string;

    public function delete(string $resource, array $condition): int;

    public function getLastInsertId(): string;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollBack(): void;
}
