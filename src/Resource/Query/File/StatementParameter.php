<?php

namespace Atlas\Resource\Query\File;

use Atlas\Resource\Query\StatementParameterInterface;

final readonly class StatementParameter implements StatementParameterInterface
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
