<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\FailedEventInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

class FailedEventConsumer implements ConsumerInterface
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(string $model, ObjectManager $om)
    {
        $this->model = $model;
        $this->om = $om;
    }

    final public function execute(AMQPMessage $msg)
    {
        $message = EventMessage::fromJson($msg->getBody());
        if (!$message instanceof EventMessage) {
            throw new Exception(sprintf('$message is not an instance of %s', EventMessage::class));
        }

        /** @var FailedEventInterface $failedEvent */
        $failedEvent = new $this->model;

        $failedEvent->setPayload($message->getPayload());
        $this->om->persist($failedEvent);
        $this->om->flush();

        return ConsumerInterface::MSG_ACK;
    }
}
