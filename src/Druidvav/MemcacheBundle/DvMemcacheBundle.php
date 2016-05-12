<?php
namespace Druidvav\MemcacheBundle;

use Druidvav\MemcacheBundle\DependencyInjection\Compiler\EnableSessionSupport;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DvMemcacheBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->addCompilerPass(new EnableSessionSupport());
    }
}
