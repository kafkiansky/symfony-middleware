<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Psr;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

interface PsrRequestCloner
{
    public function clone(SymfonyRequest $symfonyRequest, PsrRequest $psrRequest): SymfonyRequest;
}
