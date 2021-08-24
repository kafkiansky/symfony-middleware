<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware;

use Kafkiansky\SymfonyMiddleware\DependencyInjection\AttributeReaderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Kafkiansky\SymfonyMiddleware\DependencyInjection\SymiddlewareCompilerPass;
use Kafkiansky\SymfonyMiddleware\DependencyInjection\SymiddlewareExtension;

final class SymiddlewareBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SymiddlewareCompilerPass());
        $container->addCompilerPass(new AttributeReaderCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new SymiddlewareExtension();
    }
}
