<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StackMiddleware implements RequestHandlerInterface
{
    /**
     * @var \Closure(ServerRequestInterface): ResponseInterface
     */
    private \Closure $stack;

    /**
     * @param \Closure(ServerRequestInterface): ResponseInterface $stack
     */
    public function __construct(\Closure $stack)
    {
        $this->stack = $stack;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->stack)($request);
    }
}
