<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessageHandlerInterface;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\MessageResponseException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

class EventConsumer extends LoggerAwareConsumer
{
    /**
     * @var EventMessageHandlerInterface[]
     */
    private $handlers = [];

    public function addHandler(string $name, EventMessageHandlerInterface $handler)
    {
        $this->handlers[$name] = $handler;
    }

    public function processMessage(AMQPMessage $message): int
    {
        $message = EventMessage::fromJson($message->getBody());
        if (!$message instanceof EventMessage) {
            throw new Exception(sprintf('$message is not an instance of %s', EventMessage::class));
        }

        if (!isset($this->handlers[$message->getType()])) {
            throw new Exception(sprintf('No handler found for type "%s"', $message->getType()));
        }

        /** @var EventMessageHandlerInterface $handler */
        $handler = $this->handlers[$message->getType()];

        $this->logger->info(sprintf('Consume event message "%s"', $message->getType()), [
            'payload' => $message->getPayload(),
        ]);

        $handler->preHandle();

        try {
            try {
                $handler->handle($message);
                $response = ConsumerInterface::MSG_ACK;
            } catch (MessageResponseException $e) {
                $response = $e->getResponse();
            }
        } catch (\Throwable $e) {
            $handler->postHandle();
            throw $e;
        }

        $this->logger->info(sprintf('Message "%s" consumed with response %s', $message->getType(), $response));

        return $response;
    }
}
