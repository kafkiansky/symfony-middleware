<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Middleware\Registry\ServiceLocatorMiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareNotConfigured;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyRequestMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyResponseMiddleware;

final class ServiceLocatorMiddlewareRegistryTest extends TestCase
{
    public function testMiddlewareCanFound(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => []],
            ]
        );

        $middlewares = $registry->byName(ModifyRequestMiddleware::class);
        self::assertCount(1, $middlewares);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[0]);

        $middlewares = $registry->byName(ModifyResponseMiddleware::class);
        self::assertCount(1, $middlewares);
        self::assertInstanceOf(ModifyResponseMiddleware::class, $middlewares[0]);
    }

    public function testMiddlewareGroupCanBeFound(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => []],
                'api' => ['middlewares' => [ModifyRequestMiddleware::class, ModifyResponseMiddleware::class]],
            ]
        );

        $middlewares = $registry->byName('api');
        self::assertCount(2, $middlewares);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[0]);
        self::assertInstanceOf(ModifyResponseMiddleware::class, $middlewares[1]);
    }

    public function testGlobalMiddleware(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => [ModifyRequestMiddleware::class]],
            ]
        );

        $middlewares = $registry->byName('global');
        self::assertCount(1, $middlewares);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[0]);
    }

    public function testMiddlewareCanFoundButNotExecutedBecauseIfReturnFalse(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => []],
                'api' => [
                    'if' => false,
                    'middlewares' => [ModifyRequestMiddleware::class],
                ]
            ]
        );

        $middlewares = $registry->byName('api');
        self::assertCount(0, $middlewares);
    }

    public function testMiddlewareCanFoundAndExecutedBecauseIfReturnTrue(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => []],
                'api' => [
                    'if' => true,
                    'middlewares' => [ModifyRequestMiddleware::class],
                ]
            ]
        );

        $middlewares = $registry->byName('api');
        self::assertCount(1, $middlewares);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[0]);
    }

    public function testMiddlewareGroupCannotBeFound(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => []],
            ]
        );

        self::expectException(MiddlewareNotConfigured::class);
        self::expectExceptionMessage('The middleware or group "api" was not configured. Make sure it implements the "Psr\Http\Server\MiddlewareInterface" interface or group is defined.');
        $registry->byName('api');
    }

    public function testMiddlewareCannotBeFound(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(new ArrayContainer([]), ['global' => ['middlewares' => []]]);

        self::expectException(MiddlewareNotConfigured::class);
        self::expectExceptionMessage('The middleware or group "Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyRequestMiddleware" was not configured. Make sure it implements the "Psr\Http\Server\MiddlewareInterface" interface or group is defined.');
        $registry->byName(ModifyRequestMiddleware::class);
    }

    public function testEmptyGroup(): void
    {
        $registry = new ServiceLocatorMiddlewareRegistry(
            new ArrayContainer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ]),
            [
                'global' => ['middlewares' => []],
                'api' => []
            ]
        );

        self::expectException(MiddlewareNotConfigured::class);
        self::expectExceptionMessage('Middlewares groups cannot empty, but the group "api" is.');
        $registry->byName('api');
    }
}
