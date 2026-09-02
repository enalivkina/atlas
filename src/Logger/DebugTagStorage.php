<?php

declare(strict_types=1);

namespace Atlas\Logger;

use Atlas\Logger\Contract\DebugTagStorageInterface;

final class DebugTagStorage implements DebugTagStorageInterface
{
    /**
     * Строка значения тега отладки
     *
     * @var string|null
     */
    private ?string $tag;

    /**
     * Получить значение тега
     *
     * @return string|null
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * Установить значение тега
     *
     * @param string $tag
     * @return void
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }
}
