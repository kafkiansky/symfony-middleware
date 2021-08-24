<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests\stubs;

use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

#[Middleware(['test'])]
final class ControllerMethod
{
    #[Middleware(['api'])]
    public function index(): void
    {
    }
}
