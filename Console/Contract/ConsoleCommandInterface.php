<?php

declare(strict_types=1);

namespace Atlas\Console\Contract;

interface ConsoleCommandInterface
{
    public function execute(ConsoleInputInterface $input, ConsoleOutputInterface $output): void;

    public static function getSignature(): string;

    public static function getDescription(): string;
}
