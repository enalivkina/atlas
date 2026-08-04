<?php

declare(strict_types=1);

namespace Atlas\Logger;

use Atlas\Logger\Contract\DebugTagStorageInterface;

final class DebugTagGenerator
{
    public function __construct(
        private readonly DebugTagStorageInterface $tagStorage,
        private readonly string $indexName,
        private readonly string $mode = 'web',
    ) { }

    public function init(): void
    {
        $this->tagStorage->setTag($this->generateTag());
    }

    public function refreshTag(): void
    {
        $this->init();
    }

    private function generateTag(): string
    {
        return md5(sprintf(
            "%s-%d-%04x",
            date('YmdHisu'),
            getmypid(),
            random_int(0, 0xFFFF),
        ));
    }
}
