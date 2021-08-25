<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set(Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader::class);

    $services
        ->set(
            Kafkiansky\SymfonyMiddleware\Attribute\Reader\AttributeReader::class,
            Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader::class,
        );

    $services
        ->set(
            Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface::class,
            Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory::class
        );

    $services->set(Nyholm\Psr7\Factory\Psr17Factory::class, Nyholm\Psr7\Factory\Psr17Factory::class);

    $services
        ->set(
            Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface::class,
            Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory::class,
        )
        ->args([
            '$serverRequestFactory' => service(Nyholm\Psr7\Factory\Psr17Factory::class),
            '$streamFactory' => service(Nyholm\Psr7\Factory\Psr17Factory::class),
            '$uploadedFileFactory' => service(Nyholm\Psr7\Factory\Psr17Factory::class),
            '$responseFactory' => service(Nyholm\Psr7\Factory\Psr17Factory::class),
        ]);

    $services
        ->set(
            Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer::class,
            Kafkiansky\SymfonyMiddleware\Psr\Adapter\PsrHttpMessageBridgePsrRequestTransformer::class
        )
        ->args([
            service(Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface::class),
            service(Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface::class),
        ]);

    $services
        ->set(
            Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer::class,
            Kafkiansky\SymfonyMiddleware\Psr\Adapter\PsrHttpMessageBridgePsrResponseTransformer::class
        )
        ->args([
            service(Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface::class),
            service(Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface::class),
        ]);

    $services
        ->set(
            Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer::class,
            Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer::class
        )
        ->args([
            service(Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry::class),
        ])
    ;

    $services->set(
        Kafkiansky\SymfonyMiddleware\Psr\PsrRequestCloner::class,
        Kafkiansky\SymfonyMiddleware\Psr\DefaultPsrRequestCloner::class,
    );

    $services
        ->set(
            Kafkiansky\SymfonyMiddleware\Integration\ControllerReplacer::class,
            Kafkiansky\SymfonyMiddleware\Integration\ControllerReplacer::class,
        )
        ->args([
            service(Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer::class),
            service(Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer::class),
            service(Kafkiansky\SymfonyMiddleware\Psr\PsrRequestCloner::class),
        ]);

    $services->set(Kafkiansky\SymfonyMiddleware\Integration\ControllerListener::class)
        ->tag('kernel.event_listener', [
            'event' => Symfony\Component\HttpKernel\KernelEvents::CONTROLLER_ARGUMENTS,
            'method' => 'onControllerArguments',
        ])
        ->args([
            service(Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareGatherer::class),
            service(Kafkiansky\SymfonyMiddleware\Attribute\Reader\AttributeReader::class),
            service(Kafkiansky\SymfonyMiddleware\Integration\ControllerReplacer::class),
        ])
    ;
};
