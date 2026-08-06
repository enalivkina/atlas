<?php

namespace Atlas\tests\Unit\Container;

use Atlas\Container\ContainerInterface;
use Atlas\Container\DependencyNotFoundException;
use Atlas\Container\DIContainer;
use Atlas\tests\Support\Container\CallableService;
use Atlas\tests\Support\Container\DependencyService;
use Atlas\tests\Support\Container\LoggerInterfaceMock;
use Atlas\tests\Support\Container\LoggerMock;
use Atlas\tests\Support\Container\ServiceWithArgument;
use Atlas\tests\Support\Container\ServiceWithDefaultValue;
use Atlas\tests\Support\Container\ServiceWithDependency;
use Atlas\tests\Support\Container\SimpleService;
use Codeception\PHPUnit\TestCase;
use LogicException;
use ReflectionProperty;

final class DIContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        $property = new ReflectionProperty(DIContainer::class, 'instance');
        $property->setValue(null, null);
    }

    public function testCreatesContainer(): void
    {
        $container = DIContainer::create();

        self::assertInstanceOf(
            DIContainer::class,
            $container,
        );
    }

    public function testCannotCreateContainerTwice(): void
    {
        DIContainer::create();

        self::expectException(LogicException::class);

        DIContainer::create();
    }

    public function testReturnsItselfByContainerInterface(): void
    {
        $container = DIContainer::create();

        self::assertSame(
            $container,
            $container->get(ContainerInterface::class),
        );
    }

    public function testBuildSimpleClass(): void
    {
        $container = DIContainer::create();

        $service = $container->build(SimpleService::class);

        self::assertInstanceOf(
            SimpleService::class,
            $service,
        );
    }

    public function testAutomaticallyResolvesDependencies(): void
    {
        $container = DIContainer::create();

        $service = $container->build(ServiceWithDependency::class);

        self::assertInstanceOf(
            DependencyService::class,
            $service->dependency,
        );
    }

    public function testUsesDefaultConstructorValue(): void
    {
        $container = DIContainer::create();

        $service = $container->build(ServiceWithDefaultValue::class);

        self::assertSame(
            'default',
            $service->name,
        );
    }

    public function testCanPassConstructorArguments(): void
    {
        $container = DIContainer::create();

        $service = $container->build(
            ServiceWithArgument::class,
            [
                'name' => 'Atlas',
            ],
        );

        self::assertSame(
            'Atlas',
            $service->name,
        );
    }

    public function testThrowsExceptionForUnknownDependency(): void
    {
        $container = DIContainer::create();

        self::expectException(
            DependencyNotFoundException::class,
        );

        $container->get(
            'UnknownService',
        );
    }

    public function testResolvesDefinitionClosure(): void
    {
        $container = DIContainer::create([
            'definitions' => [
                LoggerInterfaceMock::class => function () {
                    return new LoggerMock();
                },
            ],
        ]);


        $logger = $container->get(
            LoggerInterfaceMock::class,
        );


        self::assertInstanceOf(
            LoggerMock::class,
            $logger,
        );
    }

    public function testResolvesSingletonOnlyOnce(): void
    {
        $container = DIContainer::create([
            'singletons' => [
                LoggerInterfaceMock::class => LoggerMock::class,
            ],
        ]);


        $first = $container->get(
            LoggerInterfaceMock::class,
        );

        $second = $container->get(
            LoggerInterfaceMock::class,
        );


        self::assertSame(
            $first,
            $second,
        );
    }

    public function testHasReturnsTrueForExistingClass(): void
    {
        $container = DIContainer::create([
            'definitions' => [
                SimpleService::class => SimpleService::class,
            ],
        ]);

        self::assertTrue(
            $container->has(SimpleService::class),
        );
    }

    public function testHasReturnsFalseForUnknownService(): void
    {
        $container = DIContainer::create();


        self::assertFalse(
            $container->has('Unknown'),
        );
    }

    public function testCallsMethodWithDependencyInjection(): void
    {
        $container = DIContainer::create();


        $result = $container->call(
            CallableService::class,
            'handle',
        );


        self::assertSame(
            'handled',
            $result,
        );
    }

    public function testCallsMethodWithProvidedArgument(): void
    {
        $container = DIContainer::create();


        $service = new CallableService();


        $result = $container->call(
            $service,
            'withArgument',
            [
                'name' => 'Atlas',
            ],
        );


        self::assertSame(
            'Atlas',
            $result,
        );
    }

    public function testCallsMethodUsesDefaultValue(): void
    {
        $container = DIContainer::create();


        $service = new CallableService();


        $result = $container->call(
            $service,
            'withDefault',
        );


        self::assertSame(
            'default',
            $result,
        );
    }
}
