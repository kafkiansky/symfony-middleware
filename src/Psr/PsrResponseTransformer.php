<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Psr;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

interface PsrResponseTransformer
{
    public function toPsrResponse(SymfonyResponse $symfonyResponse): ResponseInterface;
    public function fromPsrResponse(ResponseInterface $response): SymfonyResponse;
}
