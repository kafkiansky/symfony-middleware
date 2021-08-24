<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ControllerMethod;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\InvokableController;

final class ClassMethodAttributesReaderTest extends TestCase
{
    public function testReadFromControllerMethod(): void
    {
        $reader = new ClassMethodAttributeReader();

        $attributes = $reader->read(new ControllerMethod(), 'index');

        self::assertCount(2, $attributes);

        self::assertEquals(['test'], $attributes[0]->toArray());
        self::assertEquals(['api'], $attributes[1]->toArray());
    }

    public function testReadFromInvokableController(): void
    {
        $reader = new ClassMethodAttributeReader();

        $attributes = $reader->read(new InvokableController());

        self::assertCount(1, $attributes);
        self::assertEquals(['test'], $attributes[0]->toArray());
    }
}
