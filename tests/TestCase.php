<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Psr\Adapter\PsrHttpMessageBridgePsrRequestTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\Adapter\PsrHttpMessageBridgePsrResponseTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\CopyAttributesFromRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
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
            $this->createPsrHttpFactory()
        );
    }

    final protected function createPsrResponseTransformer(): PsrResponseTransformer
    {
        return new PsrHttpMessageBridgePsrResponseTransformer(
            $this->createPsrHttpFactory(),
            new HttpFoundationFactory()
        );
    }

    private function createPsrHttpFactory(): PsrHttpFactory
    {
        $psr17 = new Psr17Factory();

        return new PsrHttpFactory($psr17, $psr17, $psr17, $psr17);
    }
}
