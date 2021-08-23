<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests\stubs;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CopyAttributesFromRequest implements MiddlewareInterface
{
    public static array $attributes = [];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        self::$attributes = $request->getAttributes();

        return $response;
    }
}
