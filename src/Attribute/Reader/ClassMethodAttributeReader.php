<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute\Reader;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

final class ClassMethodAttributeReader implements AttributeReader
{
    /**
     * {@inheritdoc}
     */
    public function read(object $class, ?string $method = null): array
    {
        $attributes = (new \ReflectionClass($class))->getAttributes(AttributeReader::ATTRIBUTE);

        if ($method !== null) {
            $attributes = array_merge(
                $attributes,
                (new \ReflectionMethod($class, $method))->getAttributes(AttributeReader::ATTRIBUTE)
            );
        }

        return array_map(static function (\ReflectionAttribute $attribute): object {
            /** @var Middleware */
            return $attribute->newInstance();
        }, $attributes);
    }
}
