<?php

namespace Arthem\Bundle\RabbitBundle\Producer\Adapter;

use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class AMQPProducerAdapter implements EventProducerAdapterInterface
{
    use LoggableTrait;

    /**
     * @var ProducerInterface[]
     */
    private array $producers = [];

    private array $eventsMap = [];

    public function __construct()
    {
        $this->setLogger(new NullLogger());
    }

    public function addProducer(string $eventType, ProducerInterface $producer): void
    {
        $this->producers[$eventType] = $producer;
    }

    public function setEventsMap(array $eventsMap): void
    {
        $this->eventsMap = $eventsMap;
    }

    public function publish(
        string $eventType,
        string $msgBody,
        string $routingKey = null,
        array $additionalProperties = [],
        ?array $headers = null
    ): void
    {
        if (!isset($this->producers[$eventType])) {
            throw new RuntimeException(sprintf('Undefined producer "%1$s". Maybe you forgot to declare queue in ArthemRabbitBundle?
# config/packages/arthem_rabbit.yaml
arthem_rabbit:
  queues:
    %1$s: ~
', $eventType));
        }

        $producerName = $this->eventsMap[$eventType];

        if (isset($headers['producer_name'])) {
            $producerName = $headers['producer_name'];
            unset($headers['producer_name']);
        }

        $this->producers[$producerName]->publish(
            $msgBody,
            $routingKey,
            $additionalProperties,
            $headers
        );
    }
}
