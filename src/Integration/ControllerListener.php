<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Integration;

use Kafkiansky\SymfonyMiddleware\Attribute\Reader\AttributeReader;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareNotConfigured;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

final class ControllerListener
{
    public function __construct(
        private readonly MiddlewareGatherer $middlewareGatherer,
        private readonly AttributeReader $reader,
        private readonly ControllerReplacer $controllerReplacer,
    ) {
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
