<?php

declare(strict_types=1);

namespace Kafkiansky\SymfonyMiddleware\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Psr\Http\Server\MiddlewareInterface;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\MiddlewareRegistry;
use Kafkiansky\SymfonyMiddleware\Middleware\Registry\ServiceLocatorMiddlewareRegistry;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

final class SymiddlewareExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(MiddlewareInterface::class)
            ->addTag('kafkiansky.symfony.middleware');

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('symiddleware.php');

        $configuration = $this->getConfiguration($configs, $container);

        if ($configuration === null) {
            return;
        }

        /** @var array{groups: array<string, array{if?: string}>, global?: string[]} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $config['groups']['global'] = [
            'if' => true, // global middleware should run always.
            'middlewares' => $config['global'] ?? [],
        ];

        unset($config['global']);

        // We will replace empty service locator in compiler pass when all middlewares will be found.
        $container->setDefinition(
            MiddlewareRegistry::class,
            new Definition(ServiceLocatorMiddlewareRegistry::class, [service_locator([]), $config['groups']])
        );
    }

    public function getAlias(): string
    {
        return 'symiddleware';
    }
}
