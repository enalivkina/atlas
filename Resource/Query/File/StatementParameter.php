<?php

namespace Atlas\Resource\Query\File;

final readonly class StatementParameter
{
    public function __construct(
        public string $resource,
        public array $selectFields = [],
        public array $whereClause = [],
        public array $orderByClause = [],
        public ?int $limit = null,
        public ?int $offset = null,
    ) {}
}
