<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Psr\DefaultPsrRequestCloner;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class DefaultPsrRequestClonerTest extends TestCase
{
    public function testCloneAttributes(): void
    {
        $cloner = new DefaultPsrRequestCloner();

        $symfonyRequest = SymfonyRequest::create('/', 'POST');
        $symfonyRequest->attributes->set('_route', 'index');

        $psrRequest = $this->createPsrRequestTransformer()->toPsrRequest($symfonyRequest);

        $psrRequest = $psrRequest->withAttribute('test', 1);

        $symfonyRequest = $cloner->clone($symfonyRequest, $psrRequest);

        self::assertEquals(2, $symfonyRequest->attributes->count());
        self::assertEquals(
            [
                '_route' => 'index',
                'test' => 1,
            ],
            $symfonyRequest->attributes->all()
        );
    }

    public function testCloneHeaders(): void
    {
        $cloner = new DefaultPsrRequestCloner();

        $symfonyRequest = SymfonyRequest::create('/', 'POST');
        $symfonyRequest->headers->set('Accept', 'application/xml');

        $psrRequest = $this->createPsrRequestTransformer()->toPsrRequest($symfonyRequest);

        $psrRequest = $psrRequest->withAddedHeader('x-start-time', $time = time());

        $symfonyRequest = $cloner->clone($symfonyRequest, $psrRequest);

        self::assertEquals('application/xml', $symfonyRequest->headers->get('accept'));
        self::assertEquals($time, $symfonyRequest->headers->get('x-start-time'));
    }

    public function testCloneQueryParams(): void
    {
        $cloner = new DefaultPsrRequestCloner();

        $symfonyRequest = SymfonyRequest::create('/', 'GET', ['page' => 1]);

        $psrRequest = $this->createPsrRequestTransformer()->toPsrRequest($symfonyRequest);
        self::assertEquals(['page' => 1], $psrRequest->getQueryParams());

        $psrRequest = $psrRequest->withQueryParams(['sort' => 'desc', 'page' => 1]);

        $symfonyRequest = $cloner->clone($symfonyRequest, $psrRequest);

        self::assertEquals(2, $symfonyRequest->query->count());
        self::assertEquals(
            [
                'sort' => 'desc',
                'page' => 1,
            ],
            $symfonyRequest->query->all()
        );
    }

    public function testCloneRequestParams(): void
    {
        $cloner = new DefaultPsrRequestCloner();

        $symfonyRequest = SymfonyRequest::create('/', 'POST');
        $symfonyRequest->request->set('name', 'kafkiansky');
        $symfonyRequest->request->set('email', 'test@gmail.com');

        $psrRequest = $this->createPsrRequestTransformer()->toPsrRequest($symfonyRequest);

        $psrRequest = $psrRequest->withParsedBody(array_merge($symfonyRequest->request->all(), ['role' => 'test']));

        $symfonyRequest = $cloner->clone($symfonyRequest, $psrRequest);

        self::assertEquals(3, $symfonyRequest->request->count());
        self::assertEquals(
            [
                'name' => 'kafkiansky',
                'email' => 'test@gmail.com',
                'role' => 'test',
            ],
            $symfonyRequest->request->all()
        );
    }
}
