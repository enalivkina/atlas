<?php

declare(strict_types=1);

namespace Atlas\Console\Plugin;

use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleInputPluginInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Dto\OptionDTO;
use Atlas\Console\Enum\ConsoleEvent;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;
use Atlas\EventDispatcher\Message;

final class CommandDetachOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private OptionDTO $option;

    public function __construct(
        private readonly ConsoleOutputInterface $output,
    ) {
        $this->option = new OptionDTO('detach', false, 'Перевод процесса в фон');
    }

    public function init(ConsoleInputInterface $input, EventDispatcherInterface $dispatcher): void
    {
        $input->addDefaultOption($this->option);

        $dispatcher->attach(ConsoleEvent::INPUT_AFTER_PARSE->value, $this);
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

        $this->output->detach();
    }
}
