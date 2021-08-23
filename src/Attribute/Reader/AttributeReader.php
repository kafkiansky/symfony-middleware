<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute\Reader;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

interface AttributeReader
{
    public const ATTRIBUTE = Middleware::class;

    /**
     * @param object $class
     * @param string|null $method Will be null is controller has just __invoke method.
     *
     * @throws \ReflectionException
     *
     * @return Middleware[]
     */
    public function read(object $class, ?string $method = null): array;
}
