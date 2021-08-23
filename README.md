# PSR-15 middleware now in Symfony.

### Contents

- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
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
middlewares:
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
middlewares:
  global:
      - App\Controller\SetCorsHeaders
  groups:
    web:
      middlewares:
        - 'App\Middleware\ModifyRequestMiddleware'
```

Now the `App\Controller\SetCorsHeaders` middleware will execute on every request.

### Examples

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

## Testing

``` bash
$ composer test
```  

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
