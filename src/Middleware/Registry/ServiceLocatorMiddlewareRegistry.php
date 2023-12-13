<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware\Registry;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareNotConfigured;

/**
 * @psalm-type MiddlewareGroup = array{if?: bool, middlewares?: list<string|class-string<MiddlewareInterface>>}
 */
final class ServiceLocatorMiddlewareRegistry implements MiddlewareRegistry
{
    /** @var array<string, class-string<MiddlewareInterface>[]> */
    private readonly array $enabledMiddlewareGroups;

    /**
     * @psalm-param array<string, MiddlewareGroup> $groups
     *
     * @throws MiddlewareNotConfigured
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $groups,
    ) {
        $this->enabledMiddlewareGroups = self::normalizeGroups($this->groups);
    }

    /**
     * {@inheritdoc}
     */
    public function byName(string $middlewareFqcnOrGroup): array
    {
        if (!$this->container->has($middlewareFqcnOrGroup) && !isset($this->groups[$middlewareFqcnOrGroup])) {
            throw MiddlewareNotConfigured::forMiddleware($middlewareFqcnOrGroup);
        }

        if ($this->container->has($middlewareFqcnOrGroup)) {
            /** @var MiddlewareInterface[] */
            return [$this->container->get($middlewareFqcnOrGroup)];
        }

        return array_map(function (string $middleware): MiddlewareInterface {
            /** @var MiddlewareInterface */
            return $this->container->get($middleware);
        }, $this->enabledMiddlewareGroups[$middlewareFqcnOrGroup] ?? []);
    }

    /**
     * @param array<string, MiddlewareGroup> $groups
     *
     * @throws MiddlewareNotConfigured
     *
     * @return array<string, class-string<MiddlewareInterface>[]>
     */
    private static function normalizeGroups(array $groups): array
    {
        $middlewareGroups = [];

        foreach ($groups as $name => $group) {
            if ($group['if'] ?? true) {
                $middlewareGroups[$name] = self::normalizeMiddlewares($groups, $group['middlewares'] ?? throw MiddlewareNotConfigured::becauseGroupIsEmpty($name));
            }
        }

        return $middlewareGroups;
    }

    /**
     * @param array<string, MiddlewareGroup> $groups
     * @param list<string|class-string<MiddlewareInterface>> $middlewares
     *
     * @throws MiddlewareNotConfigured
     *
     * @return class-string<MiddlewareInterface>[]
     */
    private static function normalizeMiddlewares(array $groups, array $middlewares): array
    {
        $groupMiddlewares = [];

        foreach ($middlewares as $middleware) {
            if (\is_a($middleware, MiddlewareInterface::class, true)) {
                $groupMiddlewares = array_merge($groupMiddlewares, [$middleware]);
            } elseif (isset($groups[$middleware]) && ($groups[$middleware]['if'] ?? true)) {
                $groupMiddlewares = array_merge(
                    $groupMiddlewares,
                    self::normalizeMiddlewares($groups, $groups[$middleware]['middlewares'] ?? throw MiddlewareNotConfigured::becauseGroupIsEmpty($middleware)),
                );
            } elseif(!isset($groups[$middleware])) {
                throw MiddlewareNotConfigured::forMiddleware($middleware);
            }
        }

        return  $groupMiddlewares;
    }
}
