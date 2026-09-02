<?php

declare(strict_types=1);

namespace Atlas\Logger\Contract;

interface DebugTagStorageInterface
{
    public function getTag(): ?string;
    public function setTag(string $tag): void;
}
