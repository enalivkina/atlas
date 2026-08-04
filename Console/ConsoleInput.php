<?php

declare(strict_types=1);

namespace Atlas\Console;

use Atlas\Console\Command\CommandDefinition;
use Atlas\Console\Contract\ConsoleCommandInterface;
use Atlas\Console\Contract\ConsoleInputInterface;
use Atlas\Console\Contract\ConsoleInputPluginInterface;
use Atlas\Console\Enum\ConsoleEvent;
use Atlas\Container\ContainerInterface;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Message;

final class ConsoleInput implements ConsoleInputInterface
{
    /**
     * @var array аргументы введенные в консоль
     */
    private array $tokens;

    /**
     * @var array аргументы, переданные как аргументы вызова в консоль
     */
    private array $arguments = [];

    private array $defaultOptions = [];

    /**
     * @var array опции переданные как аргументы вызова в консоль
     */
    private array $options = [];

    /**
     * @var CommandDefinition объект описания консольного вызова
     */
    private CommandDefinition $definition;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $argv ??= $_SERVER['argv'] ?? [];

        array_shift($argv);

        $this->tokens = $argv;
    }

    public function getDefinition(): CommandDefinition
    {
        return $this->definition;
    }

    public function addPlugins(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            if (is_subclass_of($plugin, ConsoleInputPluginInterface::class) === false) {
                throw new \InvalidArgumentException('Класс плагина не соответствует интерфейсу' . ConsoleInputPluginInterface::class);
            }

            $this->container->call($plugin, 'init');
        }
    }

    public function getFirstArgument(): string|null
    {
        return $this->tokens[0] ?? null;
    }

    public function bindDefinitions(ConsoleCommandInterface $command): void
    {
        $this->arguments = [];
        $this->options = array_fill_keys(array_keys($this->defaultOptions), false);

        $this->dispatcher->trigger(ConsoleEvent::INPUT_BEFORE_PARSE->value, new Message($this));

        $this->definition = new CommandDefinition($command::getSignature(), $command::getDescription());

        $this->parse();

        $this->dispatcher->trigger(ConsoleEvent::INPUT_AFTER_PARSE->value, new Message($this));

        $this->validate();
        $this->setDefaults();

        $this->dispatcher->trigger(ConsoleEvent::INPUT_AFTER_VALIDATE->value, new Message($this));
    }

    public function setArgumentValue(string $name, null|string $value): void
    {
        $this->arguments[$name] = is_numeric($value) === true ? (int) $value : $value;
    }

    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]) === true;
    }

    public function getArgument(string $name): int|string
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new \InvalidArgumentException(sprintf('Аргумент "%s" не существует', $name));
        }

        return $this->arguments[$name];
    }

    public function addDefaultOption(string $name, string $description): void
    {
        $this->defaultOptions[$name] = $description;
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options) === true && $this->options[$name] !== false;
    }

    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    public function enableOption(string $name): void
    {
        $this->options[$name] = true;
    }

    /**
     * Регистрация вызванного аргумента
     *
     * @param string $arg введенный аргумент
     * @return void
     */
    private function parseArgument(string $arg): void
    {
        foreach ($this->definition->getArguments() as $name) {
            if (isset($this->arguments[$name]) === true) {
                continue;
            }

            $this->setArgumentValue($name, $arg);

            return;
        }

        throw new \InvalidArgumentException('Слишком много аргументов. Ожидается аргументов: ' . count($this->arguments));
    }

    /**
     * Установка значения параметризированной опции
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    private function setOptionValue(string $name, string $value): void
    {
        $this->options[$name] = $value;
    }

    /**
     * Определение требует ли опция значния (является параметризированной)
     *
     * @param string $name
     * @return bool
     */
    private function isParametrizedOption(string $name): bool
    {
        $optionDto = $this->defaultOptions[$name] ?? $this->definition->getOptionDefinition($name);

        if ($optionDto === null) {
            throw new \InvalidArgumentException(sprintf('Опция "--%s" не существует', $name));
        }

        return $optionDto->hasValue === true;
    }

    /**
     * Преобразование введенных аргументов в консоль в аргументы и опции вызова команды
     *
     * @return void
     */
    private function parse(): void
    {
        $needParseValue = false;
        $optionName = null;

        foreach ($this->tokens as $key => $token) {
            if ($key === 0) {
                continue;
            }

            if ($needParseValue === true) {
                $this->setOptionValue($optionName, $token);
                $needParseValue = false;

                continue;
            }

            if ($this->isOption($token) === false) {
                $this->parseArgument($token);

                continue;
            }

            $optionName = $this->normalizeOptionName($token);

            $needParseValue = $this->isParametrizedOption($optionName) === true;

            $this->enableOption($optionName);
        }
    }

    /**
     * Валидация аргументов, переданных для вызова команды
     *
     * @return void
     */
    private function validate(): void
    {
        foreach ($this->definition->getArguments() as $arg) {
            if (isset($this->arguments[$arg]) === false && $this->definition->isRequired($arg) === true) {
                throw new \InvalidArgumentException('Не указан обязательный параметр ' . $arg);
            }
        }

        foreach ($this->options as $name => $value) {
            if ($this->isParametrizedOption($name) === true && $value === true) {
                throw new \InvalidArgumentException('Не задано значение параметризированной опции ' . $name);
            }
        }
    }

    /**
     * Установка значений по умолчанию для аргументов вызова команды,
     * имеющих значения по умолчанию
     *
     * @return void
     */
    private function setDefaults(): void
    {
        foreach ($this->definition->getArguments() as $arg) {
            if (isset($this->arguments[$arg]) === false && $this->definition->isRequired($arg) === false) {
                $this->setArgumentValue($arg, $this->definition->getDefaultValue($arg));
            }
        }
    }

    /**
     * Проверяет является ли токен опцией
     * опции начинаются с -- или -
     *
     * @param string $token
     * @return bool
     */
    private function isOption(string $token): bool
    {
        return $token !== $this->normalizeOptionName($token);
    }

    /**
     * Убирает из начала имени опции -- или -
     *
     * @param string $name
     * @return string
     */
    private function normalizeOptionName(string $name): string
    {
        if (str_starts_with($name, '--') === true) {
            return substr($name, strlen('--'));
        }

        if (str_starts_with($name, '-') === true) {
            return substr($name, strlen('-'));
        }

        return $name;
    }
}
