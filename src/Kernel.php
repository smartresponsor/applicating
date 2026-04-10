<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @throws \Throwable
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        unset($container);

        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/packages/*.yaml', 'glob');
        $loader->load($confDir.'/packages/'.$this->environment.'/*.yaml', 'glob');
        $loader->load($confDir.'/services/*.yaml', 'glob');
    }

    /**
     * @throws \Throwable
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/routes/*.yaml');
        $routes->import('../src/Controller/', 'attribute');
    }
}
