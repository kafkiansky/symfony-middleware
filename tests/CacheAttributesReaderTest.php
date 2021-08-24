<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Attribute\Reader\CacheAttributesReader;
use Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ControllerMethod;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\InvokableController;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CacheAttributesReaderTest extends TestCase
{
    public function testAttributesCacheOnControllerMethod(): void
    {
        $cache = new ArrayAdapter();

        self::assertCount(0, $cache->getValues());

        $cacheReader = new CacheAttributesReader($cache, new ClassMethodAttributeReader());

        $attributes = $cacheReader->read(new ControllerMethod(), 'index');

        self::assertCount(2, $attributes);
        self::assertEquals(['test'], $attributes[0]->toArray());
        self::assertEquals(['api'], $attributes[1]->toArray());

        self::assertTrue(
            $cache->hasItem('symfony.middleware.KafkianskySymfonyMiddlewareTestsstubsControllerMethod.index')
        );

        // Get from cache
        $attributes = $cacheReader->read(new ControllerMethod(), 'index');

        self::assertCount(2, $attributes);
        self::assertEquals(['test'], $attributes[0]->toArray());
        self::assertEquals(['api'], $attributes[1]->toArray());
    }

    public function testAttributesCacheOnInvokableController(): void
    {
        $cache = new ArrayAdapter();

        $cacheReader = new CacheAttributesReader($cache, new ClassMethodAttributeReader());

        $attributes = $cacheReader->read(new InvokableController());

        self::assertCount(1, $attributes);
        self::assertEquals(['test'], $attributes[0]->toArray());

        self::assertTrue(
            $cache->hasItem('symfony.middleware.KafkianskySymfonyMiddlewareTestsstubsInvokableController')
        );

        // Get from cache
        $attributes = $cacheReader->read(new InvokableController());

        self::assertCount(1, $attributes);
        self::assertEquals(['test'], $attributes[0]->toArray());
    }
}
