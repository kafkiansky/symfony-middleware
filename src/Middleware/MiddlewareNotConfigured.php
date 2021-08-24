<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\Middleware;

use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareNotConfigured extends \Exception
{
    public static function forMiddleware(string $middlewareNameOrGroup): MiddlewareNotConfigured
    {
        return new MiddlewareNotConfigured(
            vsprintf(
                'The middleware or group "%s" was not configured. Make sure it implements the "%s" interface or group is defined.',
                [
                    $middlewareNameOrGroup,
                    MiddlewareInterface::class,
                ]
            )
        );
    }

    public static function becauseGroupIsEmpty(string $groupName): MiddlewareNotConfigured
    {
        return new MiddlewareNotConfigured(
            sprintf('Middlewares groups cannot empty, but the group "%s" is.', $groupName)
        );
    }
}
