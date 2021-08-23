<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;

final class MiddlewareRunner
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewares;
    private RequestHandlerInterface $requestHandler;
    private PsrResponseTransformer $psrResponseTransformer;

    /**
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(
        array $middlewares,
        RequestHandlerInterface $requestHandler,
        PsrResponseTransformer $psrResponseTransformer,
    ) {
        $this->middlewares = $middlewares;
        $this->requestHandler = $requestHandler;
        $this->psrResponseTransformer = $psrResponseTransformer;
    }

    public function run(ServerRequestInterface $serverRequest): Response
    {
        /** @var \Closure(ServerRequestInterface): ResponseInterface */
        $processor = array_reduce(
            array_reverse($this->middlewares),
            /** @param \Closure(ServerRequestInterface): ResponseInterface $stack */
            function (\Closure $stack, MiddlewareInterface $middleware): \Closure {
                return function (ServerRequestInterface $request) use ($middleware, $stack): ResponseInterface {
                    return $middleware->process($request, new StackMiddleware($stack));
                };
            },
            fn (ServerRequestInterface $request): ResponseInterface => $this->requestHandler->handle($request),
        );

        return $this->psrResponseTransformer->fromPsrResponse($processor($serverRequest));
    }
}
