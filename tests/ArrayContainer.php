<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

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

        throw new class implements NotFoundExceptionInterface {
            public function getMessage()
            {
            }

            public function getCode()
            {
            }

            public function getFile()
            {
            }

            public function getLine()
            {
            }

            public function getTrace()
            {
            }

            public function getTraceAsString()
            {
            }

            public function getPrevious()
            {
            }

            public function __toString(): string
            {
                return '';
            }
        };
    }

    public function has(string $id): bool
    {
        return isset($this->middlewares[$id]);
    }
}
