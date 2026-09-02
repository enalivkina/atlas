<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher\Contract;

use Atlas\EventDispatcher\Message;

interface EventDispatcherInterface
{
    /**
     * Подписывает наблюдателя к определенному событию
     *
     * @param string $event
     * @param ObserverInterface $observer класс наблюдателя
     * @return void
     */
    public function attach(string $event, ObserverInterface $observer): void;

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param string $event
     * @return void
     */
    public function detach(string $event): void;

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param string $event Сообщение, передаваемое наблюдателю
     * @param Message|null $message
     * @return void
     */
    public function trigger(string $event, Message|null $message = null): void;
}
