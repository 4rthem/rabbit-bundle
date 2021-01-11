<?php

declare(strict_types=1);

namespace Arthem\Bundle\RabbitBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TerminateEvent extends Event
{
    const NAME = 'rabbit_handler.terminate';
}
