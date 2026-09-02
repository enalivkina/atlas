<?php

declare(strict_types=1);

namespace Atlas\Logger\Contract;

interface LoggerInterface
{
    public function critical(string $message): void;
    public function error(string $message): void;
    public function warning(string $message): void;
    public function info(string $message): void;
    public function debug(string $message): void;
}
