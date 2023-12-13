<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final class MiddlewareAction
{
    public function __construct(
        private readonly MiddlewareRunner $middlewareRunner,
        private readonly ServerRequestInterface $serverRequest,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->middlewareRunner->run($this->serverRequest);
    }
}
