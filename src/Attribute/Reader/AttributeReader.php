<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute\Reader;

interface AttributeReader
{
    /**
     * @template T of object
     *
     * @param object $class
     * @param class-string<T> $attributeName
     * @param string|null $method Will be null is controller has just __invoke method.
     *
     * @throws \ReflectionException
     *
     * @return T[]
     */
    public function read(object $class, string $attributeName, ?string $method = null): array;
}
