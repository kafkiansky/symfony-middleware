<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Psr\Adapter;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

final class PsrHttpMessageBridgePsrResponseTransformer implements PsrResponseTransformer
{
    private HttpMessageFactoryInterface $httpMessageFactory;
    private HttpFoundationFactoryInterface $httpFoundationFactory;

    public function __construct(
        HttpMessageFactoryInterface $httpMessageFactory,
        HttpFoundationFactoryInterface $httpFoundationFactory
    ) {
        $this->httpMessageFactory = $httpMessageFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    public function toPsrResponse(SymfonyResponse $symfonyResponse): ResponseInterface
    {
        return $this->httpMessageFactory->createResponse($symfonyResponse);
    }

    public function fromPsrResponse(ResponseInterface $response): SymfonyResponse
    {
        return $this->httpFoundationFactory->createResponse($response);
    }
}
