<?php

declare(strict_types=1);

namespace Atlas\Console\Plugin;

use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleInputPluginInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Dto\ArgumentDTO;
use Atlas\Console\Dto\OptionDTO;
use Atlas\Console\Enum\ConsoleEvent;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;
use Atlas\EventDispatcher\Message;

final class CommandInteractiveOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private OptionDTO $option;

    public function __construct(
        private readonly ConsoleOutputInterface $output,
    ) {
        $this->option = new OptionDTO('interactive', false, 'Интерактивный ввод аргументов');
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

        $command = $input->getDefinition();

        foreach ($command->getArguments() as $argumentName) {
            $argument = $command->getArgumentDefinition($argumentName);

            $this->printArgumentInfo($argument);

            $value = $argument->default;
            $userInput = trim(fgets(STDIN));

            if (strlen($userInput) !== 0) {
                $value = $userInput;
            }

            if (is_null($value) === false) {
                $input->setArgumentValue($argumentName, $value);
            }
        }
    }

    /**
     * Печать строки запроса ввода агрумента
     *
     * @param ArgumentDTO $argument
     * @return void
     */
    private function printArgumentInfo(ArgumentDTO $argument): void
    {
        $this->output->success("Введите аргумент {$argument->name}");

        if ($argument->description !== '') {
            $this->output->success(" ({$argument->description})");
        }

        if (is_null($argument->default) === false) {
            $this->output->success(" [{$argument->default}]");
        }

        $this->output->success(':');
        $this->output->writeLn();
    }
}
