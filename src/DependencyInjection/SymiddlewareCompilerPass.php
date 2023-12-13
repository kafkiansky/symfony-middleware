<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Psr\Http\Server\MiddlewareInterface;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

final class SymiddlewareCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(MiddlewareRegistry::class) === false) {
            return;
        }

        $definition = $container->findDefinition(MiddlewareRegistry::class);

        $found = [];

        /** @var class-string<MiddlewareInterface> $middleware */
        foreach ($container->findTaggedServiceIds('kafkiansky.symfony.middleware') as $middleware => $_tags) {
            $found[$middleware] = service($middleware);
        }

        $definition->replaceArgument(0, service_locator($found));
    }
}
