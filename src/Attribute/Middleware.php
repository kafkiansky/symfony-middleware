<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute;

use Psr\Http\Server\MiddlewareInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Middleware
{
    /**
     * @param class-string<MiddlewareInterface>[]|string[] $list
     */
    public function __construct(public readonly array $list)
    {
    }

    /**
     * @return class-string<MiddlewareInterface>[]|string[]
     */
    public function toArray(): array
    {
        return $this->list;
    }

    /**
     * @param class-string<MiddlewareInterface>[]|string[] $list
     *
     * @return Middleware
     */
    public static function fromArray(array $list): Middleware
    {
        return new Middleware($list);
    }
}
