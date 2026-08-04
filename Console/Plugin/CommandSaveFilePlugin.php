<?php

declare(strict_types=1);

namespace Atlas\Console\Plugin;

use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Dto\OptionDTO;
use Atlas\Console\Enum\ConsoleEvent;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;
use Atlas\EventDispatcher\Message;
use Atlas\Console\Contract\ConsoleInputPluginInterface;

final class CommandSaveFilePlugin implements ObserverInterface, ConsoleInputPluginInterface
{
    private OptionDTO $option;

    public function __construct(
        private readonly ConsoleOutputInterface $output,
    ) {
        $this->option = new OptionDTO('save-file', true, 'Сохранение вывода команды в файл');
    }

    public function init(ConsoleInputInterface $input, EventDispatcherInterface $dispatcher): void
    {
        $input->addDefaultOption($this->option->name, $this->option->description);

        $dispatcher->attach(ConsoleEvent::INPUT_AFTER_VALIDATE->value, $this);
    }

    public function observe(Message $event): void
    {
        /**
         * @var ConsoleInputInterface $input
         */
        $input = $event->message;

        if ($input->hasOption($this->option->name) === false) {
            return;
        }
    }
}
