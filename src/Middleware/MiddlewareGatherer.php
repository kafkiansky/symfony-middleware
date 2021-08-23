<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\MiddlewareNotConfigured;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareGatherer
{
    private MiddlewareRegistry $middlewareRegistry;

    public function __construct(MiddlewareRegistry $middlewareRegistry)
    {
        $this->middlewareRegistry = $middlewareRegistry;
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
        $middlewaresOrGroups = array_unique(array_merge(...array_map(function (Middleware $middleware): array {
            return $middleware->list;
        }, $attributes)));

        $middlewares = $this->middlewareRegistry->byName(MiddlewareRegistry::GLOBAL_MIDDLEWARE_GROUP);

        foreach ($middlewaresOrGroups as $middlewareOrGroup) {
            $middlewares = array_merge($middlewares, $this->middlewareRegistry->byName($middlewareOrGroup));
        }

        return array_values(array_unique($middlewares, SORT_REGULAR));
    }
}
