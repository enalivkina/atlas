<?php

namespace Atlas\Logger;

final readonly class LogStateDTO
{
    public function __construct(
        public string $index,
        public string $context,
        public int $level,
        public string $levelName,
        public string $action,
        public string $actionType,
        public string $datetime,
        public string $timestamp,
        public string $xDebugTag,
        public string $message,
        public ?string $category = null,
        public ?int $userId = null,
        public ?string $ip = null,
        public ?string $realIp = null,
        public ?array $exception = null,
        public ?string $extras = null,
    ) {}

    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'context' => $this->context,
            'level' => $this->level,
            'level-name' => $this->levelName,
            'action' => $this->action,
            'action-type' => $this->actionType,
            'datetime' => $this->datetime,
            'timestamp' => $this->timestamp,
            'x-debug-tag' => $this->xDebugTag,
            'message' => $this->message,
            'category' => $this->category,
            'user-id' => $this->userId,
            'ip' => $this->ip,
            'real-ip' => $this->realIp,
            'exception' => $this->exception,
            'extras' => $this->extras,
        ];
    }
}
