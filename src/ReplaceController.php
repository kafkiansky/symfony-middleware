<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Psr\Http\Server\MiddlewareInterface;
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareAction;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareRunner;
use Kafkiansky\SymfonyMiddleware\Middleware\SymfonyActionRequestHandler;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;

final class ReplaceController
{
    private ClassMethodAttributeReader $reader;
    private MiddlewareRegistry $registry;
    private PsrRequestTransformer $psrRequestTransformer;
    private PsrResponseTransformer $psrResponseTransformer;

    public function __construct(
        MiddlewareRegistry $registry,
        ClassMethodAttributeReader $reader,
        PsrRequestTransformer $psrRequestTransformer,
        PsrResponseTransformer $psrResponseTransformer,
    ) {
        $this->registry = $registry;
        $this->reader = $reader;
        $this->psrRequestTransformer = $psrRequestTransformer;
        $this->psrResponseTransformer = $psrResponseTransformer;
    }

    /**
     * @throws MiddlewareNotConfigured
     * @throws \ReflectionException
     */
    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        /** @var object|array{object, string} $controller */
        $controller = $event->getController();
        $method = null; // null if controller is invokable.

        if (\is_array($controller)) {
            list($controller, $method) = $controller;
        }

        $attributes = $this->reader->read($controller, Middleware::class, $method);

        $middlewares = self::fetchMiddlewareNames($attributes);

        if (0 < count($middlewares)) {
            $event->setController(new MiddlewareAction(
                $this->createMiddlewareRunner($this->gatherMiddlewares($middlewares), $event),
                $this->psrRequestTransformer->toPsrRequest($event->getRequest())
            ));
        }
    }

    /**
     * @psalm-param MiddlewareInterface[] $middlewares
     */
    private function createMiddlewareRunner(array $middlewares, ControllerArgumentsEvent $event): MiddlewareRunner
    {
        $destination = $event->getController();
        $arguments = $event->getArguments();

        return new MiddlewareRunner(
            $middlewares,
            new SymfonyActionRequestHandler(
                function (Request $request) use ($destination, $arguments): Response {
                    /** @var Response */
                    return $destination(...$this->withReplacedRequest($request, $arguments));
                },
                $event->getRequest(),
                $this->psrResponseTransformer,
            ),
            $this->psrResponseTransformer,
        );
    }

    /**
     * @param Middleware[] $middlewares
     *
     * @return class-string<MiddlewareInterface>[]|string[]
     */
    private static function fetchMiddlewareNames(array $middlewares): array
    {
        return array_unique(array_merge(...array_map(function (Middleware $middleware): array {
            return $middleware->list;
        }, $middlewares)));
    }

    /**
     * @param class-string<MiddlewareInterface>[]|string[] $middlewaresOrGroups
     *
     * @throws MiddlewareNotConfigured
     *
     * @return MiddlewareInterface[]
     */
    private function gatherMiddlewares(array $middlewaresOrGroups): array
    {
        $middlewares = $this->registry->byName(MiddlewareRegistry::GLOBAL_MIDDLEWARE_GROUP);

        foreach ($middlewaresOrGroups as $middlewareOrGroup) {
            $middlewares = array_merge($middlewares, $this->registry->byName($middlewareOrGroup));
        }

        return array_unique($middlewares, SORT_REGULAR);
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
