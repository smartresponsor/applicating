<?php

declare(strict_types=1);

namespace App\Applicating\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads the Symfony-native service export for the Applicating RC component.
 */
final class ApplicatingExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        unset($configs);

        $configDirectory = __DIR__.'/../../config/component';
        $servicesFile = $configDirectory.'/services.yaml';

        if (!is_file($servicesFile)) {
            return;
        }

        $loader = new YamlFileLoader($container, new FileLocator($configDirectory));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $frameworkConfigFile = __DIR__.'/../../config/packages/applicating_framework.yaml';
        if (!is_file($frameworkConfigFile)) {
            return;
        }

        $config = Yaml::parseFile($frameworkConfigFile);
        if (!is_array($config) || !isset($config['framework']) || !is_array($config['framework'])) {
            return;
        }

        $framework = $config['framework'];
        $framework['csrf_protection']['enabled'] ??= true;
        $framework['form']['enabled'] ??= true;
        $framework['form']['csrf_protection']['enabled'] ??= true;
        $framework['validation']['enabled'] ??= true;

        $container->prependExtensionConfig('framework', $framework);
    }
}
