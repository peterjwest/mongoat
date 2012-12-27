<?php

namespace WhiteOctober\MongoatBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Builds configuration
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mongoat');

        $rootNode
            ->children()
                ->scalarNode('model_namepsace')->end()
                ->arrayNode('connections')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->defaultValue('WhiteOctober\MongoatBundle\Core\Connection')->end()
                            ->scalarNode('server')->end()
                            ->scalarNode('database')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
