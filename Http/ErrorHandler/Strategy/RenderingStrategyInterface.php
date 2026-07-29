<?php

declare(strict_types=1);

namespace Atlas\Http\ErrorHandler\Strategy;

use Throwable;

interface RenderingStrategyInterface
{
    public function execute(Throwable $throwable): string;
}
