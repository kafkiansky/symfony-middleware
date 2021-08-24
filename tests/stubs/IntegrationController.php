<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests\stubs;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[Middleware([ModifyRequestMiddleware::class])]
final class IntegrationController
{
    #[Middleware([ModifyResponseMiddleware::class])]
    public function method1(Request $request, LoggerInterface $logger): JsonResponse
    {
        return new JsonResponse(['attributes' => $request->attributes->all()]);
    }

    #[Middleware([EarlyReturnMiddleware::class])]
    public function method2(Request $request, LoggerInterface $logger): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }

    #[Middleware([EarlyReturnMiddleware::class])]
    #[Middleware([
        AnotherModifyRequestMiddleware::class,
        ModifyResponseMiddleware::class,
        CopyAttributesFromRequest::class,
    ])]
    public function method3(Request $request, LoggerInterface $logger): JsonResponse
    {
        return new JsonResponse(['attributes' => $request->attributes->all()]);
    }
}
