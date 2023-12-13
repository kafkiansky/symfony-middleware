<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareGatherer
{
    /**
     * Array-key where global middleware may be defined.
     */
    private const GLOBAL_MIDDLEWARE_GROUP = 'global';

    public function __construct(
        private readonly MiddlewareRegistry $middlewareRegistry,
    ) {
    }

    /**
     * @param Middleware[] $attributes
     *
     * @throws MiddlewareNotConfigured
     *
     * @return MiddlewareInterface[]
     */
    public function gather(array $attributes): array
    {
        $middlewaresOrGroups = array_unique(
            array_merge([], ...array_map(fn (Middleware $middleware): array => $middleware->list, $attributes)),
        );

        $middlewares = $this->middlewareRegistry->byName(self::GLOBAL_MIDDLEWARE_GROUP);

        foreach ($middlewaresOrGroups as $middlewareOrGroup) {
            $middlewares = array_merge($middlewares, $this->middlewareRegistry->byName($middlewareOrGroup));
        }

        return array_values(array_unique($middlewares, SORT_REGULAR));
    }
}
