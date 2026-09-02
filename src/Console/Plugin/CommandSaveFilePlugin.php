<?php

declare(strict_types=1);

namespace Atlas\Console\Plugin;

use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Enum\ConsoleEvent;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;
use Atlas\EventDispatcher\Message;
use Atlas\Console\Contract\ConsoleInputPluginInterface;

final class CommandSaveFilePlugin implements ObserverInterface, ConsoleInputPluginInterface
{
    private array $option;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->option = ['name' => 'save-file', 'hasValue' => true, 'description' => 'Сохранение вывода команды в файл'];
    }

    public function init(): void
    {
        $this->input->addDefaultOption($this->option['name'], $this->option['description']);

        $this->dispatcher->attach(ConsoleEvent::INPUT_AFTER_VALIDATE->value, $this);
    }

    public function observe(Message $event): void
    {
        /**
         * @var ConsoleInputInterface $input
         */
        $input = $event->message;

        if ($input->hasOption($this->option['name']) === false) {
            return;
        }
    }
}
