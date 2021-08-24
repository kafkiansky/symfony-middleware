<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\DependencyInjection;

use Kafkiansky\SymfonyMiddleware\Attribute\Reader\AttributeReader;
use Kafkiansky\SymfonyMiddleware\Attribute\Reader\CacheAttributesReader;
use Kafkiansky\SymfonyMiddleware\Attribute\Reader\ClassMethodAttributeReader;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class AttributeReaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(AttributeReader::class) && $this->shouldBeCached($container)) {
            $definition = $container->findDefinition(AttributeReader::class);

            $container->setDefinition(AttributeReader::class, new Definition(CacheAttributesReader::class, [
                new Reference(CacheItemPoolInterface::class),
                new Reference($definition->getClass() ?: ClassMethodAttributeReader::class),
            ]));
        }
    }

    private function shouldBeCached(ContainerBuilder $container): bool
    {
        if ($container->hasParameter('app.cache_middleware')) {
            return (bool) $container->getParameter('app.cache_middleware');
        }

        return $container->getParameter('kernel.environment') === 'prod';
    }
}
