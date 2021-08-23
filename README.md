# Middleware support now in Symfony.

### Contents

- [Installation](#installation)
- [Usage](#usage)
    - [Examples](#examples)
        - [Middleware group](#middleware-group)
        - [Middleware queue](#middleware-queue)
        - [Middleware arguments](#middleware-arguments)
        - [Middleware condition](#middleware-condition)
        - [Conditions and arguments](#conditions-and-arguments)
        - [Global middleware group](#global-middleware-group)
        - [Queue and group](#queue-and-group)
- [Testing](#testing)
- [License](#license)

## Installation

```bash
composer require kafkiansky/symfony-middleware
```

## Usage

Imagine that you have same configuration:

```yaml
middlewares:
  groups:
    web:
      middlewares:
        - 'App\Middleware\PerformAccess'
        - 'App\Middleware\ValidateRequest'
    debug:
      if: '%env(RUN_DEBUG_MIDDLEWARE)%'
      middlewares:
        - 'App\Middleware\TrackRequestTime'
        - 'App\Middleware\EnableSqlLogger'
    api:
      middlewares:
        - 'App\Middleware\ApplicationAccess'
```

### Examples

Coming soon...

## Testing

``` bash
$ composer test
```  

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
