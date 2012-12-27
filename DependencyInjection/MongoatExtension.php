<?php

namespace WhiteOctober\MongoatBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class MongoatExtension extends Extension
{
    /**
     * Loads services and connections
     *
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['connections'] as $name => $connection) {
            $definitionName = 'mongoat.connections.'.$name;

            // Defines a service for the connection
            $container->setDefinition(
                $definitionName,
                new Definition($connection['class'],
                array(
                    $connection['server'],
                    $connection['database']
                )
            ));

            // Adds connection to Mongoat instance
            $container->getDefinition('mongoat')->addMethodCall('addConnection', array(
                $name,
                new Reference($definitionName),
            ));
        }

        $container->getDefinition('mongoat')->addMethodCall('modelNamespace', array($config['model_namepsace']));
    }

    public function getAlias()
    {
        return 'mongoat';
    }
}
