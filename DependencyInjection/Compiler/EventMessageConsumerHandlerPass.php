<?php

namespace Arthem\Bundle\RabbitBundle\DependencyInjection\Compiler;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessageHandlerInterface;
use Arthem\Bundle\RabbitBundle\Consumer\EventConsumer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use ReflectionClass;

class EventMessageConsumerHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(EventConsumer::class)) {
            return;
        }
        $eventConsumer = $container->getDefinition(EventConsumer::class);
        $taggedServices = $container->findTaggedServiceIds('arthem_rabbit.event_handler');

        /* @var $id EventMessageHandlerInterface */
        foreach ($taggedServices as $id => $tags) {
            $reflectionClass = new ReflectionClass($id);
            if ($reflectionClass->isAbstract()) {
                continue;
            }
            $events = $id::getHandledEvents();
            foreach ($events as $event) {
                $eventConsumer->addMethodCall('addHandler', [$event, new Reference($id)]);
            }
        }
    }
}
