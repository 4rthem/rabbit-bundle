<?php

namespace Arthem\Bundle\RabbitBundle\DependencyInjection\Compiler;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessageHandlerInterface;
use Arthem\Bundle\RabbitBundle\Consumer\EventConsumer;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\AMQPProducerAdapter;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventMessageConsumerHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $eventConsumer = $container->getDefinition(EventConsumer::class);
        $producerDefinition = $container->getDefinition(AMQPProducerAdapter::class);
        $taggedServices = $container->findTaggedServiceIds('arthem_rabbit.event_handler');

        /* @var $id EventMessageHandlerInterface */
        foreach ($taggedServices as $id => $tags) {
            $reflectionClass = new ReflectionClass($id);
            if ($reflectionClass->isAbstract()) {
                continue;
            }
            $events = $id::getHandledEvents();
            $queue = $id::getQueueName();
            foreach ($events as $event) {
                $producerDefinition->addMethodCall('addProducer', [$event, new Reference(sprintf('old_sound_rabbit_mq.%s_producer', $queue))]);
                $eventConsumer->addMethodCall('addHandler', [$event, new Reference($id)]);
            }
        }
    }
}
