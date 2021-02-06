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
        if (
            !$container->hasDefinition(EventConsumer::class)
            || !$container->hasDefinition(AMQPProducerAdapter::class)
        ) {
            return;
        }
        $eventConsumer = $container->getDefinition(EventConsumer::class);
        $producerDefinition = $container->getDefinition(AMQPProducerAdapter::class);
        $taggedServices = $container->findTaggedServiceIds('arthem_rabbit.event_handler');

        /* @var $id EventMessageHandlerInterface */
        $eventsMap = [];
        $defaultPriorities = [];
        foreach ($taggedServices as $id => $tags) {
            $reflectionClass = new ReflectionClass($id);
            if ($reflectionClass->isAbstract()) {
                continue;
            }
            $events = $id::getHandledEvents();
            $queue = $id::getQueueName();
            $defaultPriority = $id::getDefaultPriority();

            foreach ($events as $event) {
                $eventsMap[$event] = $queue;

                if (null !== $defaultPriority) {
                    $defaultPriorities[$event] = $defaultPriority;
                }

                $eventConsumer->addMethodCall('addHandler', [$event, new Reference($id)]);
            }
        }
        $producerDefinition->addMethodCall('setEventsMap', [$eventsMap]);
        $producerDefinition->addMethodCall('setDefaultPriorities', [$defaultPriorities]);
    }
}
