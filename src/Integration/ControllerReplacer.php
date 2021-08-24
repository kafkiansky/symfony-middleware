<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Integration;

use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareAction;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareRunner;
use Kafkiansky\SymfonyMiddleware\Middleware\SymfonyActionRequestHandler;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestCloner;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ControllerReplacer
{
    private PsrRequestTransformer $psrRequestTransformer;
    private PsrResponseTransformer $psrResponseTransformer;
    private PsrRequestCloner $psrRequestCloner;

    public function __construct(
        PsrRequestTransformer $psrRequestTransformer,
        PsrResponseTransformer $psrResponseTransformer,
        PsrRequestCloner $psrRequestCloner,
    ) {
        $this->psrRequestTransformer = $psrRequestTransformer;
        $this->psrResponseTransformer = $psrResponseTransformer;
        $this->psrRequestCloner = $psrRequestCloner;
    }

    /**
     * @param callable(): Response $originalController
     * @param array $arguments
     * @param MiddlewareInterface[] $middlewares
     * @param Request $symfonyRequest
     *
     * @return MiddlewareAction
     */
    public function createController(
        callable $originalController,
        array $arguments,
        array $middlewares,
        Request $symfonyRequest
    ): MiddlewareAction {
        return new MiddlewareAction(
            $this->createMiddlewareRunner($middlewares, $originalController, $arguments, $symfonyRequest),
            $this->psrRequestTransformer->toPsrRequest($symfonyRequest)
        );
    }

    /**
     * @param MiddlewareInterface[] $middlewares
     */
    private function createMiddlewareRunner(
        array $middlewares,
        callable $destination,
        array $arguments,
        Request $symfonyRequest
    ): MiddlewareRunner {
        return new MiddlewareRunner(
            $middlewares,
            new SymfonyActionRequestHandler(
                function (Request $request) use ($destination, $arguments): Response {
                    /** @var Response */
                    return $destination(...$this->withReplacedRequest($request, $arguments));
                },
                $symfonyRequest,
                $this->psrResponseTransformer,
                $this->psrRequestCloner,
            ),
            $this->psrResponseTransformer,
        );
    }

    private function withReplacedRequest(Request $request, array $arguments): array
    {
        $updatedArguments = [];

        /** @var object|scalar $argument */
        foreach ($arguments as $argument) {
            if ($argument instanceof Request) {
                $argument = $request;
            }

            $updatedArguments[] = $argument;
        }

        return $updatedArguments;
    }
}
