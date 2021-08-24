<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware\Registry;

use Psr\Http\Server\MiddlewareInterface;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareNotConfigured;

interface MiddlewareRegistry
{
    /**
     * Array-key where global middleware may be defined.
     */
    public const GLOBAL_MIDDLEWARE_GROUP = 'global';

    /**
     * @param class-string<MiddlewareInterface>|string $middlewareFqcnOrGroup
     *
     * @throws MiddlewareNotConfigured
     *
     * @return MiddlewareInterface[]
     */
    public function byName(string $middlewareFqcnOrGroup): array;
}
