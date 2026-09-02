<?php

declare(strict_types=1);

namespace Atlas\Console\Command;

use Atlas\Console\Contract\ConsoleCommandInterface;
use Atlas\Console\Contract\ConsoleKernelInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Enum\Color;

final class ListCommand implements ConsoleCommandInterface
{
    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly ConsoleKernelInterface $kernel,
    ) {}

    public static function getSignature(): string
    {
        return 'kernel:list';
    }

    public static function getDescription(): string
    {
        return 'Команда вывода информации о консольном ядре';
    }

    public function execute(): void
    {
        $this->output->info($this->kernel->getAppName());
        $this->output->info(' ' . $this->kernel->getVersion());
        $this->output->writeLn(2);
        $this->output->warning("Фреймворк создан {$this->kernel->getAppName()}.\nЯвляется платформой для изучения базового поведения приложения созданного на PHP.\nФреймворк не является production-ready реализацией и не предназначен для коммерческого использования.");
        $this->output->writeLn(2);

        $this->output->success('Доступные опции:');

        foreach ($this->input->getDefaultOptions() as $defaultOption) {
            $this->output->writeLn();
            $this->output->success('  --' . $defaultOption->name);

            if (is_null($defaultOption->description) === false) {
                $this->output->stdout(' - ' . $defaultOption->description);
            }
        }

        $this->output->writeLn(2);

        $this->output->success('Вызов:');
        $this->output->writeLn();
        $this->output->stdout('  команда [аргументы] [опции]');
        $this->output->writeLn(2);

        $this->output->stdout('Доступные команды:');

        foreach ($this->kernel->getCommands() as $commandsNamespace => $commands) {
            $this->output->writeLn();
            $this->output->stdout("  Неймспейс $commandsNamespace:", [Color::FG_GREEN->value]);

            foreach ($commands as $commandName => $command) {
                $this->output->writeLn();
                $this->output->success('    ' . $commandName);
                $this->output->stdout(' - ' . $command::getDescription());
            }
        }

        $this->output->writeLn(2);
    }

    public function isHidden(): bool
    {
        return false;
    }
}
