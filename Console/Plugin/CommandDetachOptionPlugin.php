<?php

declare(strict_types=1);

namespace Atlas\Console\Plugin;

use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleInputPluginInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Enum\ConsoleEvent;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;
use Atlas\EventDispatcher\Message;

final class CommandDetachOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private array $option;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    )
    {
        $this->option = [
            'name' => 'detach',
            'hasValue' => false,
            'description' => 'Перевод процесса в фон',
        ];
    }

    public function init(): void
    {
        $this->input->addDefaultOption($this->option['name'], $this->option['description']);

        $this->dispatcher->attach(ConsoleEvent::INPUT_AFTER_PARSE->value, $this);
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

        $this->output->detach();
    }
}
