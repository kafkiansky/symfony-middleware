<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ArrayContainer implements ContainerInterface
{
    private array $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->middlewares[$id];
        }

        throw new class implements NotFoundExceptionInterface {};
    }

    public function has(string $id)
    {
        return isset($this->middlewares[$id]);
    }
}
