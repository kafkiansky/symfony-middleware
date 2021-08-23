<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\ServiceLocatorMiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\EarlyReturnMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyRequestMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyResponseMiddleware;

final class MiddlewareGathererTest extends TestCase
{
    public function testMiddlewareGathered(): void
    {
        $middlewareGatherer = new MiddlewareGatherer(
            $this->createRegistry([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ])
        );

        $middlewares = $middlewareGatherer->gather([new Middleware([ModifyRequestMiddleware::class])]);

        self::assertCount(1, $middlewares);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[0]);
    }

    public function testMiddlewareAndGroupsGathered(): void
    {
        $middlewareGatherer = new MiddlewareGatherer(
            $this->createRegistry([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
            ], [
                'global' => ['middlewares' => []],
                'api' => ['middlewares' => [ModifyResponseMiddleware::class]],
            ])
        );

        $middlewares = $middlewareGatherer->gather([new Middleware([ModifyRequestMiddleware::class, 'api'])]);

        self::assertCount(2, $middlewares);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[0]);
        self::assertInstanceOf(ModifyResponseMiddleware::class, $middlewares[1]);
    }

    public function testGlobalMiddlewaresWithOtherIsGathered(): void
    {
        $middlewareGatherer = new MiddlewareGatherer(
            $this->createRegistry([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
                EarlyReturnMiddleware::class => new EarlyReturnMiddleware(),
            ], [
                'global' => ['middlewares' => [EarlyReturnMiddleware::class]],
                'api' => ['middlewares' => [ModifyResponseMiddleware::class]],
            ])
        );

        $middlewares = $middlewareGatherer->gather([new Middleware(['api'])]);

        self::assertCount(2, $middlewares);
        self::assertInstanceOf(EarlyReturnMiddleware::class, $middlewares[0]);
        self::assertInstanceOf(ModifyResponseMiddleware::class, $middlewares[1]);
    }

    public function testGlobalMiddlewaresAlwaysUnique(): void
    {
        $middlewareGatherer = new MiddlewareGatherer(
            $this->createRegistry([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
                EarlyReturnMiddleware::class => new EarlyReturnMiddleware(),
            ], [
                'global' => ['middlewares' => [EarlyReturnMiddleware::class, ModifyResponseMiddleware::class]],
                'api' => ['middlewares' => [ModifyResponseMiddleware::class]],
                'api_2' => ['middlewares' => [ModifyRequestMiddleware::class]],
            ])
        );

        $middlewares = $middlewareGatherer->gather([
            new Middleware(['api', 'api_2', 'api', EarlyReturnMiddleware::class]),
            new Middleware(['api']),
        ]);

        self::assertCount(3, $middlewares);
        self::assertInstanceOf(EarlyReturnMiddleware::class, $middlewares[0]);
        self::assertInstanceOf(ModifyResponseMiddleware::class, $middlewares[1]);
        self::assertInstanceOf(ModifyRequestMiddleware::class, $middlewares[2]);
    }

    private function createRegistry(array $middlewares, array $groups = ['global' => ['middlewares' => []]]): MiddlewareRegistry
    {
        return new ServiceLocatorMiddlewareRegistry(new ArrayContainer($middlewares), $groups);
    }
}
