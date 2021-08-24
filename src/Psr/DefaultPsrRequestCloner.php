<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Psr;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class DefaultPsrRequestCloner implements PsrRequestCloner
{
    public function clone(SymfonyRequest $symfonyRequest, PsrRequest $psrRequest): SymfonyRequest
    {
        $symfonyRequest->attributes->replace($psrRequest->getAttributes());
        $symfonyRequest->headers->replace($psrRequest->getHeaders());
        $symfonyRequest->query->replace($psrRequest->getQueryParams());

        if (\is_array($parsedBody = $psrRequest->getParsedBody())) {
            $symfonyRequest->request->replace($parsedBody);
        }

        return $symfonyRequest;
    }
}
