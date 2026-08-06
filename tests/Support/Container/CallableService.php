<?php

declare(strict_types=1);

namespace Atlas\tests\Support\Container;

final class CallableService
{
    public function handle(
        DependencyService $dependency,
    ): string {
        return 'handled';
    }

    public function withArgument(string $name): string
    {
        return $name;
    }

    public function withDefault(string $value = 'default'): string
    {
        return $value;
    }
}
