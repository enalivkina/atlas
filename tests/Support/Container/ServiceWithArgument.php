<?php

declare(strict_types=1);

namespace Atlas\tests\Support\Container;

final class ServiceWithArgument
{
    public function __construct(
        public string $name,
    ) {}
}
