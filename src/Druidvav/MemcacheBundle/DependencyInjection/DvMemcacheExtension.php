<?php

namespace Druidvav\MemcacheBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DvMemcacheExtension extends Extension
{
    /**
     * @param  array            $configs
     * @param  ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        if ($container->getParameter('kernel.debug')) {
            $loader->load('debug.yml');
        }

        if (isset($config['pools'])) {
            $this->enablePools($config['pools'], $container);
        }
        if (isset($config['cache'])) {
            $this->enableCache($config['cache'], $container);
        }
        if (isset($config['session'])) {
            $this->enableSessionSupport($config['session'], $container);
        } else {
            $container->setParameter('memcache.session_handler.auto_load',false);
        }
    }

    private function enablePools($config, ContainerBuilder $container)
    {
        foreach ($config as $pool => $poolConfig) {
            $definition = new Definition($container->getParameter('memcache.client.class'));
            $definition->addArgument($poolConfig['host']);
            $definition->addArgument($poolConfig['tcp_port']);
            $definition->addArgument($poolConfig['timeout']);
            $definition->addArgument($poolConfig['persistent_id']);
            $container->setDefinition('memcache.' . $pool . '.client', $definition);
        }
    }

    private function enableCache($config, ContainerBuilder $container)
    {
        foreach ($config as $cache => $cacheConfig) {
            $definition = new Definition($container->getParameter('memcache.cache.class'));
            $definition->addMethodCall('setMemcache', [ new Reference('memcache.' . $cacheConfig['pool'] . '.client') ]);
            $container->setDefinition('memcache.' . $cache . '.cache', $definition);
        }
    }

    private function enableSessionSupport($config, ContainerBuilder $container)
    {
        $sessionOptions = $container->getParameter('session.storage.options');
        $options = array();
        if (isset($config['ttl'])) {
            $options['expiretime'] = $config['ttl'];
        } elseif (isset($sessionOptions['cookie_lifetime'])) {
            $options['expiretime'] = $sessionOptions['cookie_lifetime'];
        }
        $options['prefix'] = $config['prefix'];
        $options['locking'] = $config['locking'];
        $options['spin_lock_wait'] = $config['spin_lock_wait'];
        $options['lock_max_wait'] = $config['lock_max_wait'];
        $container->setParameter('memcache.session_handler.auto_load', $config['auto_load']);
        $definition = new Definition($container->getParameter('memcache.session_handler.class'));
        $container->setDefinition('memcache.session_handler', $definition);
        $definition
            ->addArgument(new Reference('memcache.' . $config['pool'] . '.client'))
            ->addArgument($options);
        if (\PHP_VERSION_ID < 70000) {
            $this->addClassesToCompile(array($definition->getClass()));
        }

        if ($container->hasDefinition('memcache.data_collector')) {
            $definition = $container->getDefinition('memcache.data_collector');
            $definition->addMethodCall('addClient', array('default', $options, new Reference('memcache.default.client')));
        }
    }
}
