<?php

namespace Arthem\Bundle\RabbitBundle\Producer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Arthem\Bundle\RabbitBundle\Producer\Adapter\EventProducerAdapterInterface;
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

    public function publish(
        EventMessage $message,
        string $deprecatedRoutingKey = null, // @deprecated use in EventMessage
        array $deprecatedProperties = [], // @deprecated use in EventMessage
        ?array $deprecatedHeaders = null // @deprecated use in EventMessage
    ): void {
        $this->logger->info(sprintf('Produce event message "%s"', $message->getType()), [
            'payload' => json_encode($message->getPayload()),
        ]);

        $properties = $message->getProperties();
        $properties['content_type'] = 'application/json';

        if (!empty($deprecatedProperties)) {
            $properties = array_merge($deprecatedProperties, $properties);
        }

        $this->adapter->publish(
            $message->getType(),
            $message->toJson(),
            $message->getRoutingKey() ?? $deprecatedRoutingKey,
            $properties,
            $message->getHeaders() ?? $deprecatedHeaders
        );
    }
}
