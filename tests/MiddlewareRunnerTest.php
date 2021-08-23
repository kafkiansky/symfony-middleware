<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Middleware\MiddlewareRunner;
use Kafkiansky\SymfonyMiddleware\Middleware\SymfonyActionRequestHandler;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\CopyAttributesFromRequest;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\EarlyReturnMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyRequestMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyResponseMiddleware;
use Nyholm\Psr7\ServerRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class MiddlewareRunnerTest extends TestCase
{
    public function testMiddleware(): void
    {
        $symfonyRequest = new Request();

        $runner = new MiddlewareRunner(
            [
                new ModifyRequestMiddleware(),
                new ModifyResponseMiddleware(),
                new CopyAttributesFromRequest(),
            ],
            new SymfonyActionRequestHandler(
                function (): JsonResponse {
                    return new JsonResponse(['success' => true]);
                },
                $symfonyRequest,
                $this->createPsrResponseTransformer()
            ),
            $this->createPsrResponseTransformer(),
        );

        $response = $runner->run(new ServerRequest('POST', '/'));
        self::assertCount(1, CopyAttributesFromRequest::$attributes);
        self::assertEquals([ModifyRequestMiddleware::class => 'handled'], CopyAttributesFromRequest::$attributes);
        self::assertArrayHasKey('x-developer', array_flip($response->headers->keys()));
        self::assertEquals(['kafkiansky'], $response->headers->allPreserveCase()['x-developer']);
    }

    public function testMiddlewareCanBreakTheExecutionChain(): void
    {
        $symfonyRequest = new Request();

        $runner = new MiddlewareRunner(
            [
                new EarlyReturnMiddleware(),
                new ModifyRequestMiddleware(),
                new ModifyResponseMiddleware(),
                new CopyAttributesFromRequest(),
            ],
            new SymfonyActionRequestHandler(
                function (): JsonResponse {
                    return new JsonResponse(['success' => true]);
                },
                $symfonyRequest,
                $this->createPsrResponseTransformer()
            ),
            $this->createPsrResponseTransformer(),
        );

        $response = $runner->run(new ServerRequest('POST', '/'));

        self::assertEquals(['early_exit' => true], json_decode($response->getContent(), true));
        self::assertCount(0, CopyAttributesFromRequest::$attributes);
        self::assertArrayNotHasKey('x-developer', array_flip($response->headers->keys()));
    }
}
