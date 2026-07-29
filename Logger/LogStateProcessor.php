<?php

namespace Atlas\Logger;

use Atlas\Logger\Contract\DebugTagStorageInterface;
use Atlas\Logger\Enum\LogLevel;

final class LogStateProcessor
{
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly string $actionType,
        private readonly string $index,
    ) {}
    public function process(mixed $message, string $level, ?string $category = null, array $context = [], mixed $extras = null): LogStateDTO
    {
        $messageLog = null;
        $exceptionLog = null;

        if ($message instanceof \Throwable === true) {
            $messageLog = $message->getMessage();
            $exceptionLog = $this->throwableToOptions($message);
        }

        $actionLog = match ($this->actionType) {
            'web' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? 'unknown-web',
            'cli' => $_SERVER['argv'][1] ?? 'unknown-cli',
            default => 'unknown',
        };

        $ip = $this->actionType === 'cli'
            ? null
            : ($_SERVER['REMOTE_ADDR'] ?? null);
        $realIp = $this->actionType === 'cli'
            ? null
            : ($_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null);

        return new LogStateDTO(
            index: $this->index,
            context: implode(';', $context),
            level: LogLevel::getIndex($level),
            levelName: strtolower($level),
            action: $actionLog,
            actionType: $this->actionType,
            datetime: (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format("Y-m-d\TH:i:s.vP"),
            timestamp: (new \DateTimeImmutable('now'))->format("Y-m-d\TH:i:s.vP"),
            xDebugTag: $this->debugTagStorage->getTag(),
            message: $messageLog ?? $message,
            category: $category,
            userId: null,
            ip: $ip,
            realIp: $realIp,
            exception: $exceptionLog,
            extras: $extras,
        );
    }

    private function throwableToOptions(\Throwable $throwable): array
    {
        return [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'code' => $throwable->getCode(),
            'trace' => explode(PHP_EOL, $throwable->getTraceAsString()),
        ];
    }
}
