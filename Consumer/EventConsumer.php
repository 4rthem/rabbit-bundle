<?php

namespace Arthem\Bundle\RabbitBundle\Consumer;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessageHandlerInterface;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\MessageResponseException;
use Arthem\Bundle\RabbitBundle\Event\PreHandleEvent;
use Arthem\Bundle\RabbitBundle\Event\TerminateEvent;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

class EventConsumer extends LoggerAwareConsumer
{
    /**
     * @var EventMessageHandlerInterface[]
     */
    private array $handlers = [];
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addHandler(string $name, EventMessageHandlerInterface $handler)
    {
        $this->handlers[$name] = $handler;
    }

    public function processMessage(AMQPMessage $message): int
    {
        $event = EventMessage::fromJson($message->getBody());
        if (!$event instanceof EventMessage) {
            throw new Exception(sprintf('$message is not an instance of %s', EventMessage::class));
        }

        if (!isset($this->handlers[$event->getType()])) {
            throw new Exception(sprintf('No handler found for type "%s"', $event->getType()));
        }

        /** @var EventMessageHandlerInterface $handler */
        $handler = $this->handlers[$event->getType()];

        $this->eventDispatcher->dispatch(new PreHandleEvent($event), PreHandleEvent::NAME);

        $this->logger->info(sprintf('Consume event message "%s"', $event->getType()), [
            'payload' => $event->getPayload(),
        ]);

        $handler->preHandle();

        try {
            try {
                $handler->handle($event);
                $response = ConsumerInterface::MSG_ACK;
            } catch (MessageResponseException $e) {
                $response = $e->getResponse();
            }
        } catch (Throwable $e) {
            $handler->postHandle();
            $this->eventDispatcher->dispatch(new TerminateEvent($event), TerminateEvent::NAME);
            throw $e;
        }

        $handler->postHandle();

        $this->logger->info(sprintf('Message "%s" consumed with response %s', $event->getType(), $response));

        $this->eventDispatcher->dispatch(new TerminateEvent($event), TerminateEvent::NAME);

        return $response;
    }
}
