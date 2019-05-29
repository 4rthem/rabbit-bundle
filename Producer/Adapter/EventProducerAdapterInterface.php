<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

interface EventProducerAdapterInterface
{
    public function publish(string $eventType, string $msgBody, string $routingKey = null, array $additionalProperties = []);
}
