<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute;

use Psr\Http\Server\MiddlewareInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
/**
 * @psalm-immutable
 */
final class Middleware
{
    /**
     * @psalm-readonly
     *
     * @var class-string<MiddlewareInterface>[]|string[]
     */
    public array $list;

    /**
     * @param class-string<MiddlewareInterface>[]|string[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
