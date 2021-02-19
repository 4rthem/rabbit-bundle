<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Event;

use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    private EventMessage $eventMessage;

    public function __construct(EventMessage $eventMessage)
    {
        $this->eventMessage = $eventMessage;
    }

    public function getEventMessage(): EventMessage
    {
        return $this->eventMessage;
    }
}
