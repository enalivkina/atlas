<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher\Contract;

use Atlas\EventDispatcher\Event;

interface EventDispatcherInterface
{
    /**
     * Конфигурирует EventDispatcher с использованием предоставленного массива конфигурации
     *
     * @param array $config массив конфигурации,
     * где каждый элемент представляет собой массив вида
     * ['SOME_EVENT_NAME' => [BarObserver::class, BazObzerver::class]]
     * @return void
     */
    public function configure(array $config): void;

    /**
     * Подписывает наблюдателя к определенному событию
     *
     * @param string $eventName имя события, к которому присоединяется наблюдатель
     * @param string $observer класс наблюдателя
     * @return void
     */
    public function attach(string $eventName, string $observer): void;

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param string $eventName имя события, от которого отписывается наблюдатель
     * @param string|callable $observer класс/объект наблюдателя, пара [класс/объект, метод] или колбек функция
     * @return void
     */
    public function detach(string $eventName, string|callable $observer): void;

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param string $eventName Имя события, которое будет запущено
     * @param Event $event Сообщение, передаваемое наблюдателю
     * @return void
     */
    public function trigger(string $eventName, Event $event): void;
}
