<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests\Psr;

use Kafkiansky\SymfonyMiddleware\Tests\TestCase;
use Nyholm\Psr7\ServerRequest;

final class PsrRequestTransformerTest extends TestCase
{
    public function testRequestTransformer(): void
    {
        $transformer = $this->createPsrRequestTransformer();

        $symfonyRequest = $transformer->fromPsrRequest(
            new ServerRequest(
                'POST',
                '/',
                ['Content-Type' => 'application/json'],
                '{"name": "test"}',
                '1.1',
                ['PHP_AUTH_USER' => 'secret']
            )
        );

        self::assertEquals('POST', $symfonyRequest->getMethod());
        self::assertEquals('/', $symfonyRequest->getPathInfo());
        self::assertEquals(4, $symfonyRequest->headers->count());
        self::assertEquals('application/json', $symfonyRequest->headers->get('content-type'));
        self::assertEquals('{"name": "test"}', $symfonyRequest->getContent());
        self::assertEquals('secret', $symfonyRequest->getUser());
    }
}
