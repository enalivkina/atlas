<?php

declare(strict_types=1);

namespace Atlas\Console\Contract;

interface ConsoleCommandInterface
{
    public function execute(): void;

    public static function getSignature(): string;

    public static function getDescription(): string;
    public function isHidden(): bool;
}
