<?php

declare(strict_types=1);

namespace Atlas\Console;

use Atlas\Common\AliasManager;
use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\Console\Command\CommandDefinition;
use Atlas\Console\Command\ListCommand;
use Atlas\Console\Contract\ConsoleCommandInterface;
use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleKernelInterface;
use Atlas\Console\Contract\ConsoleOutputInterface;
use Atlas\Console\Enum\ExitCode;
use Atlas\Container\ContainerInterface;
use Atlas\Logger\Contract\LoggerInterface;

final class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommand = 'kernel:list';
    private array $commandMap = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly AliasManager $aliasManager,
        private readonly ?string $appName = null,
        private readonly ?string $version = null,
    ) {
        $this->initDefaultCommands();
    }

    public function getAppName(): ?string
    {
        return $this->appName;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getCommands(): array
    {
        return $this->commandMap;
    }

    public function registerCommandNamespaces(array $commandNamespaces): void
    {
        foreach ($commandNamespaces as $commandNamespace) {
            $this->registerCommandNamespace($commandNamespace);
        }
    }

    public function handle(): int
    {
        $commandFullName = explode(':', $this->input->getFirstArgument() ?? $this->defaultCommand);

        try {
            $commandName = $this->commandMap[$commandFullName[0]][$commandFullName[1]]
                ?? throw new \InvalidArgumentException(sprintf("Команда %s не найдена", implode(':', $commandFullName)));

            $command = $this->container->get($commandName);
            $this->input->bindDefinitions($command);
            $command->execute($this->input, $this->output);
        } catch (\Throwable $e) {
            $this->output->stdErr($this->errorHandler->handle($e));

            $this->logger->error($e->getMessage());

            return ExitCode::ERROR->value;
        }

        return ExitCode::SUCCESS->value;
    }

    public function terminate(int $status): never
    {
        exit($status);
    }

    /**
     * Регистрация класса команды
     *
     * @param string $className
     * @return void
     */
    private function registerCommand(string $className): void
    {
        $commandDefinition = new CommandDefinition($className::getSignature(), $className::getDescription());

        $commandName = $commandDefinition->getCommandName();
        $commandNamespace = $commandDefinition->getCommandNamespace();

        $this->commandMap[$commandNamespace][$commandName] = $className;
    }

    /**
     * Регистрация неймспейса команды
     *
     * @param string $commandNameSpace
     * @return void
     */
    private function registerCommandNamespace(string $commandNameSpace): void
    {
        $commandNameSpace = $this->aliasManager->hasAlias($commandNameSpace) === true
            ? $this->aliasManager->buildPath($commandNameSpace)
            : $commandNameSpace;

        $possibleCommandFiles = glob($commandNameSpace . '/*.php');
        $rootPath = $this->aliasManager->buildPath('@app/');

        foreach ($possibleCommandFiles as $possibleCommandFile) {
            $commandPath = dirname($possibleCommandFile) . '/' . basename($possibleCommandFile, '.php');
            $commandPath = str_replace($rootPath, 'app\\', $commandPath);
            $commandPath = str_replace('/', '\\', $commandPath);

            if (class_exists($commandPath) === false) {
                continue;
            }

            if (is_subclass_of($commandPath, ConsoleCommandInterface::class) === false) {
                continue;
            }

            $this->registerCommand($commandPath);
        }
    }

    /**
     * Регистрация команд по-умолчанию
     *
     * @return void
     */
    private function initDefaultCommands(): void
    {
        $defaultCommands = [
            ListCommand::class,
        ];

        foreach ($defaultCommands as $className) {
            $this->registerCommand($className);
        }
    }
}
