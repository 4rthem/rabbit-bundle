<?php

namespace Arthem\Bundle\RabbitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('arthem_rabbit');

        $rootNode
            ->children()
                ->booleanNode('direct')->defaultValue($this->debug)->end()
                ->booleanNode('deffered')->defaultTrue()->end()
                ->scalarNode('default_connection_name')->defaultValue('default')->end()
                ->arrayNode('queues')
                    ->defaultValue(['event' => []])
                    ->useAttributeAsKey('name')
                    ->prototype('array')->end()
                ->end()
                ->arrayNode('failure')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('model')->defaultValue('App\\Entity\\FailedEvent')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
