<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests\stubs;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class EmptyIntegrationController
{
    public function method1(Request $request): JsonResponse
    {
        return new JsonResponse(['attributes' => $request->attributes->all()]);
    }
}
