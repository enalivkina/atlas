<?php

declare(strict_types=1);

namespace Atlas\Resource\Query\MySql;

final readonly class StatementParameter
{
    public function __construct(
        public string $sql,
        public array $bindings,
    ) {}
}
