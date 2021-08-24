<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Tests;

use Kafkiansky\SymfonyMiddleware\Integration\ControllerListener;
use Kafkiansky\SymfonyMiddleware\Integration\ControllerReplacer;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\AnotherModifyRequestMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\CopyAttributesFromRequest;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\EarlyReturnMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\EmptyIntegrationController;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\IntegrationController;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyRequestMiddleware;
use Kafkiansky\SymfonyMiddleware\Tests\stubs\ModifyResponseMiddleware;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ControllerIntegrationTest extends TestCase
{
    public function testFullIntegrationWithSimpleMiddlewares(): void
    {
        $request = Request::create('/', 'POST');

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new IntegrationController(), 'method1'],
            [$request, new NullLogger()],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $controllerListener = new ControllerListener(
            $this->createMiddlewareGatherer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
                EarlyReturnMiddleware::class => new EarlyReturnMiddleware(),
            ]),
            $this->createAttributeReader(),
            new ControllerReplacer(
                $this->createPsrRequestTransformer(),
                $this->createPsrResponseTransformer(),
                $this->createPsrRequestCloner()
            ),
        );

        $controllerListener->onControllerArguments($event);

        $controller = $event->getController();

        /** @var Response $response */
        $response = $controller();

        self::assertEquals(
            ['attributes' => [ModifyRequestMiddleware::class => 'handled']],
            json_decode($response->getContent(), true)
        );
        self::assertEquals('kafkiansky', $response->headers->get('x-developer'));
    }

    public function testFullIntegrationWithEarlyReturnMiddleware(): void
    {
        $request = Request::create('/', 'POST');

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new IntegrationController(), 'method2'],
            [$request, new NullLogger()],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $controllerListener = new ControllerListener(
            $this->createMiddlewareGatherer([
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
                EarlyReturnMiddleware::class => new EarlyReturnMiddleware(),
            ]),
            $this->createAttributeReader(),
            new ControllerReplacer(
                $this->createPsrRequestTransformer(),
                $this->createPsrResponseTransformer(),
                $this->createPsrRequestCloner()
            ),
        );

        $controllerListener->onControllerArguments($event);

        $controller = $event->getController();

        /** @var Response $response */
        $response = $controller();

        self::assertEquals(['early_exit' => true], json_decode($response->getContent(), true));
    }

    public function testFullIntegrationWithListOfMiddleware(): void
    {
        $request = Request::create('/', 'POST');

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new IntegrationController(), 'method3'],
            [$request, new NullLogger()],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $controllerListener = new ControllerListener(
            $this->createMiddlewareGatherer([
                CopyAttributesFromRequest::class => new CopyAttributesFromRequest(),
                AnotherModifyRequestMiddleware::class => new AnotherModifyRequestMiddleware(),
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
                ModifyResponseMiddleware::class => new ModifyResponseMiddleware(),
                EarlyReturnMiddleware::class => new EarlyReturnMiddleware(false),
            ]),
            $this->createAttributeReader(),
            new ControllerReplacer(
                $this->createPsrRequestTransformer(),
                $this->createPsrResponseTransformer(),
                $this->createPsrRequestCloner()
            ),
        );

        $controllerListener->onControllerArguments($event);

        $controller = $event->getController();

        /** @var Response $response */
        $response = $controller();

        self::assertCount(3, CopyAttributesFromRequest::$attributes);
        self::assertArrayHasKey(ModifyRequestMiddleware::class, CopyAttributesFromRequest::$attributes);
        self::assertArrayHasKey(EarlyReturnMiddleware::class, CopyAttributesFromRequest::$attributes);
        self::assertArrayHasKey(AnotherModifyRequestMiddleware::class, CopyAttributesFromRequest::$attributes);

        self::assertEquals(
            [
                ModifyRequestMiddleware::class => 'handled',
                AnotherModifyRequestMiddleware::class => 'handled',
                EarlyReturnMiddleware::class => 'handled',
            ],
            json_decode($response->getContent(), true)['attributes']
        );
    }

    public function testThatJustGlobalMiddlewareCanExecuted(): void
    {
        $request = Request::create('/', 'POST');

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new EmptyIntegrationController(), 'method1'],
            [$request],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $controllerListener = new ControllerListener(
            $this->createMiddlewareGatherer([
                CopyAttributesFromRequest::class => new CopyAttributesFromRequest(),
                ModifyRequestMiddleware::class => new ModifyRequestMiddleware(),
            ], [
                'global' => ['middlewares' => [ModifyRequestMiddleware::class]],
            ]),
            $this->createAttributeReader(),
            new ControllerReplacer(
                $this->createPsrRequestTransformer(),
                $this->createPsrResponseTransformer(),
                $this->createPsrRequestCloner()
            ),
        );

        $controllerListener->onControllerArguments($event);

        $controller = $event->getController();

        /** @var Response $response */
        $response = $controller();

        self::assertEquals([ModifyRequestMiddleware::class => 'handled'], json_decode($response->getContent(), true)['attributes']);
    }

    public function testControllerWasNotChangeBecauseNoMiddlewareWasFound(): void
    {
        $request = Request::create('/', 'POST');

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new EmptyIntegrationController(), 'method1'],
            [$request],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $controllerListener = new ControllerListener(
            $this->createMiddlewareGatherer(),
            $this->createAttributeReader(),
            new ControllerReplacer(
                $this->createPsrRequestTransformer(),
                $this->createPsrResponseTransformer(),
                $this->createPsrRequestCloner()
            ),
        );

        $controllerListener->onControllerArguments($event);

        $controller = $event->getController();

        /** @var Response $response */
        $response = $controller($request); // proof that controller was not changed.

        self::assertEmpty(json_decode($response->getContent(), true)['attributes']);
    }
}
