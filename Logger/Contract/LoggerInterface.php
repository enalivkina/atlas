<?php

declare(strict_types=1);

namespace Atlas\Logger\Contract;

interface LoggerInterface
{
    public function critical(mixed $message): void;
    public function error(mixed $message): void;
    public function warning(mixed $message): void;
    public function info(mixed $message): void;
    public function debug(mixed $message): void;
}
