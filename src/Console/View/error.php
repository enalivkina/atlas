<?php

declare(strict_types=1);

use Atlas\Console\AnsiDecorator;
use Atlas\Console\Enum\Color;

/**
 * @var Throwable $exception
 * @var AnsiDecorator $decorator
 */

echo $decorator->decorate(
    sprintf('[%1$s] Uncaught %1$s: %2$s', get_class($exception), $exception->getMessage()),
    [Color::BG_RED->value, Color::FG_WHITE->value],
) . PHP_EOL . PHP_EOL;

foreach (explode("\n", $exception->getTraceAsString()) as $line) {
    echo $decorator->decorate($line, [Color::FG_WHITE->value]) . PHP_EOL . PHP_EOL;
}
