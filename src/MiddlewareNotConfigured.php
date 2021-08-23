<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware;

use Psr\Http\Server\MiddlewareInterface;
use Throwable;

final class MiddlewareNotConfigured extends \Exception
{
    public function __construct(string $middlewareName, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            vsprintf(
                'The middleware or group "%s" was not configured. Make sure it implements the "%s" interface or group is defined.',
                [
                    $middlewareName,
                    MiddlewareInterface::class,
                ]
            ),
            $code,
            $previous
        );
    }
}
