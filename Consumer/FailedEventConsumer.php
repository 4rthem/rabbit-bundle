<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Model\FailedEventManager;
use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

class FailedEventConsumer implements ConsumerInterface
{
    /**
     * @var FailedEventManager
     */
    private $failedEventManager;

    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(FailedEventManager $failedEventManager, ObjectManager $om)
    {
        $this->failedEventManager = $failedEventManager;
        $this->om = $om;
    }

    final public function execute(AMQPMessage $msg)
    {
        $message = EventMessage::fromJson($msg->getBody());
        if (!$message instanceof EventMessage) {
            throw new Exception(sprintf('$message is not an instance of %s', EventMessage::class));
        }

        $failedEvent = $this->failedEventManager->createFailedEvent($message->getType(), $message->getPayload());
        $this->om->persist($failedEvent);
        $this->om->flush();

        return ConsumerInterface::MSG_ACK;
    }
}
