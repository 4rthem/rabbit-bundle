<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

interface EventProducerAdapterInterface
{
    public function publish(string $msgBody, string $routingKey = null, array $additionalProperties = []);
}
