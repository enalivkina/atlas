<?php

declare(strict_types=1);

namespace Atlas\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public static function create(array $config = []): self;
    /**
     * Создание экземпляра объекта в зависимости от имени класса
     *
     * @param string $dependencyName имя зависимости, для которой нужно создать объект
     * @param array $args предподготовленные параметры конструктора
     * @return object возвращает экземпляр объекта в зависимости от имени класса
     */
    public function build(string $dependencyName, array $args = []): object;

    /**
     * Выполняет вызов указанного обработчика (callable или объекта)
     * с внедрением зависимостей в качестве параметров метода или аргументов функции
     *
     * @param object|string $handler обработчик
     * @param string $method имя метода
     * @param array $args предподготовленные параметры конструктора
     * @return mixed Результат выполнения обработчика
     */
    public function call(object|string $handler, string $method, array $args = []): mixed;

    public function registerSingleton(string|callable $identifier, string $dependencyName, array $args = []): void;
    public function get(string $id): object;
    public function has(string $id): bool;
}
