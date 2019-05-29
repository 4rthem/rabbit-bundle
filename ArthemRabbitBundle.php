<?php

namespace Arthem\Bundle\RabbitBundle;

use Arthem\Bundle\RabbitBundle\DependencyInjection\Compiler\EventMessageConsumerHandlerPass;
use Arthem\Bundle\RabbitBundle\DependencyInjection\Compiler\ProducerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArthemRabbitBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventMessageConsumerHandlerPass());
        $container->addCompilerPass(new ProducerPass());
    }
}
