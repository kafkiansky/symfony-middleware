<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\DependencyInjection;

use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyNullReference
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('symiddleware');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $tree->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('global')
                    ->scalarPrototype()
                        ->isRequired()
                        ->beforeNormalization()
                            ->always($this->createMiddlewareNormalizer())
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('groups')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('if')->end()
                            ->arrayNode('middlewares')
                                ->scalarPrototype()
                                ->isRequired()
                                ->beforeNormalization()
                                    ->always($this->createMiddlewareNormalizer())
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree;
    }

    /**
     * @return \Closure(mixed): string
     */
    private function createMiddlewareNormalizer(): callable
    {
        return function (mixed $middlewareName): string {
            return match (true) {
                \is_string($middlewareName) && \class_exists($middlewareName) && \is_a($middlewareName, MiddlewareInterface::class, true) => $middlewareName,
                \is_string($middlewareName) && !\class_exists($middlewareName) => $middlewareName,
                default => throw new \RuntimeException(
                    vsprintf('Each middleware must implements the "%s" interface, but "%s" doesn\'t.', [
                        MiddlewareInterface::class,
                        \is_string($middlewareName) ? $middlewareName : get_debug_type($middlewareName),
                    ])
                )
            };
        };
    }
}
