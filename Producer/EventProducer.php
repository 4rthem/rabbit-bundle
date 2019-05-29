<?php

namespace Arthem\Bundle\RabbitBundle\Producer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\EventProducerAdapterInterface;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

class EventProducer implements LoggerAwareInterface
{
    use LoggableTrait;

    /**
     * @var EventProducerAdapterInterface
     */
    private $adapter;

    public function __construct(EventProducerAdapterInterface $adapter)
    {
        $this->setLogger(new NullLogger());
        $this->adapter = $adapter;
    }

    public function publish(EventMessage $message, string $routingKey = null, array $additionalProperties = [])
    {
        $this->logger->info(sprintf('Produce event message "%s"', $message->getType()), [
            'payload' => json_encode($message->getPayload()),
        ]);

        $additionalProperties['content_type'] = 'application/json';

        $this->adapter->publish(
            $message->getType(),
            $message->toJson(),
            $routingKey,
            $additionalProperties
        );
    }
}
