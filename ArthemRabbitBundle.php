<?php

namespace Arthem\Bundle\RabbitBundle;

use Arthem\Bundle\RabbitBundle\DependencyInjection\Compiler\EventMessageConsumerHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArthemRabbitBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventMessageConsumerHandlerPass());
    }
}
