<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute\Reader;

final class ClassMethodAttributeReader implements AttributeReader
{
    /**
     * @template T of object
     *
     * @param object $class
     * @param class-string<T> $attributeName
     * @param string|null $method
     *
     * @throws \ReflectionException
     *
     * @return T[]
     */
    public function read(object $class, string $attributeName, ?string $method = null): array
    {
        $attributes = (new \ReflectionClass($class))->getAttributes($attributeName);

        if ($method !== null) {
            $attributes = array_merge(
                $attributes,
                (new \ReflectionMethod($class, $method))->getAttributes($attributeName)
            );
        }

        return array_map(static function (\ReflectionAttribute $attribute): object {
            /** @var T */
            return $attribute->newInstance();
        }, $attributes);
    }
}
