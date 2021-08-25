<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Attribute\Reader\AttributeReader;
use Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\ServiceLocatorMiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\Psr\Adapter\PsrHttpMessageBridgePsrRequestTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\Adapter\PsrHttpMessageBridgePsrResponseTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\DefaultPsrRequestCloner;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestCloner;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\CopyAttributesFromRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        CopyAttributesFromRequest::$attributes = [];
    }

    final protected function createPsrRequestTransformer(): PsrRequestTransformer
    {
        return new PsrHttpMessageBridgePsrRequestTransformer(
            $this->createPsrHttpFactory(),
            new HttpFoundationFactory(),
        );
    }

    final protected function createPsrResponseTransformer(): PsrResponseTransformer
    {
        return new PsrHttpMessageBridgePsrResponseTransformer(
            $this->createPsrHttpFactory(),
            new HttpFoundationFactory(),
        );
    }

    final protected function createPsrRequestCloner(): PsrRequestCloner
    {
        return new DefaultPsrRequestCloner();
    }

    final protected function createAttributeReader(): AttributeReader
    {
        return new ClassMethodAttributeReader();
    }

    /**
     * @param array<class-string<MiddlewareInterface>, MiddlewareInterface[] $middlewares
     * @param array<string, array{if?: bool, middlewares: class-string<MiddlewareInterface>[]}> $groups
     */
    final protected function createMiddlewareGatherer(
        array $middlewares = [],
        array $groups = ['global' => ['middlewares' => []]]
    ): MiddlewareGatherer {
        return new MiddlewareGatherer(
            new ServiceLocatorMiddlewareRegistry(new ArrayContainer($middlewares), $groups)
        );
    }

    private function createPsrHttpFactory(): PsrHttpFactory
    {
        $psr17 = new Psr17Factory();

        return new PsrHttpFactory($psr17, $psr17, $psr17, $psr17);
    }
}
