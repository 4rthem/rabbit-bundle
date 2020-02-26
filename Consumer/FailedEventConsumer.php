<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Model\FailedEventManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class FailedEventConsumer implements ConsumerInterface
{
    /**
     * @var FailedEventManager
     */
    private $failedEventManager;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(FailedEventManager $failedEventManager, EntityManagerInterface $em)
    {
        $this->failedEventManager = $failedEventManager;
        $this->em = $em;
    }

    final public function execute(AMQPMessage $msg)
    {
        $message = EventMessage::fromJson($msg->getBody());
        if (!$message instanceof EventMessage) {
            throw new Exception(sprintf('$message is not an instance of %s', EventMessage::class));
        }

        $failedEvent = $this->failedEventManager->createFailedEvent($message->getType(), $message->getPayload());
        $this->em->persist($failedEvent);
        $this->em->flush();

        return ConsumerInterface::MSG_ACK;
    }
}
