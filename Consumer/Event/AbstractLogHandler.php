<?php

namespace Arthem\Bundle\RabbitBundle\Consumer\Event;

use Arthem\Bundle\RabbitBundle\Event\TerminateEvent;
use Arthem\Bundle\RabbitBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractLogHandler implements EventMessageHandlerInterface, LoggerAwareInterface
{
    use LoggableTrait;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getQueueName(): string
    {
        return 'event';
    }

    public function preHandle(): void
    {
    }

    public function postHandle(): void
    {
        $this->eventDispatcher->dispatch(new TerminateEvent(), TerminateEvent::NAME);
    }
}
