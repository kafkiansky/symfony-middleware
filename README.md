# PSR-15 middleware now in Symfony

![test](https://github.com/kafkiansky/symfony-middleware/workflows/test/badge.svg?event=push)
[![Codecov](https://codecov.io/gh/kafkiansky/symfony-middleware/branch/master/graph/badge.svg)](https://codecov.io/gh/kafkiansky/symfony-middleware)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/kafkiansky/symfony-middleware.svg?style=flat-square)](https://packagist.org/packages/kafkiansky/symfony-middleware)
[![Quality Score](https://img.shields.io/scrutinizer/g/kafkiansky/symfony-middleware.svg?style=flat-square)](https://scrutinizer-ci.com/g/kafkiansky/symfony-middleware)


### Contents

- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Customization](#customization)
- [Caching](#caching)
- [Testing](#testing)
- [License](#license)

## Installation


```bash
composer require kafkiansky/symfony-middleware
```

## Usage

Each middleware must implement the `Psr\Http\Server\MiddlewareInterface` interface. Thanks for symfony autoconfiguration now the middleware registry knows your middleware.

So that middlewares can start execution, they must be defined on controller class and/or on controller method.

```php
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

#[Middleware([ValidatesQueryParams::class])]
final class SomeController
{
    #[Middleware([ConvertStringsToNull::class])]
    public function index(): void
    {
        
    }
}
```

If controller is invokable, middleware can be defined just on controller class:

```php
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

#[Middleware([ValidatesQueryParams::class, ConvertStringsToNull::class])]
final class SomeController
{
    public function __invoke(): void
    {
    }
}
```

#### groups

If you want to use the list of middlewares, you can define middleware group inside `symfony_middleware.yaml` configuration file:

```yaml
symiddleware:
  groups:
    debug:
      if: '%env(RUN_DEBUG_MIDDLEWARE)%'
      middlewares:
        - 'App\Middleware\TrackRequestTime'
        - 'App\Middleware\EnableSqlLogger'
```

Now define this middleware on controller class or method:

```php
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

#[Middleware(['debug'])]
final class SomeController
{
    public function __invoke(): void
    {
    }
}
```

Pay attention to the `if` parameter in configuration file. This parameter tells the middleware runner when the middleware group can be run.
If false, this middleware will not be executed.

#### global

If you want to run the list of middleware every request, you need the `global` middleware section. This keyword is reserved and `if` parameter is not supported.

```yaml
symiddleware:
  global:
      - App\Controller\SetCorsHeaders
  groups:
    web:
      middlewares:
        - 'App\Middleware\ModifyRequestMiddleware'
```

Now the `App\Controller\SetCorsHeaders` middleware will execute on every request.

## Examples

1. Simple middleware that modifies request:

```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

final class ModifyRequestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(__CLASS__, 'handled'))
    }
}
```

2. Middleware that modifies response:

```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

final class ModifyResponseMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request)

        return $response->withHeader('x-developer', 'kafkiansky');
    }
}
```

3. Middleware that stop execution:

```php

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;

final class StopExecution implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = new Response(200, [], json_encode(['success' => false]));

        return $response;
    }
}
```

In this example controller will not be executed.

4. Stop execution with symfony response:

```php

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;

final class StopExecution implements MiddlewareInterface
{
    private PsrResponseTransformer $psrResponseTransformer;

    public function __construct(PsrResponseTransformer $psrResponseTransformer)
    {
        $this->psrResponseTransformer = $psrResponseTransformer;        
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->psrResponseTransformer->toPsrResponse(new JsonResponse(['success' => false]));
    }
}
```

You can compose middleware group with single middleware, use list of `Middleware` attributes and so on. All the following examples will work:

```php
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

#[Middleware(['debug', 'api', SomeMiddleware::class])]
#[Middleware([SomeAnotherMiddleware::class])]
final class SomeController
{
    public function __invoke(): void
    {
    }
}
```

```php
use Kafkiansky\SymfonyMiddleware\Attribute\Middleware;

#[Middleware(['debug', 'api', SomeMiddleware::class])]
final class SomeController
{
    #[Middleware([SomeAnotherMiddleware::class, 'web'])]
    #[Middleware(['tracking'])]
    public function index(): void
    {
    }
}
```

Duplicated middlewares will be removed.

## Customization

PSR middlewares and Symfony has different incompatible Request objects. If your middleware going to change the request object,
only `attributes`, `query params`, `headers` and `parsed body` will be copied from psr request to symfony request.
If you wish change this behaviour, you may change the `Kafkiansky\SymfonyMiddleware\Psr\PsrRequestCloner` interface binding it to your realization.

## Caching

Package use caching on production environment to prevent reflection usage. First of all, package will search of the `app.cache_middleware` parameter. If package doesn't find it,
it's going to use the `kernel.environment` definition and will cache attributes when it set to `true`.

Package will cache all controllers even if it doesn't found the attributes for it. This approach will allow to remember all the controllers and not use reflection further.

## Testing

``` bash
$ composer test
```  

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
