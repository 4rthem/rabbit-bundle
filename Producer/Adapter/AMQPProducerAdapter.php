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

    private array $defaultPriorities = [];

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

    public function setDefaultPriorities(array $defaultPriorities): void
    {
        $this->defaultPriorities = $defaultPriorities;
    }

    public function publish(
        string $eventType,
        string $msgBody,
        string $routingKey = null,
        array $additionalProperties = [],
        ?array $headers = null
    ): void
    {
        $producerName = $this->eventsMap[$eventType];

        if (!isset($this->producers[$producerName])) {
            throw new RuntimeException(sprintf('Undefined producer "%1$s". Maybe you forgot to declare queue in ArthemRabbitBundle?
# config/packages/arthem_rabbit.yaml
arthem_rabbit:
  queues:
    %1$s: ~
', $eventType));
        }

        if (isset($headers['producer_name'])) {
            $producerName = $headers['producer_name'];
            unset($headers['producer_name']);
        }

        if (!isset($additionalProperties['priority']) && isset($this->defaultPriorities[$eventType])) {
            $additionalProperties['priority'] = $this->defaultPriorities[$eventType];
        }

        $this->producers[$producerName]->publish(
            $msgBody,
            $routingKey,
            $additionalProperties,
            $headers
        );
    }
}
