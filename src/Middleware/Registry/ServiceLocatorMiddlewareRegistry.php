<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware\Registry;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareNotConfigured;

final class ServiceLocatorMiddlewareRegistry implements MiddlewareRegistry
{
    /**
     * @psalm-param array<string, array{if?: bool, middlewares: class-string<MiddlewareInterface>[]}> $groups
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $groups,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function byName(string $middlewareFqcnOrGroup): array
    {
        if ($this->container->has($middlewareFqcnOrGroup) === false && !isset($this->groups[$middlewareFqcnOrGroup])) {
            throw MiddlewareNotConfigured::forMiddleware($middlewareFqcnOrGroup);
        }

        if ($this->container->has($middlewareFqcnOrGroup)) {
            /** @var MiddlewareInterface[] */
            return [$this->container->get($middlewareFqcnOrGroup)];
        }

        /** @var MiddlewareInterface[] $middlewares */
        $middlewares = [];

        if (!isset($this->groups[$middlewareFqcnOrGroup]['if']) || $this->groups[$middlewareFqcnOrGroup]['if']) {
            $middlewares = array_map(function (string $middlewareFqcn): MiddlewareInterface {
                /** @var MiddlewareInterface */
                return $this->container->get($middlewareFqcn);
            }, $this->groups[$middlewareFqcnOrGroup]['middlewares']
                ?? throw MiddlewareNotConfigured::becauseGroupIsEmpty($middlewareFqcnOrGroup)
            );
        }

        return $middlewares;
    }
}
