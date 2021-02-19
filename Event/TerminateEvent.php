<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Event;

class TerminateEvent extends AbstractEvent
{
    const NAME = 'rabbit_handler.terminate';
}
