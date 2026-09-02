<?php

declare(strict_types=1);

namespace Atlas\Resource\Query\MySql;

use Atlas\Resource\Query\StatementParameterInterface;

final readonly class StatementParameter implements StatementParameterInterface
{
    public function __construct(
        public string $sql,
        public array $bindings,
    ) {}
}
