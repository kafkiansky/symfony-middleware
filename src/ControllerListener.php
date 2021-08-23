<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware;

use Kafkiansky\SymfonyMiddleware\Attribute\Reader\AttributeReader;
use Kafkiansky\SymfonyMiddleware\Integration\ControllerReplacer;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

final class ControllerListener
{
    private MiddlewareGatherer $middlewareGatherer;
    private AttributeReader $reader;
    private ControllerReplacer $controllerReplacer;

    public function __construct(
        MiddlewareGatherer $middlewareGatherer,
        AttributeReader $reader,
        ControllerReplacer $controllerReplacer
    ) {
        $this->middlewareGatherer = $middlewareGatherer;
        $this->reader = $reader;
        $this->controllerReplacer = $controllerReplacer;
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

        $attributes = $this->reader->read($controller, $method);

        $middlewares = $this->middlewareGatherer->gather($attributes);

        if (count($middlewares) > 0) {
            /** @psalm-var callable(): Response $originalController */
            $originalController = $event->getController();

            $event->setController($this->controllerReplacer->createController(
                $originalController,
                $event->getArguments(),
                $middlewares,
                $event->getRequest()
            ));
        }
    }
}
