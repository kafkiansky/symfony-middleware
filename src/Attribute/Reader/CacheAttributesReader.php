<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Attribute\Reader;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Server\MiddlewareInterface;

final class CacheAttributesReader implements AttributeReader
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly AttributeReader $delegate,
    ) {
    }

    /**
     * @param object $class
     * @param string|null $method
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     *
     * @return Middleware[]
     */
    public function read(object $class, ?string $method = null): array
    {
        $cacheKey = self::cacheKey($class, $method);

        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            /** @var array $cachedMiddlewares */
            $cachedMiddlewares = $item->get();

            $middlewares = $this->normalizeFromCache($cachedMiddlewares);
        } else {
            $middlewares = $this->delegate->read($class, $method);

            $item->set($this->normalizeToCache($middlewares));

            $this->cache->save($item);
        }

        return $middlewares;
    }

    /**
     * @param array $cachedMiddlewares
     *
     * @return Middleware[]
     */
    private function normalizeFromCache(array $cachedMiddlewares): array
    {
        $middlewares = [];

        /** @var class-string<MiddlewareInterface>[]|string[] $item */
        foreach ($cachedMiddlewares as $item) {
            $middlewares[] = Middleware::fromArray($item);
        }

        return $middlewares;
    }

    /**
     * @param Middleware[] $middlewares
     *
     * @return array
     */
    private function normalizeToCache(array $middlewares): array
    {
        $normalized = [];

        foreach ($middlewares as $middleware) {
            $normalized[] = $middleware->toArray();
        }

        return $normalized;
    }

    private static function cacheKey(object $class, ?string $method = null): string
    {
        $key = 'symfony.middleware.'.str_replace('\\', '', $class::class);

        if ($method !== null) {
            $key .= '.'.$method;
        }

        return $key;
    }
}
