<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Psr\Adapter;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Kafkiansky\SymfonyMiddleware\Psr\PsrRequestTransformer;

final class PsrHttpMessageBridgePsrRequestTransformer implements PsrRequestTransformer
{
    private HttpMessageFactoryInterface $httpMessageFactory;
    private HttpFoundationFactoryInterface $httpFoundationFactory;

    public function __construct(
        HttpMessageFactoryInterface $httpMessageFactory,
        HttpFoundationFactoryInterface $httpFoundationFactory,
    ) {
        $this->httpMessageFactory = $httpMessageFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    public function toPsrRequest(SymfonyRequest $symfonyRequest): ServerRequestInterface
    {
        return $this->httpMessageFactory->createRequest($symfonyRequest);
    }

    public function fromPsrRequest(ServerRequestInterface $request): SymfonyRequest
    {
        return $this->httpFoundationFactory->createRequest($request);
    }
}
