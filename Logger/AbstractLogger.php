<?php

declare(strict_types=1);

namespace Atlas\Logger;

use Atlas\Logger\Contract\LoggerInterface;
use Atlas\Logger\Enum\LogLevel;

abstract class AbstractLogger implements LoggerInterface
{
    /**
     * Логирование критической ошибки
     *
     * @param string $message сообщение
     * @return void
     */
    public function critical(string $message): void
    {
        $this->log(LogLevel::CRITICAL->value, $message);
    }

    /**
     * Логирование ошибки
     *
     * @param string $message сообщение
     * @return void
     */
    public function error(string $message): void
    {
        $this->log(LogLevel::ERROR->value, $message);
    }

    /**
     * Логирование предупредительного сообщения
     *
     * @param string $message сообщение
     * @return void
     */
    public function warning(string $message): void
    {
        $this->log(LogLevel::WARNING->value, $message);
    }

    /**
     * Логирование информационного сообщения
     *
     * @param string $message сообщение
     * @return void
     */
    public function info(string $message): void
    {
        $this->log(LogLevel::INFO->value, $message);
    }

    /**
     * Логирование сообщения отладки
     *
     * @param string $message сообщение
     * @return void
     */
    public function debug(string $message): void
    {
        $this->log(LogLevel::DEBUG->value, $message);
    }

    /**
     * Форматирование строки логирования
     *
     * @param string $level уровень логирования
     * @param string $message сообщение
     * @return string
     */
    abstract protected function formatMessage(string $level, string $message): string;

    /**
     * Запись форматированного лога в вывод
     *
     * @param string $log форматированный лог
     * @return void
     */
    abstract protected function writeLog(string $log): void;

    /**
     * Запись лога в вывод
     *
     * @param string $level уровень логирования
     * @param string $message сообщение
     * @return void
     */
    private function log(string $level, string $message): void
    {
        $log = $this->formatMessage($level, $message);
        $this->writeLog($log);
    }
}
