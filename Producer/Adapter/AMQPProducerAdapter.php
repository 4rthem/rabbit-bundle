<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\NullLogger;

class AMQPProducerAdapter implements EventProducerAdapterInterface
{
    use LoggableTrait;

    /**
     * @var ProducerInterface
     */
    private $producer;

    public function __construct(ProducerInterface $producer)
    {
        $this->setLogger(new NullLogger());
        $this->producer = $producer;
    }

    public function publish(string $msgBody, string $routingKey = null, array $additionalProperties = [])
    {
        $this->producer->publish(
            $msgBody,
            $routingKey,
            $additionalProperties
        );
    }
}
