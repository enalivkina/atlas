<?php

declare(strict_types=1);

namespace Atlas\Console\Command;

use Atlas\Console\Dto\CommandInfoDTO;

final class CommandDefinition
{
    /**
     * Информация о команде: имя, неймспейс, описание
     * @var CommandInfoDTO
     */
    private CommandInfoDTO $commandInfoDTO;

    private array $arguments = [];

    private array $options = [];

    public function __construct(string $signature, string $description)
    {
        $this->commandInfoDTO = new CommandInfoDTO();
        $this->initDefinitions($signature);
        $this->commandInfoDTO->description = $description;
    }

    /**
     * Возврат имен аргументов команды
     *
     * @return array
     */
    public function getArguments(): array
    {
        return array_keys($this->arguments);
    }

    /**
     * Возврат имен опций команды
     *
     * @return array
     */
    public function getOptions(): array
    {
        return array_keys($this->options);
    }

    /**
     * Возврат имени команды
     *
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->commandInfoDTO->name;
    }

    /**
     * Возврат неймспейса команды
     *
     * @return string
     */
    public function getCommandNamespace(): string
    {
        return $this->commandInfoDTO->namespace;
    }

    /**
     * Возврат описания команды
     *
     * @return string
     */
    public function getCommandDescription(): string
    {
        return $this->commandInfoDTO->description;
    }

    /**
     * Возврат параметров, определенных для аргумента
     *
     * @param string $name имя аругмента
     * @return array
     */
    public function getArgumentDefinition(string $name): array
    {
        return $this->arguments[$name] ?? [];
    }

    /**
     * Возврат параметров, определенных для опции
     *
     * @param string $name имя опции
     * @return array
     */
    public function getOptionDefinition(string $name): array
    {
        return $this->options[$name] ?? [];
    }

    /**
     * Определение аргумента, установленного обязательным
     *
     * @param string $name имя аргумента
     * @return bool
     */
    public function isRequired(string $name): bool
    {
        return isset($this->arguments[$name]) === true && $this->arguments[$name]['required'] === true;
    }

    /**
     * Возврат значения по умолчанию,
     * установленного для аргумента
     *
     * @param string $name имя аргумента
     * @return mixed
     */
    public function getDefaultValue(string $name): mixed
    {
        return ($this->arguments[$name] ?? null)['default'];
    }

    /**
     * Формирование параметров, определенных для опций и аргументов
     *
     * @param string $signature строка описания команды
     * @return void
     */
    private function initDefinitions(string $signature): void
    {
        if (preg_match('/^([\w\S]+)/', $signature, $nameMatches) !== 1) {
            throw new \InvalidArgumentException('Не удалось определить имя команды');
        }

        $fullName = explode(':', $nameMatches[1]);

        if (count($fullName) !== 2) {
            throw new \InvalidArgumentException('Не удалось выделить неймспейс команды');
        }

        $this->commandInfoDTO->namespace = $fullName[0];
        $this->commandInfoDTO->name = $fullName[1];

        preg_match_all('/{\s*(.*?)\s*}/', $signature, $tokenMatches);

        foreach ($tokenMatches[1] as $tokenMatch) {
            if (preg_match('/--(.*)/', $tokenMatch) === 1) {
                $this->initOption($tokenMatch);

                continue;
            }

            $this->initArgument($tokenMatch);
        }
    }

    /**
     * Определение параметров, определенных для опций
     *
     * @param string $option строка зарегистрированной опции
     * @return void
     */
    private function initOption(string $option): void
    {
        if (preg_match('/^--([\w-]+)/', $option, $nameMatches) !== 1) {
            throw new \InvalidArgumentException("Не удалось определить имя опции: $option");
        }

        $name = $nameMatches[1];

        if (isset($this->options[$name]) === true) {
            throw new \InvalidArgumentException("Опция '$name' уже определена");
        }

        $optionDTO = [
            'name' => $name,
            'hasValue' => str_contains($option, '='),
        ];

        if (preg_match('/:(.*)$/', $option, $descriptionMatch) === 1) {
            $optionDTO['description'] = trim($descriptionMatch[1]);
        }

        $this->options[$name] = $optionDTO;
    }

    /**
     * Определение параметров, определенных для аргументов
     *
     * @param string $arg строка зарегистрированного аргумента
     * @return void
     */
    private function initArgument(string $arg): void
    {
        if (preg_match('/^([\w-]+)/', $arg, $nameMatches) !== 1) {
            throw new \InvalidArgumentException("Не удалось определить имя аргумента: $arg");
        }

        $name = $nameMatches[1];

        if (isset($this->arguments[$name])) {
            throw new \InvalidArgumentException("Аргумент '$name' уже определен");
        }

        $argumentDTO = ['name' => $name, 'required' => str_contains($arg, '?') === false];

        if (preg_match('/:(.*)$/', $arg, $descriptionMatch) === 1) {
            $argumentDTO['description'] = trim($descriptionMatch[1]);
        }

        if (preg_match('/=(\w+)/', $arg, $defaultMatch) === 1) {
            $argumentDTO['default'] = $defaultMatch[1];
        }

        $this->arguments[$name] = $argumentDTO;
    }
}
