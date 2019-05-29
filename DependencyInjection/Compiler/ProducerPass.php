<?php

namespace Arthem\Bundle\RabbitBundle\DependencyInjection\Compiler;

use Arthem\Bundle\RabbitBundle\Producer\Adapter\AMQPProducerAdapter;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProducerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(AMQPProducerAdapter::class)) {
            return;
        }
        $producerDefinition = $container->getDefinition(AMQPProducerAdapter::class);
        $taggedServices = $container->findTaggedServiceIds('old_sound_rabbit_mq.producer');

        /* @var $id ProducerInterface */
        foreach ($taggedServices as $id => $tags) {
            if (!preg_match('#old_sound_rabbit_mq\.(.+?)_producer#', $id, $regs)) {
                continue;
            }
            $producerDefinition->addMethodCall('addProducer', [$regs[1], new Reference($id)]);
        }
    }
}
