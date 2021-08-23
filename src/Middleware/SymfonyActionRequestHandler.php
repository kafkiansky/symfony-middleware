<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;

final class SymfonyActionRequestHandler implements RequestHandlerInterface
{
    /**
     * @var callable(SymfonyRequest): Response
     */
    private $destination;
    private PsrResponseTransformer $psrResponseTransformer;
    private SymfonyRequest $symfonyRequest;

    /**
     * @param callable(SymfonyRequest): Response $destination
     */
    public function __construct(
        callable $destination,
        SymfonyRequest $symfonyRequest,
        PsrResponseTransformer $psrResponseTransformer,
    ) {
        $this->destination = $destination;
        $this->psrResponseTransformer = $psrResponseTransformer;
        $this->symfonyRequest = $symfonyRequest;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = ($this->destination)($this->migrate($this->symfonyRequest, $request));

        return $this->psrResponseTransformer->toPsrResponse($response);
    }

    private function migrate(SymfonyRequest $symfonyRequest, ServerRequestInterface $psrRequest): SymfonyRequest
    {
        $symfonyRequest->attributes->replace($psrRequest->getAttributes());
        $symfonyRequest->headers->replace($psrRequest->getHeaders());

        return $symfonyRequest;
    }
}
