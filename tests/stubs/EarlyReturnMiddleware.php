<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests\stubs;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EarlyReturnMiddleware implements MiddlewareInterface
{
    private bool $stopPropagation;

    public function __construct(bool $stopPropagation = true)
    {
        $this->stopPropagation = $stopPropagation;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->stopPropagation) {
            return new Response(200, [], '{"early_exit": true}');
        }

        return $handler->handle($request->withAttribute(__CLASS__, 'handled'));
    }
}
