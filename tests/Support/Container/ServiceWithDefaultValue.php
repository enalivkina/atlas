<?php

declare(strict_types=1);

namespace Atlas\tests\Support\Container;

final class ServiceWithDefaultValue
{
    public function __construct(
        public string $name = 'default',
    ) {}
}