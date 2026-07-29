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
     * @param DebugTagGenerator $debugTagGenerator
     */
    public function __construct(private readonly DebugTagGenerator $debugTagGenerator)
    {
        $this->tag = $this->debugTagGenerator->getTag();
    }


    /**
     * Получить значение тега
     *
     * @return string
     */
    public function getTag(): string
    {
        if ($this->tag === null) {
            throw new \RuntimeException('Тег отладки не определен');
        }

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
