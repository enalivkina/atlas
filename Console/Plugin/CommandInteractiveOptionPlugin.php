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

final class CommandInteractiveOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private array $option;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->option = ['name' => 'interactive', 'hasValue' => false, 'description' => 'Интерактивный ввод аргументов'];
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

        $command = $input->getDefinition();

        foreach ($command->getArguments() as $argumentName) {
            $argument = $command->getArgumentDefinition($argumentName);

            $this->printArgumentInfo($argument);

            $value = $argument['default'];
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
     * @param array $argument
     * @return void
     */
    private function printArgumentInfo(array $argument): void
    {
        $this->output->success("Введите аргумент {$argument['name']}");

        if ($argument['description'] !== '') {
            $this->output->success(" ({$argument['description']})");
        }

        if (is_null($argument['default']) === false) {
            $this->output->success(" [{$argument['default']}]");
        }

        $this->output->success(':');
        $this->output->writeLn();
    }
}
