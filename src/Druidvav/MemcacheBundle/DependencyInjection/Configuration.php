<?php

namespace Druidvav\MemcacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dv_memcache');
        $rootNode
            ->append($this->addClientsSection())
            ->append($this->addCacheSection())
            ->append($this->addSessionSupportSection())
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function addClientsSection()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('pools');
        $node
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('host')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('tcp_port')
                        ->defaultValue(11211)
                        ->validate()
                        ->ifTrue(function ($v) { return !is_numeric($v); })
                            ->thenInvalid('port must be numeric')
                        ->end()
                    ->end()
                    ->scalarNode('persistent_id')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('timeout')
                        ->defaultValue(1)
                        ->validate()
                        ->ifTrue(function ($v) { return !is_numeric($v); })
                            ->thenInvalid('timeout must be numeric')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function addCacheSection()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('cache');
        $node
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('pool')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ->end();
        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function addSessionSupportSection()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('session');

        $node
            ->children()
                ->scalarNode('pool')->isRequired()->end()
                ->booleanNode('auto_load')->defaultTrue()->end()
                ->scalarNode('prefix')->defaultValue('lmbs')->end()
                ->scalarNode('ttl')->end()
                ->booleanNode('locking')->defaultTrue()->end()
                ->scalarNode('spin_lock_wait')->defaultValue(150000)->end()
                ->scalarNode('lock_max_wait')
                    ->defaultNull()
                    ->validate()
                    ->always(function($v) {
                        if (null === $v) {
                            return $v;
                        }
                        if (!is_numeric($v)) {
                            throw new InvalidConfigurationException("Option 'lock_max_wait' must either be NULL or an integer value");
                        }
                        return (int) $v;
                    })
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
