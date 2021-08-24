<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestCloner;
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
    private PsrRequestCloner $psrRequestCloner;

    /**
     * @param callable(SymfonyRequest): Response $destination
     */
    public function __construct(
        callable $destination,
        SymfonyRequest $symfonyRequest,
        PsrResponseTransformer $psrResponseTransformer,
        PsrRequestCloner $psrRequestCloner,
    ) {
        $this->destination = $destination;
        $this->psrResponseTransformer = $psrResponseTransformer;
        $this->symfonyRequest = $symfonyRequest;
        $this->psrRequestCloner = $psrRequestCloner;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $clonedSymfonyRequest = $this->psrRequestCloner->clone($this->symfonyRequest, $request);

        $response = ($this->destination)($clonedSymfonyRequest);

        return $this->psrResponseTransformer->toPsrResponse($response);
    }
}
