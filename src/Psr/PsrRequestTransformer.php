<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Psr;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

interface PsrRequestTransformer
{
    public function toPsrRequest(SymfonyRequest $symfonyRequest): ServerRequestInterface;
}
